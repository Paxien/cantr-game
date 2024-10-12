<?php


$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {

?>
<script type="text/javascript" language="javascript">

var http_request = false;
var DestID = '';
var OpenedIDs = new Array ()
var OpenedContents = new Array ()
var OpenedCount = 0;
var Clear = true;

function makeRequest(url) {
  document.getElementById('ajaxstat').innerHTML = 'REQUEST - <font color=#ff8080>WAIT</font>';
  http_request = false;

  if (window.XMLHttpRequest) { // Mozilla, Safari,...
    http_request = new XMLHttpRequest();
    if (http_request.overrideMimeType) {
      http_request.overrideMimeType('text/xml');
      // See note below about this line
    }
  } else if (window.ActiveXObject) { // IE
    try {
      http_request = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        http_request = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {}
    }
  }

  if (!http_request) {
    alert('Giving up :( Cannot create an XMLHTTP instance');
    return false;
  }
  http_request.onreadystatechange = alertContents;
  http_request.open('GET', url, true);
  http_request.send(null);
}

// ==============================
function alertContents() {
  if (http_request.readyState == 4) {
    if (http_request.status == 200) {
      if (DestID) {
        if (DestID == 'controlhead' || Clear) {
          document.getElementById(DestID).innerHTML = http_request.responseText;
          Clear = false;
        } else {
          document.getElementById(DestID).innerHTML = document.getElementById(DestID).innerHTML  + http_request.responseText;
        }
      } else {
        document.getElementById('ajax').innerHTML = http_request.responseText;
        OpenedCount = 0;
      }
      document.getElementById('ajaxstat').innerHTML = 'READY';
    } else {
      document.getElementById('ajaxstat').innerHTML = 'ERROR';
    }
  }
}

function RunAjax (name, type) {
  Clear = false;
  DestID = "";
  Request = 'ce.utils.inc.php?req='+type+'&name='+name;
  if (document.getElementById ('useCustomNames').checked)
    Request = Request + '&usecustom=1';
  if (document.getElementById ('searchById').checked)
    Request = Request + '&byid=1';
  makeRequest (Request);
}

function AjaxB (destination, req) {
  Clear = true;
  DestID = destination;
  Request = 'ce.utils.inc.php?req='+req;
  makeRequest (req);
}

function AjaxAdd (itemid, request, data, indent) {
  Clear = false;
  DoAjax (itemid, request, data, indent);
}

function AjaxReplace (itemid, request, data, indent) {
  Clear = true;
  DoAjax (itemid, request, data, indent);
}

function DoAjax (itemid, request, data, indent) {
  if (!Clear) {
    if (OpenedCount > 0 && !Clear) {
      for (i = 0; i < OpenedCount; i++) {
        if (OpenedIDs [i] == itemid) {
          document.getElementById (itemid).innerHTML = OpenedContents [i];
          OpenedIDs [i] = "";
          return;
        }
      }
    }

    OpenedIDs [OpenedCount] = itemid;
    OpenedContents [OpenedCount] = document.getElementById (itemid).innerHTML;
    OpenedCount ++;
  }  
  
  DestID = itemid;

  Content = document.getElementById (itemid).innerHTML;
  Content = Content.replace (/cetpl/gi, "cetmi");
  document.getElementById (itemid).innerHTML = Content;

  makeRequest ('ce.utils.inc.php?req='+request+'&parent='+itemid+'&indent='+indent+'&'+data);
}

var TimeoutID;

function EditChange (name, type) {
  clearTimeout (TimeoutID);
  TimeoutID = setTimeout ("RunAjax ('"+name+"', '"+type+"')", 650);
}

function DelayedRunFunction( functionName ) {
  clearTimeout (TimeoutID);
  TimeoutID = setTimeout (functionName + "()", 650);
}

function ChangeMode (mode) {
  OpenedCount = 0;
  Clear = true;
  DestID = 'controlhead';
  document.getElementById ('ajax').innerHTML = "";
  makeRequest ('ce.utils.inc.php?req=mode&type='+mode);
}

function ItemVal (Item) {
  return encodeURI (document.getElementById (Item).value);
}

function StartPlyrSearch () {
  var Request;
  Clear = false;
  DestID = "";
  Request = 'ce.utils.inc.php?req=plyrsearch'+
    '&pid='+ItemVal ('plyrID') +
    '&first='+ItemVal ('plyrFirstName') +
    '&last='+ItemVal ('plyrLastName') +
    '&lang='+ItemVal ('plyrLang') +
    '&email='+ItemVal ('plyrEmail') +
    '&ip='+ItemVal ('plyrIP') +
    '&status='+ItemVal ('plyrStatus');
  makeRequest (Request);
}    

function ClearPlyrSearch () {
  document.getElementById ('plyrID').value = '';
  document.getElementById ('plyrFirstName').value = '';
  document.getElementById ('plyrLastName').value = '';
  document.getElementById ('plyrLang').value = 0;
  document.getElementById ('plyrEmail').value = '';
  document.getElementById ('plyrIP').value = '';
  document.getElementById ('plyrStatus').value = 0;
} 


function StartCharsSearch () {
  var Request;
  Clear = false;
  DestID = "";
  Request = 'ce.utils.inc.php?req=charsearch'+
    '&cid='+ItemVal ('charID') +
    '&cname='+ItemVal ('characterName') +
    '&pid='+ItemVal ('charPlayerId') +
    '&locid='+ItemVal ('charLocId') +
    '&agefrom='+ItemVal ('ageFrom') +
    '&ageto='+ItemVal ('ageTo') +    
    '&bornfrom='+ItemVal ('bornFrom') +
    '&bornto='+ItemVal ('bornTo') +
    '&lang='+ItemVal ('charLang') +
    '&knowas='+ItemVal ('charKnowAs') +
    
    '&status='+ItemVal ('charStatus');
  makeRequest (Request);
}  
function ClearCharsSearch () {
  document.getElementById ('charID').value = '';
  document.getElementById ('characterName').value = '';
  document.getElementById ('charKnowAs').value = '';
  document.getElementById ('charPlayerId').value = '';
  document.getElementById ('charLocId').value = '';
  document.getElementById ('ageFrom').value = '20';
  document.getElementById ('ageTo').value = '1000';
  document.getElementById ('bornFrom').value = 0;
  document.getElementById ('charLang').value = 0;          
  document.getElementById ('charStatus').value = 1;
  
} 
</script>
<?php

  include 'ce.func.inc.php';

  $ACL = GetPrivilleges ($player);
  
  function MenuItem ($Access, $Name, $Param) {
    if ($GLOBALS ['ACL'][$Access])
      return "<a href=\"JavaScript:;\" onclick='ChangeMode (\"$Param\")'>$Name</a>";
    else
      return "<font color=#a0a0a0>$Name</font>";
  }
  
  echo 
    "<center>
    <table width=700 cellSpacing=3 cellPadding=0><tr>
    <td class=cex align=left>Cantr II Explorer</td>
    <td class=cex align=right>".
    MenuItem ("maPlyrs", "Players", "plr")." | ".
    MenuItem ("maChars", "Characters", "chars")." | ".
    MenuItem ("maLocs", "Locations", "loc")." | ".
    MenuItem ("maBuilds", "Buildings", "bld")." | ".
    MenuItem ("maVehs", "Vehicles", "veh").

    "
    </td></tr></table>
    <table width=700 cellSpacing=3 cellPadding=0 style='border-top: 1px solid #888888'><tr><td id='controlhead'>
     <table width=700 cellSpacing=0 cellPadding=0><tr><td class=cex>
    	 Please select research type.
     </td><td align=right><small id='ajaxstat'>READY</small></tr>
     </table>
    </td></tr></table>

    <table width=700 height=400 cellSpacing=3 cellPadding=0>
     <!-- <tr><td height=17 class=cex vAlign=top>Cantr II &mdash; the Game</td></tr> -->
     <tr><td id='ajax' vAlign=top>
     </td></tr>
    </table>
    <A HREF=\"index.php?page=listplayers\">Go back to players search</A></TD></TR><br>
    <A HREF=\"index.php?page=player\">Go back to your player page</A></TD></TR>

    </center>";

}
