<?php define( 'PAGEHREF', "index.php?page=votinglinks"); ?>
<script type="text/javascript">

function orderUp( uid ) {
  var href = '<?php echo PAGEHREF . "&action=reorder&orderdir=-1" ?>';
  
  var hiddenField = document.createElement("input");
  hiddenField.setAttribute("type", "hidden");
  hiddenField.setAttribute("name", "uid");
  hiddenField.setAttribute("value", uid );

  document.forms['mainform'].appendChild( hiddenField );
    
  document.forms['mainform'].action = href;
  document.forms['mainform'].submit();
}

function orderDown( uid ) {
  var href = '<?php echo PAGEHREF . "&action=reorder&orderdir=1" ?>';
  
  var hiddenField = document.createElement("input");
  hiddenField.setAttribute("type", "hidden");
  hiddenField.setAttribute("name", "uid");
  hiddenField.setAttribute("value", uid );

  document.forms['mainform'].appendChild( hiddenField );
      
  document.forms['mainform'].action = href;
  document.forms['mainform'].submit();
} 

</script>

<?php

$linkname = HTTPContext::getRawString('linkname');
$linkhref = HTTPContext::getRawString('linkhref');
$orderdir = HTTPContext::getInteger('orderdir');

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo((_ENV == 'www') ? "46" : "45")) {
  redirect("player");
  exit;
}

$panel = new VotingLinkManager($playerInfo);

$action = $_REQUEST['action'];
$uid = HTTPContext::getInteger('uid');

if(isset($action)) {
  switch($action) {
    case 'reorder':
      $panel->reorder( $uid, $orderdir );
      break;
    case 'submit':
      if( !isset( $language ) ) $language = 0;
      $panel->updateVoteLink($uid, $linkname, $linkhref, $language, !empty($enabled) );
      break;
    case 'remove':
      $panel->deleteVoteLink($uid);
      break;
    case 'new':
      $uid = $panel->updateVoteLink(-1, 'newLink', '<!-- nothing -->', 0, false );
      $action = 'edit';
    case 'edit':
      $languages = $panel->getLanguages();
      break;
  }
}

$voteLinks = $panel->getVoteLinks();

?>
     
<?php show_title ("MANAGE VOTE LINKS"); ?>

<div id='admin-main'>

<form name='mainform' id='mainform' method="post" action="<?php echo PAGEHREF . "&action=submit" ?>">

<table>
<tr><th style='border: 0px'></th><th>Name</th><th>Href</th><th>Language</th><th>Enabled</th></tr>
<?php

 $index = -1;
 $max = count($voteLinks);
 foreach ($voteLinks as $link) {
   $index++;
   $dataid  = $link->uid;
   $href = $link->url;
   $name = $link->name;
   $enabled = $link->enabled != 0;
   $dataLanguage = $link->language;
   $tdStyleAdd = ( $uid == $dataid ) ? 'border-color:#00FF99' : '';
   
   echo "<tr style='height:22px;'>\n";
   //up button
   echo "<td style='border:0px;padding:0px;'>";
   if ( $index > 0 ) echo "<label onClick='orderUp($dataid);' class='sorting-button'>&nbsp;&uarr;&nbsp</label>";
   echo "</td>";
   
   if (isset($action) && $action == 'edit' && $uid == $dataid) {
     $enabled = $enabled ? 'checked' : '';
     echo
       "<td rowspan='2' style='width:100px;'><input style='width:90%' type='text' name='linkname' value=\"$name\"/></td>".
       "<td rowspan='2' style='width:100px;'><input style='width:90%' type='text' name='linkhref' value=\"$href\"/></td>\n";
       
     //version of row when user editing values
     echo "<td rowspan='2'><select style='width:80px' name='language'>\n".
      "<option value=\"0\">All</option>\n";
      
     foreach ($languages as $lang) {
       $lName = $lang->name;
       $lId = $lang->id;
       $sel = $dataLanguage == $lang->id ? 'selected' : '';
       echo "  <option value='$lId' $sel/>$lName</option>\n";
     }     
     echo "</select></td>\n";
     
     echo "<td rowspan='2'><input type='checkbox' name='enabled' $enabled/></td>\n";
     echo "<td rowspan='2' style='border:0px'>\n  <input type='submit' value='OK'/>\n".
       "<input type='hidden' name='uid' value='$dataid'>".
       "</td>\n";
   }
   else {
     $langName = $panel->getLanguageName($dataLanguage);
     $enabled = $enabled ? '&#x2713;' : '&#9747;';
     //version of normal row
     echo 
        "<td rowspan='2' style='width:100px;$tdStyleAdd'>$name</td>".
        "<td rowspan='2' style='width:700px;$tdStyleAdd'><code>". $href . "</code></td>\n".
        "<td rowspan='2' style='width:80px;$tdStyleAdd'>$langName</td>\n".
        "<td rowspan='2' style='$tdStyleAdd'>$enabled</td>\n".
        "<td rowspan='2' style='border:0px;padding:0px 0px 0px 5px'>\n  <a href='".PAGEHREF."&action=edit&uid=$dataid'>edit</a></td>\n".
        "<td rowspan='2' style='border:0px;padding:0px 0px 0px 5px'><a href='".PAGEHREF."&action=remove&uid=$dataid''>remove</a></td>\n";
   }
   echo "</tr>\n";
   echo "<tr style='height:22px;'><td style='border: 0px;padding:0px'>";
   if( $index < $max - 1 ) echo "<label onClick='orderDown($dataid);' class='sorting-button'>&nbsp;&darr;&nbsp</label>";
   echo "</td></tr>";
 }
?>
<tr><td colspan="3" style="text-align:left; border: 0px"> <a href='<?php echo PAGEHREF ?>&action=new'>new</a></td></tr>
</table>

</form>

</div>

<div id="back-link">
  <a href="index.php?page=player">Back to player page</a>
</div>

<?php show_title ("VOTING LIST SAMPLE"); ?>

<div style='text-align: center;'>

<?php                   
//again because i'm lazy
$voteLinks = $panel->getVoteLinks();
foreach ($voteLinks as $link) {
  $href = html_entity_decode($link->url);
  echo $href . "<br>\n";
}
?>
</div>

<div id="back-link">
  <a href="index.php?page=player">Back to player page</a>
</div>
