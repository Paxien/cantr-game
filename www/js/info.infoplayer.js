function lock_prompt(id) {
  var field = prompt('How many days should that account be locked? [0 = perm]', '0');
  if (field != null) {
    var lock_time = parseInt(field);
    var hiform = '<form id="hidlock" method="post" action="index.php?page=lock_char">';
    hiform += '<input type="hidden" name="action" value="lock">';
    hiform += '<input type="hidden" name="charid" value="' + id + '">';
    hiform += '<input type="hidden" name="lock_time" value="' + lock_time + '">';
    hiform += '</form>';
    var hidspan = document.createElement("span");
    hidspan.innerHTML = hiform;
    document.body.appendChild(hidspan);
    document.forms["hidlock"].submit();
  }
}