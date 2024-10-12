<?php

$plr = Request::getInstance()->getPlayer();
if (!$plr->hasAccessTo(AccessConstants::MANUAL_EVENT_CREATOR_TOOL)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$message = $_REQUEST['message'];

/**
 * @author psychowico
 */

show_title ("MANUAL EVENTS BROADCASTER");
?>

<div style="width:500px;height:550px; margin:auto">

<?php if( isset( $message ) ): ?>
  <div style="height:20px;padding:5px;border:1px solid green;margin:5px 5px 10px 5px;color:yellowgreen">
    <?=urldecode( $message ) ?>
  </div>
<?php endif; ?>

<script language="javascript" type="text/javascript">
    function showOption( type )
    {
        $('#options_location').hide();
        $('#options_character').hide();
        $('#options_language_group').hide();
        $(type).show();
    }
</script>

<form action="index.php?page=manual_events" method="post">
<input type="hidden" name="data" id="data" value="yes" />

<fieldset>
    <legend>Broadcast type</legend>

    <input type="radio" name="broadcast_target" id='broadcast_target1' checked="checked" value="location"
        onclick="showOption('#options_location')" />
    <label for="broadcast_target1"> to location</label>

    <input type="radio" name="broadcast_target" id='broadcast_target2' value="character"
           onclick="showOption('#options_character')"/>
    <label for="broadcast_target2"> to character</label>

    <input type="radio" name="broadcast_target" id='broadcast_target3' value="language_group"
           onclick="showOption('#options_language_group')"/>
    <label for="broadcast_target3"> to a entire language group</label>

</fieldset>

<br />

<fieldset id='options_location'>

<legend>
  Location broadcasting options
</legend>

<dl>

<dt><label for="location_id">Location id:</label></dt>
<dd>
    <input type='text' name='location_id' id='location_id'/>
</dd>

<dt><label for="broadcast_language_loc">Language:</label></dt>
<dd>
  <select name="broadcast_language_loc" id="broadcast_language_loc">
    <option value="0"> All </option>
    <?php foreach( $langcode as $code => $languageName ) : ?>
    <option value="<?=$code?>"> <?=$languageName ?> </option>
    <?php endforeach; ?>
  </select>
</dd>

<dt><label for="recursively">Recursively (in buildings and ships):</label></dt>
<dd>
    <input type="checkbox" name="recursively" id='recursively' checked="checked" />
</dd>

</fieldset>

<fieldset id='options_character' style="display:none">

<legend>
    Character broadcasting options
</legend>

<label for="character_id">Character id:</label>
<input type='text' name='character_id' id='character_id' />

</fieldset>

<fieldset id='options_language_group' style="display:none">

    <legend>
        Language group broadcasting options
    </legend>

    <label for="broadcast_language">Language:</label>
    <select name="broadcast_language" id="broadcast_language">
      <option value="0"> All </option>
      <?php foreach( $langcode as $code => $languageName ) : ?>
      <option value="<?=$code?>"> <?=$languageName ?> </option>
      <?php endforeach; ?>
    </select>

</fieldset>


<br />



<fieldset>
  <legend>Text to broadcasting</legend>
  <textarea rows="6" cols="56" name='text_to_broadcast' id='text_to_broadcast'></textarea>

</fieldset>

<br />

<input type="submit" value="Broadcast" onclick="return confirm('Are you really sure?');"/>

</form>
</div>

<div style="width: 100%; position: fixed; bottom: 10px; left: 10px;">
  <a href="index.php?page=player">
    <img src="<?php echo _IMAGES; ?>/button_back2.gif" title="Back"></a>
</div>
