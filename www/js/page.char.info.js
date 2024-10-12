
$(function() {
  
  $(".action_drop_project").click(function(event) {
    event.preventDefault();

    asyncRequest({
      dataType: "text",
      data: {
        page: "dropproject",
        character: $("#ownCharId").val(),
      },
      success: function (input) {
        var ret = jsonAllowEmpty(input);
        if (isError(ret)) {
          return;
        }
        $("#projectPanel").hide();
      },
    });
  });

  $(".action_drop_dragging").click(function(event) {
    event.preventDefault();

    asyncRequest({
      dataType: "text",
      data: {
        page: "dropdragging",
        character: $("#ownCharId").val(),
      },
      success: function (input) {
        var ret = jsonAllowEmpty(input);
        if (isError(ret)) {
          return;
        }
        $("#draggingPanel").hide();
      },
    });
  });
});
