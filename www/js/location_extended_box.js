/*
 * Main function which shows the location description box
 */

var lastClicked = null;

function showLocationDescBox(event) {
  var locationLink = $(event.target).closest("a");
  var closestBigParent = locationLink.closest("div, tr");

  var MOUSE_KEY_MIDDLE = 2;
  if (event.which == MOUSE_KEY_MIDDLE) {
    return;
  }

  event.preventDefault();

  var locId = getLocIdFromLink(locationLink);
  if (locationLink.is(lastClicked)) { // user clicked again on the same link, so hide the box
    hideLocationBox();
    return;
  } else {
    $(".descBox").remove();
    lastClicked = locationLink;
  }

  var locBox = $("<div></div>", {id: "locationTab", "data-location-id": locId})
    .append(
      $("<ul></ul>", {id: "locTabsList"}).append(
        $("<li></li>", {id: "locTabGeneralButton"}).append(
          $("<a></a>", {href: "#locTabGeneral"}).text("INFO")
        ).css("visibility", "hidden")
      )
    );

  locBox.append(
    $("<div></div>", {id: "locTabGeneral", class: "locTabPage"}).css("minHeight", "50px")
  );

  var nodeToAdd = locBox;
  if (closestBigParent.is("tr")) { // need to create a new row
    var numberOfColumns = 0;
    // get the first row, because it is never a subject of colrows
    closestBigParent.closest("table").find("tr:first").find("td").each(function() {
      numberOfColumns += $(this).attr("colspan") ? $(this).attr("colspan") : 1;
    });

    var tdNode = $("<td></td>")
      .attr("colspan", numberOfColumns)
      .append(locBox);

    nodeToAdd = $("<tr></tr>")
      .append(tdNode)
  } else {
    locBox.addClass("page-left");
    if ($("#eventsList").length) {
      locBox.css("width", "auto");
    }
  }

  nodeToAdd.addClass("locationTabTopNode").addClass("descBox");
  nodeToAdd.on("remove", function() {
    lastClicked = null;
  });
  nodeToAdd.insertAfter(closestBigParent);

  $("#locationTab").tabs({heightStyle: "content"});

  asyncRequest({
    dataType: "json",
    data: {
      page: "info.location",
      character: $("#ownCharacterId").val(),
      location_id: locId
    },
    success: function(ret) {
      $("#locTabsList").append(getLocationToolbarImages($("#ownCharacterId").val(), locId, ret));

      $("#locTabGeneral").append(
        addDescriptionRow(ret.name + (ret.typeName ? " [" + ret.typeName + "]" : "")),
        addDescriptionRowIf(ret.signs.length,
          ret.signs.map(function(signName) {
            return "<p  class=\"sign\">[ " + signName + " ]</p>";
          }).join("\n")
        ),
        addDescriptionRowIf(ret.visibleObjects.length,
          tagText("js_location_has_contents") + ret.visibleObjects.join(", ") + '.')
      );
      createLocationNameChangeForm($("#locTabGeneral"), ret.name, locId);

      $("#locTabGeneral").append(
        addDescriptionRowIf(ret.customDescription, tagText("js_location_custom_desc") + " " + ret.customDescription)
      );
    }
  });
}

function createLocationNameChangeForm(parentNode, currentName, locId) {
  parentNode.append(
    $("<div></div>").append(
      tagText("js_location_name_label"),
      $("<input/>", {id: "locName", type: "text"}).val(currentName),
      $("<input/>", {id: "changeLocNameConfirm", type: "button", class: "button_charmenu"}).val(tagText("js_location_rename")))
      .css("margin-top", "4px").css("margin-bottom", "8px"));

  triggerSubmitByEnter($("#locName"), $("#changeLocNameConfirm"));

  $("#changeLocNameConfirm").click(function() {
    asyncRequest({
      dataType: "text",
      data: {
        page: "name",
        character: $("#ownCharacterId").val(),
        type: 2,
        target_id: locId,
        name: $("#locName").val()
      },
      success: function(ret) {
        ret = jsonAllowEmpty(ret);
        if (isError(ret)) {
          return;
        }
        var newName = $("#locName").val();
        $(".loc_" + locId).text(newName);
        $(".locationTabTopNode").remove();
      }
    });
  });
}

function getLocIdFromLink(locationLink) {
  var locationId = -1;
  $.each(locationLink.classList(), function(idx, val) {
    var parts = val.match(/loc_(\d+)/);
    if (parts != null) {
      locationId = parts[1];
    }
  });
  return locationId;
}

function hideLocationBox() {
  $(".locationTabTopNode").fadeOut("fast", function() {
    $(".locationTabTopNode").remove();
  });
}

function getLocationToolbarImages(ownCharacterId, locId, locInfo) {
  var buttonToolbar = $("<div></div>", {id: "buttonToolbar"});

  buttonToolbar.append(
    imageLink("button_small_edit", "index.php?page=nameloc&id=" + locId, ownCharacterId, tagText("alt_alter_sign")),
    $("<div></div>", {class: "toolbarButtonsGap"})
  );

  var connectionId = locInfo.connectionToLocation;
  if (locInfo.isAdjacent) {
    if (locInfo.canPointAt) {
      buttonToolbar.append(
        imageLink("button_small_pointat", "index.php?page=pointat&to_building=" + locId,
          ownCharacterId, tagText("alt_pointat_building")));
    }
    buttonToolbar.append(
      imageLink("button_small_enter", "index.php?page=move&target=" + locId,
        ownCharacterId, tagText("alt_enter_building_or_room")));

    if (locInfo.canKnock) {
      buttonToolbar.append(
        imageLink("button_small_knock", "index.php?page=knock&target=" + locId,
          ownCharacterId, tagText("alt_knock_door")));
    }
    buttonToolbar.append($("<div></div>", {class: "toolbarButtonsGap"}));
  } else if (connectionId) {
    buttonToolbar.append(
      imageLink("button_small_pointat", "index.php?page=pointat&to_road=" + connectionId, ownCharacterId, tagText("alt_pointat_road")),
      imageLink("button_small_follow", "index.php?page=travel&connection=" + connectionId, ownCharacterId, tagText("alt_follow_exit")),
      $("<div></div>", {class: "toolbarButtonsGap"}));
  }

  buttonToolbar.append(
    $("<img/>", {src: "graphics/cantr/pictures/button_small_end.gif"}).attr("title", tagText("js_box_close")).click(function() {
      hideLocationBox()
    }));
  return buttonToolbar;
}

$(function() {
  $(document).on("click", ".location", showLocationDescBox);
});
