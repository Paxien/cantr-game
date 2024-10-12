function SearchStart () {
  ShowMessage (WaitStr);
  AjaxNode (0, "request=cat&stxt=" + encodeURIComponent( $("#stxt").val() ) );
}

function SearchClear () {
  $("#stxt").val("");
  SearchStart ();
}

function SearchKeyPress (E) {
  if (window.event) 
    keyNum = E.keyCode;
  else if (E.which) 
    keyNum = E.which;
  if (keyNum == 13) 
    SearchStart ();
}
