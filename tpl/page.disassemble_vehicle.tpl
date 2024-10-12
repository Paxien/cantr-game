{include file="template.title.[$lang].tpl" title="[$title_vehicle_disassembling]"}

<div class="page">
  <CANTR REPLACE NAME=text_vehicle_disassembling VEHICLE={$vehicleId}>

  <div class="centered">
    <form method="post" action="index.php?page=disassemble_vehicle&data=yes">
      <input type="hidden" name="target" value="{$vehicleId}">
      <a href="index.php?page=char.buildings"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]" /></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_vehicle_disassembling]">
    </form>
  </div>
</div>
