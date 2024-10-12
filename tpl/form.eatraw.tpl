{include file="template.title.[$lang].tpl" title="<CANTR REPLACE NAME=title_eat TYPE=$rawname>"}

<div class="page">
<table>
  <TR>
    <TD COLSPAN=2>
      {if $full}
        [$error_full_stomach]
      {else}
        [$page_eatraw_2]
      {/if}
    </TD>
  </TR>
	<TR>
    <TD COLSPAN=2>
      [$page_eatraw_3]
        <ul>
            {if $dmg100}<li>[$char_desc_bar_damage]: {$dmg100} % / 100 g</li>{/if}
            {if $hunger100}<li>[$char_desc_bar_hunger]: {$hunger100} % / 100 g</li>{/if}
            {if $drunk100}<li>[$char_desc_bar_drunkenness]: {$drunk100} % / 100 g</li>{/if}
            {if $tired100}<li>[$char_desc_bar_tiredness]: {$tired100} % / 100 g</li>{/if}
        </ul>
    </TD>
  </TR>
	<TR>
    <TD COLSPAN=2>
      {if $nothingToGive}
        [$page_eatraw_nothing]
      {else}
        [$page_eatraw_max]
        <ul>
            {if $healMax}<li>[$page_eatraw_health]: {$healMax} g</li>{/if}
            {if $hungerMax}<li>[$page_eatraw_nutrition]: {$hungerMax} g</li>{/if}
            {if $drunkMax}<li>[$char_desc_bar_drunkenness]: {$drunkMax} g</li>{/if}
            {if $energyMax}<li>[$page_eatraw_energy]: {$energyMax} g</li>{/if}
        </ul>
      {/if}
    </TD>
  </TR>
	{if $full==0}
  <FORM METHOD=POST ACTION="index.php?page=eatraw">
  <TR>
    <TD WIDTH=300>
      <INPUT TYPE=hidden NAME="object_id" VALUE="{$object_id}">
      <INPUT TYPE=hidden NAME="max" VALUE="{$max}">
      <INPUT TYPE=hidden NAME="data" VALUE="yes">
      [$page_eatraw_amount]
    </TD>
    <TD WIDTH=400>
      <INPUT TYPE=text SIZE=50 NAME=amount>
    </TD>
  </TR>
  <TR>
    <TD COLSPAN=2 ALIGN=center>
      <BR>
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$button_char_go_back]"></a>
      <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$button_continue]" >
    </TD>
  </TR>
  </FORM>
	{else}
	<tr>
	  <td colspan=2 align="center">
			<a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$button_char_go_back]"></a>			
      </FORM>
		</td>
	</tr>
	{/if}
</TABLE>
</div>
