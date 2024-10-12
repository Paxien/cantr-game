<div class="page">
  <p>
    <CANTR REPLACE NAME=charsettings_death_old_age AGE={$OLD_AGE_DEATH_MIN_YEARS}>
  </p>
  <p>
    {if $isOldEnoughToDie}
      [$charsettings_death_old_enough]
      <br>
    {/if}
    <label style="cursor:pointer;">
      <input type="checkbox" name="old_age_death" value="1"
          {if $isGoingToDie} checked="checked"{/if}{if !$isOldEnoughToDie} disabled="disabled"{/if}> [$charsettings_button_death]
    </label>
  </p>
</div>
