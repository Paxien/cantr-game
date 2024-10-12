<form method="POST" id="registration-form" action="index.php?page=adduser" autocomplete="off" class="responsive-form">
  <input type="hidden" name="referrer" value="{$referrer|htmlentities}"/>
  <div class="page" id="registration-form-wizard">
    <h2 class="responsive-form-header">[$register_header_account]</h2>
    <div class="form-group">
      <p>[$register_about_form_data]</p>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="username-label" for="username">
          [$form_username]:
        </label><input class="responsive-form-value" type="text" size="20" id="username" name="username"
                       value="{$username|htmlentities}"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="email-label" for="email">
          [$form_email]:
        </label><input class="responsive-form-value" type="text" size="20" id="email" name="email"
                       value="{$email|htmlentities}"/>
        <span class="register-nec">*</span>
      </div>
      <p class="reactivation-option">[$register_inactive_account_reactivation]
        <a class="reactivation-link" href="#">[$register_inactive_account_reactivation_link]</a></p>
      <div class="responsive-form-row">
        <label class="responsive-form-label responsive-label-bottom" id="password-label" for="password">
          [$page_adduser_4]:<br>
          <span style="font-size: 8pt">[$register_subtext_password_length]</span>
        </label><input class="responsive-form-value" type="password" size="20" id="password" name="password"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="password_retype-label" for="password_retype">
          [$form_passwordretype]:
        </label><input class="responsive-form-value" type="password" size="20" id="password_retype" name="password_retype"/>
        <input type="hidden" name="data" value="yes"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="language-label" for="language">
          [$form_language]:
        </label>
        <select id="language" class="responsive-form-value" name="language">
          {foreach $languages as $languageId => $languageName}
            <option value="{$languageId}"{if $languageId == $language} selected="true"{/if}>{$languageName}</option>
          {/foreach}
        </select>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="terms-label" for="terms">
        <input type="checkbox" id="terms" class="responsive-form-value" name="terms"  value="yes">
        </label>
        <span class="responsive-form-value">[$form_accept_terms]<span class="register-nec">*</span></span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="privacy-label" for="privacy">
        <input type="checkbox" id="terms" class="responsive-form-value" name="privacy"  value="yes">
        </label>
        <span class="responsive-form-value">[$form_accept_privacy]<span class="register-nec">*</span></span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="email-accept-label" for="email_accept">
        <input type="checkbox" id="email_accept" class="responsive-form-value" name="email_accept"  value="yes">
        </label><span class="responsive-form-value">[$form_accept_email]<span class="register-nec">*</span></span>
      </div>
    </div>
    <h2 class="responsive-form-header">[$register_header_profile]</h2>
    <div class="form-group">
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="firstname-label" for="firstname">
          [$form_firstname]:
        </label><input class="responsive-form-value" type="text" size="20" id="firstname" name="firstname"
                       value="{$firstname|htmlentities}"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label responsive-label-bottom" id="lastname-label" for="lastname">
          [$form_lastname]:<br>
          <span style="font-size: 8pt">[$register_subtext_lastname]</span>
        </label><input class="responsive-form-value" type="text" size="20" id="lastname" name="lastname"
                       value="{$lastname|htmlentities}"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="age-label" for="age">
          [$form_birthyear]:
        </label><input class="responsive-form-value form-year-value"
                       type="number" id="year" name="year" value="{$year|htmlentities}" placeholder="yyyy"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="country-label" for="country">
          [$form_country]:
        </label><input class="responsive-form-value" type="text" size="20" id="country" name="country"
                       value="{$country|htmlentities}"/>
        <span class="register-nec">*</span>
      </div>
      <div class="responsive-form-row">
        <p>[$register_refplayer_explanation]</p>
        <label class="responsive-form-label responsive-label-bottom" id="refplayer-label" for="refplayer">
          [$register_label_refplayer]<br>
          <span style="font-size: 8pt">[$register_subtext_refplayer]</span>
        </label><input class="responsive-form-value" type="text" size="20" id="refplayer" name="refplayer"
                       value="{$refplayer|htmlentities}"/>
      </div>

      <h2 class="responsive-form-header">[$register_header_reference]</h2>

      <p>[$register_reference_explanation]</p>
      <div class="responsive-form-row">
        <label class="responsive-form-label" id="reference-label" for="reference">
          [$page_adduser_6]:
        </label><input class="responsive-form-value" type="text" size="20" id="reference" name="reference"
                       value="{$reference|htmlentities}"/>
      </div>
      <div class="responsive-form-row">
        <label class="responsive-form-label responsive-label-top" id="comment-label" for="comment">
          [$page_adduser_7]:
        </label><textarea class="responsive-form-value" id="comment" name="comment">{$comment|htmlentities}</textarea>
      </div>
    </div>
    <h2 class="responsive-form-header">[$register_header_characters]</h2>
    <div class="form-group">
      <p>
        [$register_characters_explanation]
      </p>
      <div class="responsive-form-row">
        <fieldset class="character-creation-box">
          <legend>[$register_first_character_title]</legend>
          <div class="responsive-form-row">
            <label class="responsive-form-label" id="charname1-label" for="charname1">
              [$form_name_first_char]:
            </label><input class="responsive-form-value" type="text" size="20"
                           id="charname1" name="charname1" value="{$charname1|htmlentities}"/>
            <span class="register-nec">*</span>
          </div>
          <div class="responsive-form-row">
            <label class="responsive-form-label responsive-label-top" id="sex1-label">
              [$form_sex_first_char]:
            </label>
            <div class="responsive-form-value">
              <label class="responsive-form-value-option"><input type="radio" name="sex1"
                                                                 value="1" {if $sex1 == 1} checked{/if}/>[$form_male]</label>
              <label class="responsive-form-value-option"><input type="radio" name="sex1"
                                                                 value="2" {if $sex1 == 2} checked{/if}/>[$form_female]</label>
            </div>
            <span class="register-nec">*</span>
          </div>
        </fieldset>
      </div>
    </div>
  </div>
  <div class="page" style="text-align: right">
    <a class="ghostButton register-back-button" style="display: inline-block"
       href="index.php?page=adduser&referrer={$referrer|htmlentities}">« [$plain_button_back]</a>
    <div style="display: inline-block" class="register-next-button">
      <button class="cta ctaWithArrow">[$plain_button_next]</button>
    </div>
  </div>
</form>

<link rel="stylesheet" href="[$JS_VERSION]/css/registration-form.css"/>
<script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery.validate.min.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/libs/jquery.steps.js"></script>

<script type="text/javascript">
  var englishLanguageNames = {$englishLanguageNames};
</script>
<script type="text/javascript" src="[$JS_VERSION]/js/registration-form.js"></script>
