{include file="template.title.[$lang].tpl" title="[$title_alter_book_title]"}

<div class="page">
  <p class="centered"><b>[$form_alter_book_title]</b></p>

  <form action="index.php?page=alter_book_title&data=yes" method="POST">
    [$form_book_title]: <input type="text" name="book_title" size="50" value="{$book_title|escape}"/>
    <input type="hidden" name="object_id" value="{$object_id}" />
    <div class="centered">
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </div>
  </form>
</div>
