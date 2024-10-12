{include file="template.title.[$lang].tpl" title="[$title_login]"}
<form name="loginform" method="post" action="/index.php?page=login&noformat=1">
<div class="page">
  <table>
    <tr>
      <td colspan=2>
        [$page_login_1]
      </td>
    </tr>
    <tr>
      <td width="200">
        <br>[$form_id]:
      </td>
      <td>
        <br>
        <input name="id" id="accountId">
      </td>
    </tr>
    <tr>
      <td>
        <br>[$form_password]:
      </td>
      <td>
        <input type="password" name=password>
      </td>
    </tr>
    <tr>
      <td align="center" colspan="2">
        <input type="hidden" name="data" value="yes"><br>
        <input type="image" src="[$_IMAGES]/button_login.gif" value="login"><br>
        <a HREF="index.php?l={$l}">[$page_login_backtostart]</a>
        <p>
          <a href="index.php?page=passreminder">[$page_forgotten_password]</a>
        </p>
      </td>
    </tr>
  </table>
</div>
</form>

<script type="text/javascript">
  $("#accountId").focus();
</script>
