{include file="template.title.[$lang].tpl" title="[$title_player_menu]"}

<div id="player_menu" class="page">
{* if we add "noformat=1" to link, our urlencoding mechanism doesn't add any additional parameters to it *}
  <a href="index.php?page=player&noformat=1">
    <img src="[$_IMAGES]/button_back2.gif" title="[$alt_go_back_to_pp]"></a>
  <a href="http://wiki.cantr.net" target="_blank">
    <img src="[$_IMAGES]/button_help.gif" title="[$alt_get_more_info]"></a>
  <a href="index.php?page=logout">
    <img src="[$_IMAGES]/button_logout.gif" title="[$alt_log_out]"></a>
</div>
