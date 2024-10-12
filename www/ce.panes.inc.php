<?php

SkipLog ();
$db = Db::get();
switch ($type) {
  case "loc": case "veh": case "bld":
    echo "
   <table width=\"700\" cellSpacing=0 cellPadding=0><tr>

     Please enter search text and chose from listed below: 
     <input length=70 size=40 onKeyUp=\"EditChange (this.value, '$type')\">

   </td><td align=right><small id='ajaxstat'>READY</small></td></tr>
   <tr>
   <td class=cex vAlign=bottom colSpan=2 align=right>
     <label><input type=\"checkbox\"	id=\"useCustomNames\" /> Search in players' custom names (slow - may take 10-20 seconds)</label>
     <br />
     <label><input type=\"checkbox\"	id=\"searchById\" />Search by location ID rather than name</label>
   </td></tr>
   </table>
   <hr>";	
  break;
  
  case "plr":
  ?>
  <script type="text/javascript" language="javascript">
  <!--

  -->
  </script>
  <?php
    echo
      "<table width=700><tr><td>
        Please enter search criteria: 
       </td><td align=right><small id='ajaxstat'>READY</small></td></tr>
       </table><table width=700>
       <tr>
         <td>ID: <input length=6 size=6 id=\"plyrID\" onKeyUp=\"StartSearch ()\" /></td>
         <td align=center>First name: <input length=20 size=20 id=\"plyrFirstName\"></td>
         <td align=center>Last name: <input length=20 size=20 id=\"plyrLastName\"></td>
         <td align=right>Language: <select id=\"plyrLang\">
         <option value=\"0\" selected>any</option>";
    $stm = $db->query("SELECT id, name FROM languages");
    while (list ($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<option value=\"$id\">$name</option>\n";
    }
    echo 
      "  </select>
       </tr>
       </table><table width=700 style='border-bottom: 1px solid #888888'>
       <tr>
         <td>E-mail: <input length=40 size=20 id=\"plyrEmail\"></td>
         <td align=center>IP address: <input length=40 size=15 id=\"plyrIP\"></td>
         <td align=center>Status: <select id=\"plyrStatus\">
           <option value=\"0\" selected>any</option>
           <option value=\"a\">active</option>
           <option value=\"l\">locked</option>
         </select></td>
         <td align=right>
           <a href=\"JavaScript: ClearPlyrSearch ()\" class='linkbutt'>Clear</a>
           <a href=\"JavaScript: StartPlyrSearch ()\" class='linkbutt'>Search</a></td>
       </tr>
       </table>";

  break;
  
  case 'chars':
    $stm = $db->prepare("SELECT day FROM turn");
    $today = $stm->executeScalar();
?>    
      <table width=700 style='border-bottom: 1px solid #888888'>
      <tr>
        <td colspan='4'>
              <script type="text/javascript" >
        var todayCantrDay = <?php echo $today; ?>;
      </script>
          Please enter search criteria: 
        </td>
          <td align=right style="width:140px" >
            <small id='ajaxstat'>READY</small>
          </td>
       </tr>
       
       <tr>
         <td>Char ID: <input length=6 size="8" id="charID" onKeyUp="DelayedRunFunction ( 'StartCharsSearch' )"  title="Searching by character id"/></td>
         <td align=center>Player id: <input length='15' size='10' onKeyUp="DelayedRunFunction ( 'StartCharsSearch' )"  id="charPlayerId" title="Searching by owner player id"></td>
         <td align=center colspan="3">Name: <input length='15' size="12" id="characterName" onKeyUp="DelayedRunFunction ( 'StartCharsSearch' )" title="Searching by character true name" >
         Know as: <input length='15' size="17" id="charKnowAs" onKeyUp="DelayedRunFunction ( 'StartCharsSearch' )"  title="Searching by names others have named a character"></td>
                          
       </tr>
       <tr>
         <td>Age: <input maxlength="4" size="2" id="ageFrom" value="20"><input maxlength="4" size="2" id="ageTo" value='1000'></td>
         <td align="center">Born: 
            <input maxlength="10" size="3" id="bornFrom" value='0' title="Minimal characters age">
            <input maxlength="10" size="3" id="bornTo" value="<?php echo $today ?>" title="Maximal character age"></td>
         <td align=center>Status: <select id="charStatus" style="width:80px">           
           <option value="0">pending</option>
           <option value="1" selected>active</option>
           <option value="2">being buried</option>
           <option value="3">buried</option>
           <option value="-1">any</option>
         </select>
         </td>
         <td align="center">Loc id: <input maxlength="10" size="10" id="charLocId" title="Id of characters location"></td>
          <td align="right">Lang: <select id="charLang" title="Character language choosed by player">
         <option value="0" selected>any</option>"
<?php
    $stm = $db->query("SELECT id, name FROM languages");
    while (list ($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<option value=\"$id\">$name</option>\n";
    }
?>
    
      </select>
      </td>
       </tr>
       <tr>
         <td align="right" colspan="5" style='padding:6px'>
           <a href="JavaScript: ClearCharsSearch ()" class='linkbutt'>Clear</a>           
           <a href="JavaScript: StartCharsSearch ()" class='linkbutt'>Search</a>
         </td>
       </tr>
       </table>
<?php
        
    
  break;
}

?>
