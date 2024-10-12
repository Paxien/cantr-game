<script type="text/javascript" src="[$JS_VERSION]/js/front_page.js"></script>

<div class="front-page-root" style="margin-top:2px;">
    <div class="main-column">
      <p class="front-page-title-big">[$intro_main_title]</p>
      <p class="front-page-title-medium">[$intro_subtitle]</p>

      <p class="front-page-txt">[$intro_text_1]</p>
      <div class="ctaContainer">
        <a href="index.php?page=adduser&referrer={$referrer}" class="ctaLink"><div class="cta ctaWithArrow">[$intro_link_register]</div></a>
      </div>
      <p class="front-page-video">[$intro_video]</p>
      <p class="front-page-txt">[$intro_text_2]</b></p>
      <p class="front-page-txt">[$intro_text_3]</p>
      <p style="text-align:right;" class="front-page-txt">[$intro_text_gamemaster]</p>
    </div>
    <div class="menu-column">
      <div class="small-rect">
        <div class="small-rect-header">
          [$intro_select_language]
        </div>
        <div id="lang_dock" class="lang-sel">
          <ul style="list-style-type:none;margin:0px;">
            <li onclick="toggleLangList()" style="cursor:pointer; list-style-image: url('../graphics/cantr/pictures/flags/{$langs.$l.en_name}.gif')">{$langs.$l.original_name}
            <span style="float:right;"> <b>∇</b></span></li>
          </ul>
        </div>
      </div>
      <div id="lang_div" class="lang-sel" style="position:absolute;display:none;">
        <ul style="list-style-type:none;margin:0px;">
      {foreach from=$langs item=lang key=lang_id}
        <li onclick="setLanguage('{$lang.lang_abr}', 'intro')"
            style="cursor:pointer;list-style-image: url('../graphics/cantr/pictures/flags/{$lang.en_name}.gif')"
        >{$lang.original_name}</li>
      {/foreach}
        </ul>
      </div>
      <div class="small-rect" style="text-align:center">
        <div class="small-rect-header">
          <a href="index.php?page=login">[$intro_category_login]</a>
        </div>
        <div class="front-page-login-box">
        <form method="post" action="/index.php?page=login&noformat=1">
        <input type="hidden" name="data" value="yes">

          <div>
            [$intro_login] <input type="text" size="10" name="id">
          </div>
          <div >
            [$intro_password] <input type="password" size="10" name="password">
          </div>
          <div>
            <a href="index.php?page=passreminder" style="font-size:8pt;">[$intro_link_passreminder]</a>
            <input type="submit" value="Login" class="button_charmenu">
          </div>
        </form>
        </div>
      </div>
      <div class="small-rect">
        <div class="small-rect-header">
          [$intro_category_content]
        </div>
        <ul class="front-page-links">
          <li><a href="http://forum.cantr.org/">[$intro_link_forum]</a></li>
          <li><a href="[$intro_wiki_language_link]" target="_blank">[$intro_link_wiki]</a></li>
          <li><a href="[$tutorial_link]" target="_blank">[$intro_link_tutorial]</a></li>
          {*<li><a href="">[$intro_link_screenshots]</a></li> *}
          <li><a href="[$link_irc]" target="_blank">[$intro_link_irc]</a></li>
          <li><a href="index.php?page=contact">[$intro_link_contact]</a></li>
        </ul>
      </div>
      <div class="small-rect">
        <div class="small-rect-header">
          [$intro_category_news]
        </div>
        <div id="twitter_div">
          <a class="twitter-timeline" data-width="100%" data-height="600" data-theme="dark" href="https://twitter.com/cantrii?ref_src=twsrc%5Etfw">Tweets by cantrii</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        </div>
      </div>
    </div>
</div>
