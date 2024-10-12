$(document).ready (function() {
  $("input[name=animal_id]").click(function() {
    var action = $(this).val();
    
    $("#detailsPanel").html( $("#continueButton") ).prepend( text[action] );
    $("#continueButton").toggle(true);
    
    $("#detailsPanel").toggle(true);
  });
});
