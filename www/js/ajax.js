var http_request = false;
var DestID = '';
var FuncID = '';
var NodeRequests = new Array ();
var Busy = false;
var OpenedIDs = new Array ();
var OpenedContents = new Array ();
var FullContents = new Array ();
var OpenedCount = 0;
var Refresh = false;
var OldContents = "";
var SesInfo = "";

var ns6=document.getElementById&&!document.all;
var ie4=document.all;
var ns4=document.layers;

function makeRequest (url) {
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
	Busy = true;
}

// ==============================
function alertContents() {
	Busy = false;
	if (http_request.readyState == 4) {
    if (http_request.status == 200) {
       
      if (FuncID) {
        eval (FuncID+" (http_request.responseText)");
      } 
      if (DestID) {

        if (OldContents) {
          document.getElementById (DestID).innerHTML = OldContents;
        }

        if (Clear) {
          FullContents [DestID] = http_request.responseText;
          Clear = false;
        } else {
          if (Refresh) {
            Refresh = false;
            FullContents [DestID] = OpenedContents [DestID] + http_request.responseText;
          } else
            FullContents [DestID] = document.getElementById(DestID).innerHTML + http_request.responseText;
            document.getElementById(DestID).innerHTML = document.getElementById(DestID).innerHTML + http_request.responseText;
        }
        document.getElementById(DestID).innerHTML = FullContents [DestID];
        
        // Scanning for previously opened branches
        // tree functionality
        idlen = DestID.length;
        for (i = 0; i < OpenedCount; i++) 
          if (OpenedIDs [i] != DestID) 
            if (OpenedIDs [i].substr (0, idlen) == DestID) 
              document.getElementById(OpenedIDs [i]).innerHTML = FullContents [OpenedIDs [i]];

      } 
      if (!DestID && !FuncID) {
       //document.getElementById('ajax').innerHTML = http_request.responseText;
       OpenedCount = 0;
      }
    } 
    DestID = "";
    FuncID = "";
	}
}

function OpenNode (itemid, Request) {
  if (Busy) return;
  
  Clear = false;
  if (itemid == "ajax") {  	
    NodeRequests = new Array ();
    OpenedIDs = new Array ();
    OpenedContents = new Array ();
    FullContents = new Array ();
    Clear = true;
  }

  NodeRequests [itemid] = Request;

  if (!document.getElementById (itemid)) {
    alert ("Element " + itemid + " is missing.");
    return;
  }
  if (!Clear) {
    if (OpenedCount > 0 && !Clear) {
      for (i = 0; i < OpenedCount; i++) {
        if (OpenedIDs [i] == itemid) {
          document.getElementById (itemid).innerHTML = OpenedContents [itemid];
          OpenedIDs [i] = "";
          NodeRequests [itemid] = 0;

          if (itemid != "ajax") {  	
            //Content = document.getElementById (itemid+"i").src;
            //Content = Content.replace (/iconopen/gi, "iconclose");
            //document.getElementById (itemid+"i").src = Content;
          }
          return;
        }
      }
    }

    OpenedIDs [OpenedCount] = itemid;
    OpenedContents [itemid] = document.getElementById (itemid).innerHTML;
    OpenedCount ++;
  }  

  DestID = itemid;

  OldContents = "";
  if (itemid != "ajax") {  	
    //Content = document.getElementById (itemid+"i").src;
    //Content = Content.replace (/iconclose/gi, "iconopen");
    //document.getElementById (itemid+"i").src = Content;
    OldContents = document.getElementById (DestID).innerHTML;
    document.getElementById (DestID).innerHTML = OldContents + " ...please wait..."; 
  }
	
  makeRequest ('treeview.inc.php?' + SesInfo + '&request=' + Request + '&parent=' + itemid);
  
}
