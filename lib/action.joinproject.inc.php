<?php
require_once("func.projectsetup.inc.php");

// SANITIZE INPUT
$project = HTTPContext::getInteger('project');

try {
  Project::loadById($project);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.projects", "error_too_far_away");
}

$successOrErrorMessage = automatic_join_project($project, $character);
if ($successOrErrorMessage !== true) {
  CError::throwRedirect("char.events", $successOrErrorMessage);
}

redirect("char");
