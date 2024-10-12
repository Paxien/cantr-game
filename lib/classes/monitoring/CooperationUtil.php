<?php

class CooperationUtil
{
  private function __construct()
  {
  }

  /**
   * @param $ids array
   * @param Db $db
   * @return array of arrays which have keys: date, victim, perpetrator, weapon
   */
  public static function getFightingCooperationFor($ids, Db $db)
  {
    // fight cooperation
    $stm = $db->prepareWithIntList("
      SELECT e.*, eo.observer
      FROM chars ch, events_obs eo, events e
      WHERE ch.player IN (:ids) AND eo.observer = ch.id AND e.type IN (49, 60, 52, 55) AND eo.event = e.id
      ORDER BY e.id
    ", [
      "ids" => $ids,
    ]); // all possible "you attack" and slap actions
    $stm->execute();

    $victims = []; // array of victims
    // Key is victim id, value is array of attackers. So count($victims[$victimId]) > 1 => attacked by more than 1 of suspected chars
    foreach ($stm->fetchAll() as $eventInfo) {
      preg_match('/VICTIM=(\d+)( WEAPON=(.*?)( DAMAGE=(\d+) SKILL_ACTOR=(.*)|$)|$)/', urldecode($eventInfo->parameters), $result);
      $victimId = $result[1];
      $weapon = $result[3];
      if (preg_match('/^<CANTR REPLACE NAME=item_([0-9a-zA-Z_]*)_o>/', $weapon, $wepRes)) {
        $weapon = $wepRes[1];
      }
      $attackData = array(
        "date" => "$eventInfo->day-$eventInfo->hour." .
        str_pad($eventInfo->minute, 2, "0", STR_PAD_LEFT),
        "weapon" => ($weapon ? $weapon : "a slap"));
      $victims[$victimId][$eventInfo->observer][] = $attackData;
    }
    $fights = [];
    foreach ($victims as $victimId => $perpetrators) {
      if (count($perpetrators) > 1) {
        foreach ($perpetrators as $perpId => $attacks) {
          foreach ($attacks as $attackData) {
            $fights[] = [
              "date" => $attackData['date'],
              "victim" => $victimId,
              "perpetrator" => $perpId,
              "weapon" => $attackData['weapon'],
            ];
          }
        }
      }
    }

    usort($fights, function($a, $b) {
      $charIdDiff = $a["victim"] - $b["victim"];
      if ($charIdDiff == 0) {
        return strnatcmp($a["date"], $b["date"]);
      }
      return $charIdDiff;
    });

    return $fights;
  }

  public static function getGoodsPassingReport($text, Db $db)
  {
    $normal = "";
    if (!empty($text)) {

      // Preparing resources passing report
      $colours = array(1 => '#2fb2b0', 'blue', 'green', 'red', 'brown', '#c9bb1d', 'black', 'black', 'black', 'black');

      $A = explode("\n", $text);
      foreach ($A as $item) {
        $B = explode("|", $item);
        if (count($B) > 6) {
          $Coop[$B[0]] [] = $B;
        }
      }

      asort($Coop);
      $bbCode = "";
      foreach ($Coop as $ids => $player) {

        $multi = $ids [0] == '+';

        if ($multi) {
          // Multiplayer cooperation
          $stm = $db->prepare("SELECT names FROM troubleplayers WHERE id = :id");
          $stm->bindInt("id", $ids);
          $persons = $stm->executeScalar();
          if ($persons) {
            $persons = explode("\r", $persons);
            $colour = 1;
            foreach ($persons as $person) {
              $data = explode("\t", $person);
              $normal .= $data [0] . ". " . $data [1] . ",\n";
              $bbCode .= "[i]" . $data [0] . "[/i] [color=" . $colours [$colour++] . "]" . $data [1] . "[/color]\n";

            }
          } else {
            $normal .= "Unregistered group of players:\n";
            $multi = false;
            $bbCode .= "[i]Unregistered group of players[/i]:\n";
          }
          $bbCode .= "[list]";
        } else {
          // Single player cooperation
          $normal .= $player [0][0] . ". " . $player [0][1] . ":\n";
          $bbCode .= "\n[color=Brown][i]" . $player [0][0] . "[/i][/color] " . $player [0][1] . "[list]";
        }
        foreach ($player as $item) {
          $normal .= " - (" . $item [4] . "." .
            ($item [5] < 10 ? "0" : "") .
            $item [5] . ") " . $item [2] . " passed " .
            $item [6] . " to " . $item [3] . ".\n";
          $bbCode .= "[*][i](" . $item [4] . ".[size=9]" .
            ($item [5] < 10 ? "0" : "") .
            $item [5] . "[/size])[/i] " .
            ($multi ? "[color=" . $colours [$item [7]] . "]" . $item [2] . "[/color]" : $item [2]) . " passed " .
            "[i]" .
            ($multi ? $item [6] : "[color=Brown]" . $item [6] . "[/color]") . "[/i] to " .
            ($multi ? "[color=" . $colours [$item [8]] . "]" . $item [3] . "[/color]" : $item [3]) . ".\n";
        }
        $normal .= "\n";
        $bbCode .= "[/list]\n";
      }
    }
    if ($normal) {
      return "Resources/items Passing Cooperation Daily Report\n\n" .
      $normal . "\n" .
      "Code below is for forums for better look.\n\n" . $bbCode;
    } else {
      return "Resources/items Passing Cooperation Daily Report\n\n" .
      "Hooray! No cooperation happened!";
    }
  }

  /**
   * Gets an array of messages in format: "dddd-dd:dd Char XXX says : ..." and groups them by char id taken from the string.
   * @param string[] $messages array of strings being ooc texts said by characters
   * @return string[]
   */
  public static function groupMessagesByCharId($messages)
  {
    $messagesByChar = []; // key: char id, value: array this char's ooc messages
    foreach ($messages as $message) {
      $matches = null;
      if (preg_match("/^.* Char (\d+) says : .*$/", $message, $matches)) {
        $charId = $matches[1];
        $messagesByChar[$charId][] = $message;
      }
    }

    return Pipe::from($messagesByChar)->map(function($messageArray) {
      return implode("\n", $messageArray); // merge array of arrays into flat array of ooc strings
    })->toArray();
  }
}