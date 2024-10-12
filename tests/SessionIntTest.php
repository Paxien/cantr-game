<?php

namespace App\Tests;

use Session;

/**
 * @group integration
 */
class SessionIntTest extends AbstractIntTest
{
  public function testRemovesExpiredSessions()
  {
    $this->createSession(1, "2010-01-11 23:59");
    $this->createSession(2, "2017-07-14 2:39");
    $this->createSession(3, "2017-07-14 2:40");
    $this->createSession(4, "2017-07-14 3:14");
    $this->createSession(5, "2020-12-31 0:00");

    Session::deleteExpiredSessions($this->db, 1500000000); // 2017-07-14 2:40

    $stm = $this->db->query("SELECT id FROM sessions");
    $notExpiredSessionIds = $stm->fetchScalars();
    asort($notExpiredSessionIds);
    $this->assertEquals([3, 4, 5], $notExpiredSessionIds);
  }

  private function createSession($id, $lastDateTime)
  {
    $stm = $this->db->prepare("INSERT INTO sessions (id, player, language, info, lasttime, login_ip)
      VALUES (:id, :player, :language, '', :lastDateTime, '')");
    $stm->execute(["id" => $id, "player" => 123, "language" => 1, "lastDateTime" => $lastDateTime]);
  }
}