<?php

class BottomMenus
{
  /** @var Character */
  private $char;

  public function __construct(Character $char)
  {
    $this->char = $char;
  }

  public function show()
  {
    $smarty = new CantrSmarty();
    $smarty->assign("canManufacture", $this->char->getLocation() > 0);
    $smarty->displayLang("page.char.menu.tpl", $GLOBALS['lang_abr']);
    $smarty->displayLang("page.player.menu.tpl", $GLOBALS['lang_abr']);
  }
}