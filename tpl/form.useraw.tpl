{include file="template.title.[$lang].tpl" title="[$title_use] $RAWNAME"} 


<FORM METHOD=POST ACTION="index.php?page=useraw">
<div class="page">
<table>
  <TR>
    <TD COLSPAN=2>
    [$form_useraw]
    </TD>
  </TR>
  <TR>
    <TD WIDTH=300>
      <INPUT TYPE=hidden NAME="needamount" VALUE="{$NEEDED}">
      <INPUT TYPE=hidden NAME="project" VALUE="{$project}">
      <INPUT TYPE=hidden NAME="object_id" VALUE="{$object_id}">
      <INPUT TYPE=hidden NAME="data" VALUE="yes">
      [$form_useraw_amount]
    </TD>
    <TD>
      <INPUT TYPE=text SIZE=50 NAME="amount" VALUE="{$max}">
    </TD>
  </TR> 
  <TR>
    <TD HEIGHT=100 WIDTH=50% ALIGN=right>
      <a href="index.php?page={$page}&object_id={$object_id}"><img SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"></a>
    </TD>

    <TD HEIGHT=100 WIDTH=50% ALIGN=left>
      <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_use_the_resource]">      
    </TD>
  </TR>
</TABLE>
</div>
</FORM>