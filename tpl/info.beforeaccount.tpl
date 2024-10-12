{include file="template.title.[$lang].tpl" title="[$title_newbie_guide]"}

<div class="page">
  [$page_newbie_guide_1]
  <form method="post" action="index.php?page=adduser&f=yes&referrer={$referrer}">
    <input type="hidden" name="tick" value="1"/>
    <div class="responsive-form-row">
      <div class="ctaContainer register-next-button">
        <div class="ctaLink">
          <button class="cta ctaWithArrow">[$page_newbie_guide_2]</button>
        </div>
      </div>
    </div>
    <div class="centered">
      [$page_newbie_guide_3]
    </div>
    <div>
      <a class="ghostButton" href="index.php?page=intro&referrer={$referrer|htmlentities}">« [$plain_button_back]</a>
    </div>
  </form>
</div>
