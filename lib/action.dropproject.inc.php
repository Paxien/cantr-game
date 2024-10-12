<?php

if ($char->getProject() > 0) {
  try {
    $project = Project::loadById($char->getProject());

    if ($project->getType() == ProjectConstants::TYPE_RESTING) {
      if ($project->getWorkersCount() == 1) {
        $project->deleteFromDb();
      }
    }
  } catch (InvalidArgumentException $e) {
    Logger::getLogger(__FILE__)
      ->warn("Character {$char->getId()} dropping participation in inexistent project {$char->getProject()}");
  }

  $char->setProject(0);
  $char->saveInDb();
}

redirect("char");
