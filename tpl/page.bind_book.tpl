{include file="template.title.[$lang].tpl" title="<CANTR REPLACE NAME=title_bind_book>"}

<div class="page">
  <p class="centered"><b>[$form_bind_book_text]</b></p>

  <form action="index.php?page=bind_book&data=yes" method="POST">
    [$form_book_title]: <input type="text" name="book_title" size="50" value="{$book_title}" />
    <input type="hidden" name="object_id" value="{$object_id}" />
    <div class="centered">
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </div>
  </form>
</div>
