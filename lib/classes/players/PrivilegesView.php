<?php

class PrivilegesView
{

  private $db;
  private $player;

  public function __construct(Player $player, Db $db)
  {
    $this->player = $player;
    $this->db = $db;
  }

  public function getAssignmentTexts()
  {
    $stm = $this->db->prepare("SELECT assignments.council as councilid,
        assignments.status AS status, assignments.special AS special,
        councils.name AS council FROM assignments,councils 
      WHERE player = :playerId AND assignments.council = councils.id");
    $stm->bindInt('playerId', $this->player->getId());
    $stm->execute();
    return Pipe::from($stm->fetchAll())->map(function($assignment) {
      $assignment->statusText = self::getStatusText($assignment->status, $assignment->special);
      return $assignment;
    })->toArray();
  }

  /**
   * @param int $assignmentStatus status of assignment
   * @param string $special additional text to display as part of assignment status
   * @return string
   */
  private static function getStatusText($assignmentStatus, $special)
  {
    $assignmentText = self::$ASSIGNMENTS[$assignmentStatus];
    if (!empty($special)) {
      $assignmentText .= " ($special)";
    }
    return $assignmentText;
  }

  private static $ASSIGNMENTS = [
    0 => "Hidden member",
    1 => "Chair",
    2 => "Senior member",
    3 => "Member",
    4 => "Special member",
    5 => "Aspirant member",
    6 => "On leave"
  ];

  public function getAccess()
  {
    $stm = $this->db->prepare("SELECT access_types.description 
      FROM access, access_types WHERE player = :playerId AND access.page = access_types.id");
    $stm->bindInt("playerId", $this->player->getId());
    $stm->execute();
    return $stm->fetchScalars();
  }

  public function getCeAccess()
  {
    $stm = $this->db->prepare("SELECT ceAccess.access AS page, ceAccessTypes.description AS description 
      FROM ceAccess,ceAccessTypes WHERE player = :playerId AND ceAccess.access=ceAccessTypes.id");
    $stm->bindInt("playerId", $this->player->getId());
    $stm->execute();
    return $stm->fetchScalars();
  }
}
