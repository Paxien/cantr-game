{include file="template.title.[$lang].tpl" title="EDIT OBJECT TYPE INFO ({$objecttype_info->id})"}

<style>
  .form-grid {
    display: grid;
    grid-template-columns: auto auto;
    gap: 10px;
    max-width: 700px;
  }

  .grid-item {
    width: 100%; text-align: left;
    margin-bottom: 10px; /* Adjust this to change the amount of margin */
  }

  .m-0 {
    margin-bottom: 0px;
  }
</style>

<form method="post" action="index.php?page=manageobjects&func=form" id="objecttypeForm">
  <div class="page">
    <div class="grid">

      <div class="grid-item m-0">
        <strong>Name:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->name}<br><input type="text" name="name" value="{$objecttype_info->name}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Identifying unique name:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->unique_name}<br><input type="text" name="unique_name" value="{$objecttype_info->unique_name}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Show instructions outside:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->show_instructions_outside}<input type="text" name="show_instructions_outside"
         value="{$objecttype_info->show_instructions_outside}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Show instructions inventory:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->show_instructions_inventory}<input type="text" name="show_instructions_inventory"
         value="{$objecttype_info->show_instructions_inventory}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Build conditions:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->build_conditions}<input type="text" name="build_conditions"
        value="{$objecttype_info->build_conditions}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Build project description:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->build_description|escape} <br>Use only variables asked in build form.<br>
        <input type="text" name="build_description" value="{$objecttype_info->build_description|escape}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Build requirements:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->build_requirements}<input type="text" name="build_requirements"
        value="{$objecttype_info->build_requirements}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Build result:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->build_result}<input type="text" name="build_result" value="{$objecttype_info->build_result}"
                                                      size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Weight of Object:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->project_weight}<input type="text" name="project_weight"
        value="{$objecttype_info->project_weight}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Build/repair skill type:</strong>
      </div>
      <div class="grid-item">
        <select name="skill">
          {foreach $skills as $skillId => $skillName}
            <option value="{$skillId}" {if $objecttype_info->skill == $skillId} selected{/if}>{$skillName}</option>
          {/foreach}
        </select>
      </div>

      <div class="grid-item m-0">
        <strong>Rules:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->rules}<br>
        <input type="text" id="rules_input" name="rules" value="{$objecttype_info->rules}" size="70"><br>
        <span id="rules_errors" style="color:red"></span>
      </div>

      <div class="grid-item m-0">
        <strong>Properties:</strong>
      </div>
      <div class="grid-item">
          {foreach $props as $property}
            <div class="grid-item">
            <input type="text" name="propNames[]" size="15" value="{$property->property_type|escape}">:
            <input type="text" name="propDetails[]" size="50" value="{$property->details|escape}"><span class="errorText"></span></div>
          {/foreach}
      </div>

      <div class="grid-item m-0">
        <strong>Subtable:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->subtable}<input type="text" name="subtable" value="{$objecttype_info->subtable}" size="70">
      </div>

      <div class="grid-item m-0">
        <strong>Deterioration per day:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->deter_rate_turn}<input type="text" name="deter_rate_turn"
         VALUE="{$objecttype_info->deter_rate_turn}" size="10">
      </div>

      <div class="grid-item m-0">
        <strong>Deterioration per use:</strong>
      </div>
      <div class="grid-item">
        was: {$objecttype_info->deter_rate_use}<input type="text" name="deter_rate_use"
        value="{$objecttype_info->deter_rate_use}" size="10">
      </div>

      <div class="grid-item m-0">
        <strong>Deterioration state visible:</strong>
      </div>
      <div class="grid-item">
        <select name="deter_visible">
        {if $objecttype_info->deter_visible == 1}
          <option value="1" selected>yes</option>
          <option value="0">no</option>
        {else}
          <option value="1">yes</option>
          <option value="0" selected>no</option>
        {/if}
        </select>
      </div>

      <div class="grid-item m-0">
        <strong>Repair rate per hour:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->repair_rate}<input type="text" name="repair_rate" value="{$objecttype_info->repair_rate}" size="10">
      </div>

      <div class="grid-item m-0">
        <strong>Build Menu category:</strong>
      </div>
      <div class="grid-item">
        <select name="objectcategory">
          {foreach $objectcategories as $objectcategory_info}
          <option value="{$objectcategory_info->id}" {if $objectcategory_info->id == $objecttype_info->objectcategory}selected{/if}>
            {$objectcategory_info->name} {if $objectcategory_info->status} NOT BUILDABLE{/if}
            {/foreach}
        </select>
      </div>

      <div class="grid-item m-0">
        <strong>Is object visible to others?</strong>
      </div>
      <div class="grid-item">
        <select name="visible">
          {if $objecttype_info->visible == 1}
          <option value="1" selected>yes</option>
          <option value="2">no</option>
            {else}
          <option value="1">yes</option>
          <option value="2" selected>no</option>
            {/if}
        </select>
      </div>

      <div class="grid-item m-0">
        <strong>Comments (will not be saved, only end up in the email; please state the changes you made and its purpose):</strong>
      </div>
      <div class="grid-item">
        <textarea name="comments" cols="50" rows="7"></textarea>
        <br><button id="useLastComment">use last comment</button>
      </div>

      <div class="grid-item m-0">
        <strong>Picture file:</strong>
      </div>
      <div class="grid-item">
        Was: {$objecttype_info->image_file_name}<input type="text" name="image_file_name"
        value="{$objecttype_info->image_file_name}" size="80">
      </div>

      <div class="grid-item m-0">
        <strong>Picture:</strong>
      </div>
      <div class="grid-item">
        {$image}
      </div>

      <div class="grid-item"></div>
      <div class="grid-item">
        <input type="hidden" name="data" value="yes">
        <input type="hidden" name="object" value="{$object}">

        <br>
        <input type="submit" value="store">
      </div>
    </div>
  </form>
</div>


<script type="text/javascript" src="[$JS_VERSION]/js/admin/objecttypes.js"></script>
