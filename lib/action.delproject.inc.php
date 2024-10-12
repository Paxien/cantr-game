<?php

$projectId = HTTPContext::getInteger('project');

$project = ProjectCanceler::FromId($projectId, $char->getId(), Db::get());

if (!$project) {
  CError::throwRedirectTag("char.projects", "error_too_far_away");
}

if ($project) {
  $cancellableOrErrorMessage = $project->canBeCancel($character);
  if ($cancellableOrErrorMessage === true) {
    $project->cancel();
  } else {
    CError::throwRedirect("char.projects", $cancellableOrErrorMessage);
  }
}

redirect("char.projects");
