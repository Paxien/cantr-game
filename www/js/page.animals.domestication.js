$(document).ready(function () {
// globals
  var curr_id;
  var actions;

  $("#selectPanel > li").addClass("button_charmenu_unavailable");

  $("input[name=pack_id]").click(function () {
    curr_id = $(this).val();
    actions = animals[curr_id];
    $("#selectPanel > li").addClass("button_charmenu_unavailable").removeClass("button_charmenu");
    for (var aid in actions) {
      $("#" + aid).removeClass("button_charmenu_unavailable").addClass("button_charmenu");
    }
  });

  $("#selectPanel > li").click(function () {
    if ($(this).hasClass("button_charmenu")) { // available button was clicked
      var action = $(this).attr("id");
      $("input[name=action_type]").val(action);
      $("#continueButton").toggle(true);
      $("#detailsPanel").html($("#continueButton")).prepend(actions[action]);

      $("#detailsPanel").toggle(true);
    }
  });

});