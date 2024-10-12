$(document).ready(function() {
  $(".selectHunt").click(function(event) {
    event.preventDefault();
    var state = $.parseJSON($(this).prop("value")); // because string "false" isn't bool false
    $("input.wildPacks").prop('checked', state);
  });
});
