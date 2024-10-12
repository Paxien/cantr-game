<?php

$page = "server.projects";
include "server.header.inc.php";


print "Projects:\n";
$db = Db::get();
$db->query("UPDATE projects SET turnsleft = 1 WHERE turnsleft = 0");

// reset bonus from vehicle boosting projects
$stm = $db->prepare("UPDATE projects SET result = '0' WHERE type = :type");
$stm->bindInt("type", ProjectConstants::TYPE_BOOSTING_VEHICLE);
$stm->execute();

$done = 0;
$inProgress = 0;

/* Projects progressed by char */

$stm = $db->prepare("SELECT id FROM chars WHERE project > 0 AND status = :status ORDER BY project");
$stm->bindInt("status", CharacterConstants::CHAR_ACTIVE);
$stm->execute();
$charIds = $stm->fetchScalars();
foreach ($charIds as $charId) {
  try {
    $char = Character::loadById($charId);
    printf("\nCharacter %d working on %d - ", $char->getId(), $char->getProject());

    try {
      $project = Project::loadById($char->getProject());

      printf("Turns: %d [%.1F%%]", $project->getTurnsLeft(), $project->getPercentDone());
      $preventingProgress = $project->validateProgress($char);
      if (empty($preventingProgress)) {
        $isDone = $project->makeProgress($char);
        ($isDone) ? $done++ : $inProgress++;

        if ($isDone) {
          print " done";
        } else {
          printf("-> %d [%.1F%%] progressing", $project->getTurnsLeft(), $project->getPercentDone());
        }
      } else {
        print " requirements not met [" . implode(", ", $preventingProgress) . "]";
      }
    } catch (InvalidArgumentException $e) {
      print " project not found";
    }
  } catch (Exception $e) {
    Logger::getLogger("server.projects")
      ->error("unexpected exception when processing char {$charId}: " . $e);
  }
}

// Handle automatic projects
$stm = $db->prepare("SELECT * FROM projects WHERE automatic IN (:automatic, :semiAutomatic)");
$stm->bindInt("automatic", ProjectConstants::PROGRESS_AUTOMATIC);
$stm->bindInt("semiAutomatic", ProjectConstants::PROGRESS_SEMIAUTOMATIC);
$stm->execute();
foreach ($stm->fetchAll() as $project_info) {
  print "\nAutomatic project $project_info->id - ";

  $project = Project::loadFromFetchObject($project_info, $db);

  if ($project != null && $project->loaded()) {
    printf("Turns: %d [%.1F%%]", $project->getTurnsLeft(), $project->getPercentDone());
    $preventingProgress = $project->validateProgress(null);
    if (empty($preventingProgress)) {
      $isDone = $project->makeProgress(null);
      ($isDone) ? $done++ : $inProgress++;

      if ($isDone) {
        print " done";
      } else {
        printf("-> %d [%.1F%%] progressing", $project->getTurnsLeft(), $project->getPercentDone());
      }
    } else {
      print " requirements not met [" . implode(", ", $preventingProgress) . "]";
    }
  } else {
    print " project not found";
  }
}

print "\n Done: $done; Workers in progress: $inProgress \n";


// NATURAL HEALING
$max_natural_healing = _MAX_NATURAL_HEALING / ProjectConstants::TURNS_PER_DAY; //maximum of natural healing
$base_healing = _BASE_HEALING; //amount of natural healing without furniture
$natural_healing = _SCALESIZE_GSS * $max_natural_healing * $base_healing / 100; //the actual value (in percent) it needs to be to do the math
$max_health = _SCALESIZE_GSS;
print "\n\n natural healing: $max_natural_healing% basevalue: $base_healing \n";

$stm = $db->prepare("SELECT chars.id as id, chars.project as project, states.value as health
  FROM chars INNER JOIN states ON chars.id = states.person
  WHERE chars.status = :active AND states.type = :health AND states.value < :maxHealth");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("health", StateConstants::HEALTH);
$stm->bindInt("maxHealth", $max_health);
$stm->execute();
foreach ($stm->fetchAll() as $char_info) {
  $health_up = 0;

  import_lib("func.genes.inc.php");
  $hunger = read_state($char_info->id, StateConstants::HUNGER);
  $hunger = $hunger / _SCALESIZE_GSS;

  $stm = $db->prepare("SELECT result FROM projects WHERE type = :type AND id = :id LIMIT 1");
  $stm->bindInt("type", ProjectConstants::TYPE_RESTING);
  $stm->bindInt("id", $char_info->project);
  $stm->execute();
  foreach ($stm->fetchAll() as $project_info) {
    $health_up += _SCALESIZE_GSS * ($project_info->result / 1250 * ($max_natural_healing * (1 - $base_healing))) / 100; //the 1250 is the highest value of the resting furniture
  }

  $health_up = rand_round(($natural_healing + $health_up) * (1 - $hunger));

  alter_state($char_info->id, _GSS_HEALTH, $health_up); //here the natural healingrate is added to the health of the character
}


$db = Db::get();
$stm = $db->prepare("UPDATE states SET value = GREATEST(0, CAST(value AS SIGNED) - :valueChange) WHERE type = :type AND value > 0");
$stm->bindInt("type", StateConstants::TIREDNESS);
$stm->bindInt("valueChange", rand_round(ProjectConstants::TIREDNESS_RECOVER_PER_TURN));
$stm->execute();

$db = Db::get();
$stm = $db->prepare("UPDATE states SET value = GREATEST(0, CAST(value AS SIGNED) - :valueChange) WHERE type = :type AND value > 0");
$stm->bindInt("type", StateConstants::DRUNKENNESS);
$stm->bindInt("valueChange", rand_round(ProjectConstants::DRUNKENNESS_RECOVER_PER_TURN));
$stm->execute();

include "../lib/server/server.footer.inc.php";
