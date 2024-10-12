{include file="template.title.[$lang].tpl" title="[$title_build_menu]"}

<div class="page">
  <table id="ajaxtable" name="ajaxtable" style="display: none;">
    <tr valign="baseline">
      <td>
        [$search_for]
      </td>
      <td align="right">
        <input name="stxt" id="stxt" size="20" onkeydown="SearchKeyPress (event)"/>
        <input type="submit" value="X" onclick="SearchClear()"/>
        <input type="submit" value="[$button_search]" onclick="SearchStart()"/>
        <br/><br/>
      </td>
    </tr>
    <tr>
      <td id="treeRoot" colSpan="2">
        <div align="center" style="padding-top: 100px; padding-bottom: 100px;">[$please_wait]</div>
      </td>
    </tr>

    <tr>
      <td colspan="2" align="right">
        <a href="index.php?page=build&noJavaScript=1">
          <small>Old HTML build menu</small>
        </a>
      </td>
    </tr>
  </table>
  <script src="[$JS_VERSION]/js/ajaxtree.js"></script>
  <script src="[$JS_VERSION]/js/buildmenu.js"></script>
  <script type="text/javascript">
    SessInfo = "&ch={$character}&l={$l}";
    WaitStr = "[$please_wait]";
    $("#ajaxtable").show();
    AjaxRootNode();
  </script>
</div>
