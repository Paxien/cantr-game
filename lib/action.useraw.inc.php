<?php

// SANITIZE INPUT
$amount = HTTPContext::getInteger('amount');
$projectId = HTTPContext::getInteger('project');
$objectId = HTTPContext::getInteger('object_id');

try {
  $project = Project::loadById($projectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("page.inventory", "error_project_not_specified");
}

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "too_far_away");
}

$forProject = new UseForProject($char, $project);

try {
  $forProject->useRaw($object, $amount);
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("useraw&project=$projectId&object_id=$objectId", $e->getMessage());
}

redirect("char.inventory");
