<?php

class ProjectSetup
{
  public static function isMassProductionAllowed(ObjectType $objectType)
  {
    $results = explode(";", $objectType->getBuildResult());
    $buildConditions = explode(";", $objectType->getBuildConditions());

    $disallowedBecauseOfBuildConditions = self::isMassProductionDisallowedBecauseOfBuildConditions($buildConditions);
    if ($disallowedBecauseOfBuildConditions) {
      return false;
    }

    $disallowedBecauseOfBuildResult = self::isMassProductionDisallowedBecauseOfBuildResult($results);
    if ($disallowedBecauseOfBuildResult) {
      return false;
    }

    return true;
  }

  /**
   * @param string[] $buildConditions
   * @return bool
   */
  private static function isMassProductionDisallowedBecauseOfBuildConditions(array $buildConditions)
  {
    return Pipe::from($buildConditions)->filter(function($arg) {
        return strstr($arg, "maxonobj") || strstr($arg, "maxonloc");
      })->count() > 0;
  }

  /**
   * @param string[] $results
   * @return bool
   */
  private static function isMassProductionDisallowedBecauseOfBuildResult(array $results)
  {
    if (count($results) > 1) {
      return true;
    }

    $anyResultHasCustomName = Pipe::from($results)
        ->filter(function($result) {
          return strstr($result, ">ask") || strstr($result, ">unique-ask"); // we know there's just one result
        })->count() > 0;
    return $anyResultHasCustomName;
  }
}
