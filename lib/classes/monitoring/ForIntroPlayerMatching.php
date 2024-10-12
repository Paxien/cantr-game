<?php


/**
 * Responsible for checking and matching new player registration details
 * with data already stored in intro and main cantr databases
 */
class ForIntroPlayerMatching
{

  private $newPlayer; // stdClass object
  private $logger;

  private $MONTH_SEC;
  /** @var Db */
  private $db;

  public function __construct($newPlayer)
  {
    $this->newPlayer = $newPlayer;
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();
    $this->MONTH_SEC = 60 * 60 * 24 * 30; // "constant"
  }

  /**
   * List of people who look suspicious when compared to newly registered player
   * @return [type]        [description]
   */
  public function findSuspiciousMatches()
  {
    $suspiciousList = [];

    $lastLoginRegex = '|^(\d\d/\d\d/\d{4} \d\d:\d\d) (.*) \(.*\)$|';

    // removed players with ip-match from last month
    $matched = preg_match($lastLoginRegex, $this->newPlayer->ipinfo, $matches);

    if ($matched) {
      $currentTS = DateTime::createFromFormat("d/m/Y H:i", $matches[1])->getTimestamp();

      $stm = $this->db->prepare("SELECT 'refused' AS id, firstname, lastname, email, lastlogin, 'refused' AS type
        FROM removed_players
        WHERE lastlogin LIKE :lastLogin AND trouble = 1");
      $stm->bindStr("lastLogin", "%{$matches[2]}%");
      $stm->execute();
      foreach ($stm->fetchAll() as $removedPlr) {
        $removedPlrMatched = preg_match($lastLoginRegex, $removedPlr->lastlogin, $remMatches);

        $remTS = DateTime::createFromFormat("d/m/Y H:i", $remMatches[1])->getTimestamp();
        if ($removedPlrMatched && ($remTS >= $currentTS - $this->MONTH_SEC)) { // last month only
          $suspiciousList[] = $removedPlr;
        }
      }

      // locked players who logged at least once from the same ip in last year
      // And active players with same IP
      $stm = $this->db->prepare("SELECT p.*, 'locked' AS type FROM players p INNER JOIN ips ON ips.player = p.id
        WHERE ip = :ipToMatch AND ((p.status = :lockedStatus AND ips.lasttime >= NOW() - INTERVAL 1 YEAR) OR p.status < :lockedStatus)");
      $stm->bindStr('ipToMatch', $matches[2]);
      $stm->bindInt('lockedStatus', PlayerConstants::LOCKED);
      $stm->execute();
      foreach ($stm->fetchAll() as $suspiciousPlr) { // done in sql
        $suspiciousList[] = $suspiciousPlr;
      }

    } else {
      $this->logger->error("lastlogin regex '{$this->newPlayer->ipinfo}' "
        . "for new player wasn't matched correctly");
    }

    return $suspiciousList;
  }

}
