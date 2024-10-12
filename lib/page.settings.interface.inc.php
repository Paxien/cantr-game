<?php

class SettingsInterface
{

  public $template_name = "page.settings.interface.tpl";

  public function getSmarty()
  {
    global $player;

    $skin = new SkinHandler($player);

    if ($_REQUEST['data']) {
      PlayerSettings::getInstance($player)->save();
      $is_custom = $_REQUEST['custom_css'];

      $base_css = $_REQUEST['selected_skin'];
      $custom_css = $_REQUEST['custom_css_text'];

      $is_custom = $is_custom ? true : false;
      $skin->setSkin($is_custom, $base_css, $custom_css);
    }

    $smarty = new CantrSmarty;
    $smarty->assign("optionsList", PlayerSettings::getInstance($player)->getOptionsList());
    $smarty->assign("skinsList", $skin->getAvailableSkins());
    $smarty->assign("selectedSkin", $skin->getSelectedSkinName());
    $smarty->assign("isCustomSkin", $skin->isCustom());
    $smarty->assign("customSkin", $skin->getCustomSkinText());

    return $smarty;
  }

}
