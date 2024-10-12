<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  {if $ownCharName}
  <title>{$ownCharName} | Cantr II</title>
  {else}
  <title>[$cantr_header_title]</title>
  {/if}
  <meta name="description" content="[$meta_description]">
  <meta name="keywords" content="[$meta_keywords]">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  {if $allowResponsiveLayout}
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  {else}
  <meta name="viewport" content="width=820">
  {/if}
  <link rel="manifest" href="/manifest.json">
  {if !$devserverMode}
  {foreach $reactCss as $css}
  <link rel="stylesheet" href="/react/css/{$css}" type="text/css">
  {/foreach}
  {/if}
  {foreach from=$StyleSheets item=CSS}
  <link rel="stylesheet" href="[$JS_VERSION]/css/{$CSS}" type="text/css">
  {/foreach}
  <link rel="apple-touch-icon" href="[$_IMAGES]/touch-icon-iphone.png">
  <link rel="apple-touch-icon" sizes="72x72" href="[$_IMAGES]/touch-icon-ipad.png">
  <link rel="apple-touch-icon" sizes="114x114" href="[$_IMAGES]/touch-icon-iphone.png">
  {if $ENV->is("main") || $ENV->is("intro")} {* players tracking code *}
  {literal}
    <!-- Google Analytics Code -->
    <script type="text/javascript">
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-18970547-1', 'auto');
      ga('send', 'pageview');
    </script>
    <!-- End Google Analytics Code -->

    <!-- Piwik -->
    <script type="text/javascript">
      var _paq = _paq || [];
      _paq.push(['trackPageView']);
      _paq.push(['enableLinkTracking']);
      (function() {
        var u="//cantr.net/";
        // CAUTION! It connects to custom 'cantr.piwik.php'!
        _paq.push(['setTrackerUrl', u+'cantr.piwik.php']);
        _paq.push(['setSiteId', 3]);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <noscript><p><img src="//cantr.net/cantr.piwik.php?idsite=3" style="border:0;" alt="" /></p></noscript>
    <!-- End Piwik Code -->

    <!-- Hotjar Tracking Code for https://cantr.net -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:1517092,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
    <!-- End Hotjar Code -->
	

	<!-- Facebook Pixel Code -->

  <script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window,document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1060334527684709'); 
  fbq('track', 'PageView');
  </script>
  <noscript>
  <img height="1" width="1" 
  src="https://www.facebook.com/tr?id=1060334527684709&ev=PageView&noscript=1"/>
  </noscript>

	<!-- End Facebook Pixel Code -->
    

    <!-- Cookie Consent Banner from https://app.termly.io/ -->
    <script>
    (function () {
      var s = document.createElement('script');
      s.type = 'text/javascript';
      s.async = true;
      s.src = 'https://app.termly.io/embed.min.js';
      s.id = 'd93978c4-eb09-4ded-b78b-756ff928d7aa';
      s.setAttribute("data-name", "termly-embed-banner");
      var x = document.getElementsByTagName('script')[0];
      x.parentNode.insertBefore(s, x);
    })();
    </script>
    <!-- End Cookie Consent Banner -->
  {/literal}
  {/if}
  <script type="text/javascript">
    var translations = {$js_translations};
    var language = {$language};
  </script>
  {if $jQueryNeeded}
    <script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery-ui.min.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery.autosize.min.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/cantr_global.js"></script>
    {if $useLocationExtendedBox}
      <script type="text/javascript" src="[$JS_VERSION]/js/location_extended_box.js"></script>
    {/if}
    {if $showTooltips}
      <link rel="stylesheet" href="[$JS_VERSION]/css/introjs.css" type="text/css">
      <script type="text/javascript" src="[$JS_VERSION]/js/libs/intro.min.js"></script>
      <script type="text/javascript" src="[$JS_VERSION]/js/tooltip_texts.js"></script>
      <script type="text/javascript" src="[$JS_VERSION]/js/intro_tooltips.js"></script>
    {/if}
  {else}
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js"></script>
  {/if}
</head>

<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" id="topBar">
  <tr>
    <td align="center" width="100%">
      <p class="header-title">Cantr II{if $headerSubtitle} <span class="header-subtitle">{$headerSubtitle}</span>{/if}</p>
      <span title="[$timeline_3]" class="small-top" id="datetime">[$form_day] {$turn->day} [$form_time]: {$turn->hour}:{$turn->minute}:{$turn->second}</span>
    </td>
  </tr>
  <script type="text/javascript">
    var day = {$turn->day};
    var hour = {$turn->hour};
    var minute = {$turn->minute};
    var second = {$turn->second};
    var dayText = '[$form_day]';
    var timeText = '[$form_time]';

    var translations = {$js_translations};
  </script>
  <script type="text/javascript" src="[$JS_VERSION]/js/clock.js"></script>
  <tr>
    <td align="center">
      <span class="tiny-top">[$active_players]</span>
    </td>
  </tr>
</table>
{$PageContents}

<div class="centered" id="footer_panel">
  <p>
    <a href="index.php?page=contact">[$footer_link_contact]</a> |
    <a href="index.php?page=departments" target="_blank">[$footer_link_staff]</a> |
    <a href="https://forms.gle/FQRqW9o74YFWYtaT6" target="_blank">[$footer_link_join_staff]</a> |
    <a href="index.php?page=rules" target="_blank">[$footer_link_rules]</a> |
    <a href="index.php?page=terms" target="_blank">[$footer_link_terms]</a> |
    <a href="index.php?page=privacy" target="_blank">[$footer_link_privacy]</a> |
    <a href="index.php?page=cookies" target="_blank">[$footer_link_cookies]</a> |
    <a href="http://forum.cantr.org" target="_blank">[$intro_link_forum]</a> |
    <a href="[$intro_wiki_language_link]" target="_blank">wiki</a> |
    <a href="[$link_irc]" target="_blank">[$intro_link_irc]</a> |
    <a href="http://webzine.cantr.org/" target="_blank">webzine</a> |
    <a href="https://twitter.com/cantrii" target="_blank">twitter</a> |
    <a href="https://www.facebook.com/cantrnet" target="_blank">facebook</a> |
    <a href="https://www.instagram.com/cantr_rpg/" target="_blank">instagram</a>
  </p>
  <p>
    {$TimeStats}
  </p>
</div>

<input type="hidden" id="pageName" value="{$pageName}">
{if $character}
  <input type="hidden" id="ownCharacterId" value="{$character}"/>
{/if}
{if $devserverMode}
<script type="text/javascript" src="http://localhost:3000/static/js/bundle.js"></script>
{else}
{foreach $reactJs as $js}
<script type="text/javascript" src="/react/js/{$js}"></script>
{/foreach}
{/if}
</body>
</html>
