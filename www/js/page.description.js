function rad2deg(angle) {
  return angle / Math.PI * 180
}

function mapDirection(x, y) {
  var direction = Math.round(rad2deg(Math.atan2(y, x)));
  if (direction < 0) {
    return 360 + direction;
  }
  return direction;
}

$(function() {
  $("#changeBuildingDescButton").click(function() {
    $('#descChange').toggle();
  });

  $(".adjustSailingButton").click(function() {
    if ($(".adjustSailingBox").is(":visible")) {
      $(".adjustSailingBox").hide("fast");
      return;
    }

    asyncRequest({
      dataType: "json",
      data: {
        page: "info.sailing",
        character: $("#ownCharId").val(),
      },
      success: function(ret) {
        if (isError(ret)) {
          return;
        }
        $(".adjustSailingBox").show("fast");
        var currentSailingText = tagText("page_alter_sailing_1", {
          DEGREE: ret.wantedDirection,
          CURRENTSPEED: ret.speedPercent,
          TURNS: 8,
        });
        $(".currentSailingMessage").html(currentSailingText);
        $("#sailingDirection").val(ret.wantedDirection);
        $("#sailingSpeed").val(ret.speedPercent);
        $("#sailingHours").val(ret.hours);
      }
    });
  });

  $(".confirmSailing").click(function(event) {
    event.preventDefault();
    asyncRequest({
      dataType: "text",
      data: {
        page: "set_ship_course",
        speed: $("#sailingSpeed").val(),
        direction: $("#sailingDirection").val(),
        hours: $("#sailingHours").val(),
        character: $("#ownCharId").val(),
        data: "yes",
      },
      success: function(ret) {
        ret = jsonAllowEmpty(ret);
        if (isError(ret)) {
          return;
        }
        $(".adjustSailingBox").hide("fast");
        asyncRequest({
          dataType: "json",
          data: {
            page: "info.sailing",
            character: $("#ownCharId").val(),
          },
          success: function(ret) {
            if (isError(ret)) {
              return;
            }
            $(".sailingInfo").text(ret.text);
            $("#mapImage").attr("src", $("#mapImage").attr("src") + "&ts_" + new Date().getUTCMilliseconds() + "=1");
          }
        });
      },
    });
  });

  $("#mapImage").click(function(event) {
    if (!$(".adjustSailingBox").is(":visible")) {
      return;
    }

    var x = event.pageX - $(this).offset().left;
    var y = event.pageY - $(this).offset().top;

    var halfWidth = $(this).width() / 2;
    var halfHeight = $(this).height() / 2;

    var direction = mapDirection(x - halfWidth, y - halfHeight);

    $("#sailingDirection").val(direction);
  });
});
