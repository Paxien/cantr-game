<?php

namespace App\Tests;

use GameDate;
use LanguageConstants;
use MailService;
use PHPUnit_Framework_MockObject_MockObject;
use PlayerCleanupManager;
use PlayerConstants;
use Request;
use stdClass;

/**
 * @group integration
 */
class ServerCleanupIntTest extends AbstractIntTest
{
  const REMOVAL_DAYS = 35;
  const NEW_PLAYER_REMOVAL_DAYS = 22;
  const NEW_PLAYER_REMINDER_DAYS = 3;
  /**
   * @var GameDate
   */
  private $today;

  public function setUp()
  {
    parent::setUp();
    $this->today = GameDate::NOW()->getDay();
  }

  public function testRemovesAfterLongInactivity()
  {
    $playerId = 10;
    $this->createPlayer($playerId, "plr@ex.com", LanguageConstants::ENGLISH,
      $this->today - 100, $this->today - self::REMOVAL_DAYS, PlayerConstants::ACTIVE);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses(["plr@ex.com"]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $stm = $this->db->prepare("SELECT status FROM players WHERE id = :id");
    $status = $stm->executeScalar(["id" => $playerId]);
    $this->assertEquals(PlayerConstants::IDLEDOUT, $status);
  }

  public function testRemovesOnlyNewPlayersAfterShorterInactivity()
  {
    $newPlayerId = 10;
    $this->createPlayer($newPlayerId, "plr_new@ex.com", LanguageConstants::ENGLISH,
      $this->today - self::NEW_PLAYER_REMOVAL_DAYS, $this->today - self::NEW_PLAYER_REMOVAL_DAYS, PlayerConstants::ACTIVE);
    $notNewPlayerId = 11;
    $this->createPlayer($notNewPlayerId, "plr@ex.com", LanguageConstants::ENGLISH,
      $this->today - 100, $this->today - self::NEW_PLAYER_REMOVAL_DAYS, PlayerConstants::ACTIVE);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses(["plr_new@ex.com"]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $this->assertEquals(PlayerConstants::IDLEDOUT, $this->getPlayerStatus($newPlayerId));
    $this->assertEquals(PlayerConstants::ACTIVE, $this->getPlayerStatus($notNewPlayerId));
  }

  public function testDoesNotRemovePlayerInGab()
  {
    $gabPlayerId = 10;
    $this->createPlayer($gabPlayerId, "plr@ex.com", LanguageConstants::ENGLISH,
      $this->today - 200, $this->today - self::REMOVAL_DAYS, PlayerConstants::ACTIVE);
    $stm = $this->db->prepare("INSERT INTO assignments (player, council, status, special) VALUES (:playerId, :councilId, 0, '')");
    $stm->execute(["playerId" => $gabPlayerId, "councilId" => _COUNCIL_GAB]);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses([]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $this->assertEquals(PlayerConstants::ACTIVE, $this->getPlayerStatus($gabPlayerId));
  }

  public function testDoesNotRemovePlayerOnLeaveAndDecreasesOnLeavePeriod()
  {
    $onLeavePlayerId = 10;
    $this->createPlayer($onLeavePlayerId, "plr@ex.com", LanguageConstants::ENGLISH,
      $this->today - 200, $this->today - self::REMOVAL_DAYS, PlayerConstants::ACTIVE, 1);
    $stm = $this->db->prepare("INSERT INTO assignments (player, council, status, special) VALUES (:playerId, :councilId, 0, '')");
    $stm->execute(["playerId" => $onLeavePlayerId, "councilId" => _COUNCIL_GAB]);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses([]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $player = $this->getPlayerInfo($onLeavePlayerId);
    $this->assertEquals(PlayerConstants::ACTIVE, $player->status);
    $this->assertEquals(0, $player->onleave);
    $this->assertEquals($this->today, $player->lastdate);
  }

  public function testSendReminderToNewPlayer()
  {
    $newPlayerId = 10;
    $this->createPlayer($newPlayerId, "plr_new@ex.com", LanguageConstants::ENGLISH,
      $this->today - self::NEW_PLAYER_REMINDER_DAYS, $this->today - self::NEW_PLAYER_REMINDER_DAYS, PlayerConstants::ACTIVE);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses(["plr_new@ex.com"]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $this->assertEquals(PlayerConstants::ACTIVE, $this->getPlayerStatus($newPlayerId));
  }

  public function testSendWarningsButNotRemovePlayers()
  {
    $playerId = 10;
    $this->createPlayer($playerId, "plr1@ex.com", LanguageConstants::ENGLISH,
      $this->today - 100, $this->today - (self::REMOVAL_DAYS - 1), PlayerConstants::ACTIVE);
    $newPlayerId = 11;
    $this->createPlayer($newPlayerId, "plr2@ex.com", LanguageConstants::ENGLISH,
      $this->today - (self::NEW_PLAYER_REMOVAL_DAYS - 1), $this->today - (self::NEW_PLAYER_REMOVAL_DAYS - 1), PlayerConstants::ACTIVE);

    $mailService = $this->createMailServerMockSendingToSpecificAddresses(["plr1@ex.com", "plr2@ex.com"]);
    $envWithIntroDisabled = Request::getInstance()->getEnvironment();
    $cleanupManager = new PlayerCleanupManager($this->db, $mailService, $envWithIntroDisabled);

    $cleanupManager->processAll(self::REMOVAL_DAYS, self::NEW_PLAYER_REMOVAL_DAYS, self::NEW_PLAYER_REMINDER_DAYS);

    $this->assertEquals(PlayerConstants::ACTIVE, $this->getPlayerStatus($playerId));
    $this->assertEquals(PlayerConstants::ACTIVE, $this->getPlayerStatus($newPlayerId));
  }

  private function createPlayer($id, $email, $language, $registerDay, $lastDay, $status, $onLeave = 0)
  {
    $stm = $this->db->prepare("INSERT INTO players (id, username, firstname, lastname, email, language,
                     password, register, lastdate,lasttime, status, country, lastlogin, approval, onleave, age)
    VALUES (:id, :username, '', '', :email, :language, :password, :registerDay, :lastDay, 0, :status, '', '', 1, :onLeave, 100)");
    $stm->execute([
      "id" => $id, "username" => "user$id", "email" => $email, "language" => $language, "password" => "xxx",
      "registerDay" => $registerDay, "lastDay" => $lastDay, "status" => $status, "onLeave" => $onLeave,
    ]);
  }

  /**
   * @param $receiverEmails string[]
   * @return PHPUnit_Framework_MockObject_MockObject
   */
  private function createMailServerMockSendingToSpecificAddresses($receiverEmails)
  {
    $mailService = $this->getMockBuilder(MailService::class)->disableOriginalConstructor()->getMock();
    foreach ($receiverEmails as $id => $receiverEmail) {
      $mailService->expects($this->at($id))
        ->method('send')
        ->with($this->equalTo($receiverEmail), $this->anything(), $this->anything());
    }
    return $mailService;
  }

  /**
   * @param $playerId
   * @return int player status
   */
  private function getPlayerStatus($playerId)
  {
    $stm = $this->db->prepare("SELECT status FROM players WHERE id = :id");
    return $stm->executeScalar(["id" => $playerId]);
  }

  /**
   * @param $onLeavePlayerId
   * @return stdClass
   */
  private function getPlayerInfo($onLeavePlayerId)
  {
    $stm = $this->db->prepare("SELECT * FROM players WHERE id = :id");
    $stm->execute(["id" => $onLeavePlayerId]);
    return $stm->fetchObject();
  }
}
