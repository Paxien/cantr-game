{include file="template.title.[$lang].tpl" title="[$title_finance_overview]"}

<div class="page">
<table>
  <TR>
    <TD COLSPAN=4>
      [$fin_currency]<BR><BR>
    </TD>
  </TR>
  <TR>
    <TD>
      <B>[$fin_month]</B>
    </TD>
    <TD>
      <B>[$fin_category]</B>
    </TD>
    <TD ALIGN=right>
      <B>[$fin_incomes]</B>
    </TD>
    <TD ALIGN=right>
      <B>[$fin_outcomes]</B>
    </TD>
  </TR>
{foreach from=$Months item=Month}
  <TR>
    <TD>{$Month->month}</TD>
    <TD>[$fin_fees]</TD>
    <TD></TD>
    <TD ALIGN=right>{$Month->transactionfees}</TD>
  </TR>
  <TR>
    <TD></TD>
    <TD>[$fin_rental]</TD>
    <TD></TD>
    <TD ALIGN=right>{$Month->serverrental}</TD>
  </TR>
  <TR>
    <TD></TD>
    <TD>[$fin_dmn_name]</TD>
    <TD></TD>
    <TD ALIGN=right>{$Month->domainname}</TD>
  </TR>
  <TR>
    <TD></TD>
    <TD>[$fin_marketing]</TD>
    <TD></TD>
    <TD ALIGN=right>{$Month->marketing}</TD>
  </TR>
  <TR style="background-color:#00AA00">
    <TD></TD>
    <TD>[$fin_advertisements]</TD>
    <TD ALIGN=right>{$Month->advertisements}</TD>
    <TD ALIGN=></TD>
  </TR>
  <TR style="background-color:#00AA00">
    <TD></TD>
    <TD>[$fin_p_donations]</TD>
    <TD ALIGN=right>{$Month->periodicdonations}</TD>
    <TD ALIGN=right></TD>
  </TR>
{/foreach}
  <TR>
    <TD><BR>[$fin_total]</TD>
    <TD></TD>
    <TD ALIGN=right><BR><B>{$TotalIn}</B></TD>
    <TD ALIGN=right><BR><B>{$TotalOut}</B></TD>
  </TR>
  <TR>
    <TD>[$fin_original_bal]</TD>
    <TD></TD>
    <TD ALIGN=right>{$OriginalBalance}</TD>
    <TD></TD>
  </TR>
  <TR>
    <TD>[$fin_current_bal]</TD>
    <TD></TD>
    <TD ALIGN=right>{$CurrentBalance}</TD>
    <TD></TD>
  </TR>
</TABLE>

<div class="centered">
  <A HREF="index.php?page=player">[$back_to_player]</A>
</div>
</div>
