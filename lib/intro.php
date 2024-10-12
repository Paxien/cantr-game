<?php

if( isset( $s ) && !empty( $s ) ) {
  redirect("player");
  return;
}

  $smarty = new CantrSmarty();
  
  $smarty->assign("langs", LanguageConstants::$LANGUAGE);
  $smarty->assign("l", $l);
  $smarty->assign("referrer", $referrer);

  $smarty->displayLang("page.intro.tpl", $lang_abr);

?>
