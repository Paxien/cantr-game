{include file="template.title.[$lang].tpl" title="[$title_password_reminder]"}

<div class="page">
  {if $show == "password_sent"}
    [$password_sent]
    <br/>
  {elseif $show == "hash_sent"}
    [$password_request_sent]
    <br/>
  {else}
    <form method="post" action="index.php?page=passreminder">
      <table>
        <tr>
          <td colspan=2>
            [$password_request_form]<br/><br/>
          </td>
        </tr>
        <tr>
          <td width="200">
            [$password_request_form_email]:
          </td>
          <td>
            <input type="text" name="email">
          </td>
        </tr>
      </table>
      <div class="centered">
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="Send"><br/><br/>
        <a href="index.php?page=intro">[$page_login_backtostart]</a>
      </div>
    </form>
  {/if}
</div>

