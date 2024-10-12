{include file="template.title.[$lang].tpl" title="[$project_repairing] $itemname"}

<div class="page">
<table>
  <TR>
    <TD COLSPAN=2>
      [$page_repair_1]<BR>
    </TD>
  </TR>
  <FORM METHOD=POST ACTION="index.php?object_id={$object_id}&page=executerepair&fullRepair={$fullRepair}">
    <INPUT TYPE=hidden NAME="object" VALUE="{$itemname}">
    <INPUT TYPE=hidden NAME="data" VALUE="yes">
  <TR>
    <TD>
      [$form_repair_amount]:
    </TD>
    <TD>
      <INPUT TYPE=text NAME="repairhours" SIZE=30 VALUE="{$fullRepair}">[$hours]
    </TD>
  </TR>
  <TR>
    <TD HEIGHT=100 WIDTH=50% align="center" colspan="2">
      <a href="index.php?page=char.inventory"><img SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"></a>
      <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </TD>
  </TR>  
  </FORM>
</TABLE>
</div>