
$(function() {
  
  $(".storagesList input").click(function(event) {
    max_amount = list[$(event.target).val()];
    $("#max_amt").html(max_amount);
  });
  
  $(".storagesList input").first().click();
  
  $("#maxButton").click(function(event) {
    $("#amount").val(Math.min(max_amount, max_res));
  });
  
});
