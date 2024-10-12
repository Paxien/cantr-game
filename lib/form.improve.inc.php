<?php

// SANITIZE INPUT
$connectionId = HTTPContext::getInteger('connection');

try {
  $connection = Connection::loadById($connectionId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.description", "Road data is invalid");
}

if (!in_array($char->getLocation(), [$connection->getStart(), $connection->getEnd()])) {
  CError::throwRedirectTag("char.description", "error_improve_not_location");
}

$tag = new tag();
$tag->language = $l;

$roadTypes = Pipe::from($connection->getTypeNames())->map(function($typeName) {
  return "<CANTR REPLACE NAME=" . $typeName . ">";
})->toArray();

$tag->content = implode(",", $roadTypes);

$roadTypes = $tag->interpret();

$possibleImprovements = $connection->getPotentialImprovements();

$options = [];
foreach ($possibleImprovements as $nextType) {
  $nextConnectionType = ConnectionType::loadById($nextType);

  if (!$connection->canBeImprovedTo($nextConnectionType)) {
    continue;
  }

  $partToImprove = $connection->getConnectionPartImprovableTo($nextConnectionType);
  if ($partToImprove != null && $connection->isBeingImproved($partToImprove)) {
    continue;
  }

  $raws = $connection->getRawsToImproveTo($nextConnectionType);
  $days = $connection->getDaysToImproveTo($nextConnectionType);
  
  $nextName = str_replace (" ","_", $nextConnectionType->getName());

  $tag->content = "<CANTR REPLACE NAME=road_$nextName>";
  $nextName = $tag->interpret();

  $rawsCode = array();
  foreach ($raws as $name => $amount) {
    $rawsCode[] = "<CANTR REPLACE NAME=grams_of_raw AMOUNT=$amount RAW=". str_replace(" ", "_", $name) .">";
  }

  $details  = "<CANTR REPLACE NAME=form_materials>: ";
  $details .= implode(", ", $rawsCode) ."<br>";
  $details .= "<CANTR REPLACE NAME=form_days_of_work>: $days";
  
  $tag->content = $details;
  $details = $tag->interpret();
  $options["improve_". $nextType] = array(
    "description" => "<CANTR REPLACE NAME=road_improve TYPE=". urlencode($nextName) .">",
    "details" => $details,
  );
}

foreach ($connection->getParts() as $part) {
  if ($part->getDeterioration() > 0) { // repair
    $raws = $connection->getRawsToImproveTo($part->getType());
    $buildDays = $connection->getDaysToImproveTo($part->getType());
    $deterRatio = $part->getDeterioration() / 10000;

    $raws = Pipe::from($raws)->map(function ($amount) use ($deterRatio) { // repairs are cheaper
      return round($amount * $deterRatio * ConnectionConstants::REPAIR_TO_IMPROVEMENT_COST);
    })->toArray();
    $rawsCode = [];
    foreach ($raws as $name => $amount) {
      $rawsCode[] = "<CANTR REPLACE NAME=grams_of_raw AMOUNT=$amount RAW=" . str_replace(" ", "_", $name) . ">";
    }

    $days = round($buildDays * $deterRatio * ConnectionConstants::REPAIR_TO_IMPROVEMENT_TIME, 1);

    $details = "<CANTR REPLACE NAME=form_materials>: ";
    $details .= implode(", ", $rawsCode) . "<br>";
    $details .= "<CANTR REPLACE NAME=form_days_of_work>: $days";

    $options["repair_" . $part->getType()->getId()] = array(
      "description" => "<CANTR REPLACE NAME=road_repair ROAD=" . $part->getType()->getName() . ">",
      "details" => $details,
    );
  }

  if ($part->getType()->isDestroyable()) {
    $buildDays = $connection->getDaysToImproveTo($part->getType());
    $deterRatio = $part->getDeterioration() / 10000;
    $days = $buildDays * (1 - $deterRatio) * ConnectionConstants::DESTRUCTION_TO_IMPROVEMENT_TIME;

    $details = "<CANTR REPLACE NAME=form_days_of_work>: $days";

    $options["destroy_" . $part->getType()->getId()] = array(
      "description" => "<CANTR REPLACE NAME=road_destroy ROAD=" . $part->getType()->getName() . ">",
      "details" => $details,
    );
  }
}

$smarty = new CantrSmarty;

$smarty->assign ("connection", $connection->getId());
$smarty->assign ("TYPE", $roadTypes);
$smarty->assign ("toType", $next);
$smarty->assign ("options", $options);
$smarty->assign ("optionsJson", json_encode($options));

$smarty->displayLang ("form.improve.tpl", $lang_abr); 
