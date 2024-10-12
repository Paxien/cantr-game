<div class="centered">
{foreach from=$categories key=cat item=cat_class}
  <a href="{$catLink}{$cat}" class="button_charmenu{if $cat==$curr_cat}active{/if}"><CANTR REPLACE NAME=link_category_{$cat}></a>
{/foreach}
</div>