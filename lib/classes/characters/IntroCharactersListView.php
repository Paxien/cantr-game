<?php

class IntroCharactersListView extends CharactersListView
{
  public function __construct(Player $player, Db $db)
  {
    parent::__construct($player, $db);
    $this->setCharIdDataKey("introCharId");
  }

  protected function getLinkToCharacter($char_info)
  {
    return "index.php?page=login_on_intro&after=" .
      urlencode("char.events&character={$char_info->id}&noformat=1");
  }
}
