var AjaxSender = 0;
var OldSenderContents = "";
var SessInfo = "";

function AjaxNode (sender, Params) {
  if (sender.Loaded) {
    $(sender.parentNode.nextSibling).toggle();
    return;
  }
  $.ajax({
    url: 'treeview.inc.php',
    type: "POST",
    dataType: "json",
    data: Params + SessInfo,
    success: handlerFunc, 
    error: errFunc,
  });
  AjaxSender = sender;
  if (AjaxSender) {
    OldSenderContents = AjaxSender.innerHTML;
  }
}

function AjaxRootNode () {
  AjaxNode (0, "request=cat");
}

function getNode (NodeData) {
  var Node = $("<li></li>", { class: NodeData.Class});
  if (!NodeData.Action) {
    Node.html(NodeData.Text);
  } else {
    var ActionStr;
    switch (NodeData.Action.Type) {
      case 0:
        ActionStr = "JavaScript: AjaxNode (this, '"+ NodeData.Action.ParamStr + "')";
        Node.html('<a href="JavaScript:;" onclick="' + ActionStr + '">' + NodeData.Text + '</a>');
        break;
      case 1:
        Node.html('<a href="' + NodeData.Action.URL + '?' + NodeData.Action.ParamStr + SessInfo +'">' + NodeData.Text + '</a>');
        break;
    }
  }
  Node.Data = NodeData;
  return Node;
}

function HandleNodes (nodes) {
  if (AjaxSender)
    Dest = AjaxSender.parentNode.parentNode;
  else {
    $("#treeRoot").empty();
  }
  
  var List = $("<ul></ul>");
  for (i = 0; i < nodes.length; i++) {
    List.append(getNode(nodes[i]));
  }
  
  if (AjaxSender) {
    AjaxSender.parentNode.parentNode.insertBefore (List.get(0), AjaxSender.parentNode.nextSibling);
    AjaxSender.Loaded = true;
    AjaxSender.Opened = true;
  } else {
    $("#treeRoot").append(List);
  }

  if (List.children().length === 1) {
    List.find("a").click();
  }
}

function HandleMessage (Obj) {
  $("#treeRoot").empty();
  var Div = $("<div></div>", {
    class: Obj.Class
  }).html(Obj.Text);
  $("#treeRoot").append(Div);
}

var handlerFunc = function(Obj) {
  if (AjaxSender) {
    AjaxSender.innerHTML = OldSenderContents;
  }
  if (Obj.Type == "TreeNodes")
    HandleNodes (Obj.Nodes);
  if (Obj.Type == "Message")
    HandleMessage (Obj);
}

var errFunc = function(t, textStatus) {
  alert('Error -- ' + textStatus);
}

function ShowMessage (Message) {
  HandleMessage ({
    Class: "ajaxmsg",
    Text: Message
  });
}
