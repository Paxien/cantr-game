/**
 * Code used for displaying character description box and allow whispering from events page
 */

function newBookmarkButton(target) {
  var proximityBasedClass = target.near ? "button_charmenu" : "button_charmenu_unavailable";
  var targetButton = $("<div></div>", {type: "button", class: proximityBasedClass, "data-char-id": target.id}).text(target.name);
  targetButton.click(function(event) {
    $("#whisperingBookmarks > div")
      .addClass(function () {
        if ($(this).hasClass('button_charmenuactive')) {
          return 'button_charmenu';
        }
        if ($(this).hasClass('button_charmenuactive_unavailable')) {
          return "button_charmenu_unavailable";
        }
      })
      .removeClass("button_charmenuactive")
      .removeClass("button_charmenuactive_unavailable");
    $(event.target)
      .removeClass(proximityBasedClass)
      .addClass(target.near ? "button_charmenuactive" : "button_charmenuactive_unavailable");
    var selectedCharId = $(event.target).attr("data-char-id");
    if (selectedCharId != $('#talk_to').val()) {
      loadBookmarksAjax(selectedCharId);
    }
    $("#talk_to").val(selectedCharId);
    $('#submitTalk').val(($('#talk_to').val() == 0) ? tagText("button_talk_to_all") : tagText("button_whisper"));
    $('#submitTalk').removeClass("button_whisper");
    if (target.id > 0) {
      $('#submitTalk').addClass("button_whisper");
    }
    $("#messageField").focus();
  });
  if (target.id > 0) { // "everyone" shouldn't have "x"
    targetButton.append($("<input/>", {type: "button", class: proximityBasedClass}).val("x").click(function(event) {
      event.stopPropagation();
      var toDelId = $(event.target).closest("div").attr("data-char-id");
      asyncRequest({
        dataType: "text",
        data: {
          page: "manage_whispering_bookmarks",
          character: $("#ownCharId").val(),
          newTarget: toDelId,
          actionType: "remove",
        },
        success: function(ret) {
          ret = jsonAllowEmpty(ret);
          if (isError(ret)) {
            return;
          }

          var currCharId = $('#talk_to').val();
          loadBookmarksAjax((currCharId != toDelId) ? currCharId : 0);
        }
      });
    }));
  }
  return targetButton;
}

function getWhisperingBookmarks(ret, currSelected) {
  if (isError(ret)) {
    return;
  }
  $("#whisperingBookmarks > div").remove();
  var bookmarks = $("#whisperingBookmarks");
  bookmarks.append(newBookmarkButton({id: 0, name: tagText("js_whispering_say_to_all"), near: true}));
  for (var i = 0; i < ret.targets.length; i++) {
    bookmarks.append(newBookmarkButton(ret.targets[i]));
  }
  if (bookmarks.find("div").length > 1) {
    bookmarks.show();
  }
  bookmarks.find("div[data-char-id='" + currSelected + "']").click();
}

function loadBookmarksAjax(currSelected) {
  asyncRequest({
    dataType: "json",
    data: {
      page: "info.whispering_bookmarks",
      character: $("#ownCharId").val(),
    },
    success: function(input) {
      getWhisperingBookmarks(input, currSelected);
    }
  });
}

function getCharIdFromLink(characterLink) {
  var characterId = -1;
  $.each(characterLink.classList(), function(idx, val) {
    var parts = val.match(/char_(\d+)/);
    if (parts != null) {
      characterId = parts[1];
    }
  });
  return characterId;
}

function getObjectsList(objectsList) {
  if (objectsList == null) {
    return [];
  }
  var nodesList = [];
  for (var i = 0; i < objectsList.length; i++) {
    var element = $("<li></li>").text(objectsList[i].name);
    if (objectsList[i].description) {
      element.append('<span class="txt-label"> - ' + objectsList[i].description + '</span>');
    }
    nodesList.push(element[0]);
  }
  return nodesList;
}

function listWithHeader(header, listElements) {
  if (listElements.length > 0) {
    return $([
      $("<p></p>").text(header)[0],
      $("<ul></ul>", {class: "txt"}).append(listElements)[0]
    ]);
  }
  return $();
}


function hideChardescBox() {
  $("#charTabs").parent("div").children("div").fadeOut("fast", function() {
    $("#charTabs").remove();
  });
}

function addAndSelectWhisperReceiver(charId) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "manage_whispering_bookmarks",
      character: $("#ownCharId").val(),
      newTarget: charId,
      actionType: "add",
    },
    success: function(ret) {
      ret = jsonAllowEmpty(ret);
      if (isError(ret)) {
        return;
      }
      loadBookmarksAjax(charId);
    }
  });
}

function whisperLink(imgSrc, charId) {
  var whisperButton = $("<a></a>").append(
    $("<img/>", {src: "graphics/cantr/pictures/" + imgSrc + ".gif"}).attr("title", tagText("alt_talk_to_person"))
  );

  whisperButton.click(function(event) {
    event.preventDefault();
    addAndSelectWhisperReceiver(charId);
  });
  return whisperButton[0];
}

function getImageLinks(charId) {
  var ownCharId = $("#ownCharId").val();
  return $("<div></div>", {id: "buttonToolbar"}).append([
    imageLink("button_small_char_happy", "index.php?page=characterdescription&ocharid=" + charId, ownCharId, tagText("alt_description_person")),
    whisperLink("button_small_talk", charId),
    $("<div></div>", {class: "toolbarButtonsGap"})[0],
    imageLink("button_small_pointat", "index.php?page=pointat&to=" + charId, ownCharId, tagText("alt_point_at_person")),
    imageLink("button_small_help", "index.php?page=help_char&ocharacter=" + charId, ownCharId, tagText("alt_join_person_project")),
    imageLink("button_small_drag", "index.php?page=drag&ocharacter=" + charId, ownCharId, tagText("alt_drag_person")),
    imageLink("button_small_hit", "index.php?page=hit&to=" + charId, ownCharId, tagText("alt_hit_person")),
    $("<div></div>", {class: "toolbarButtonsGap"})[0],
    $("<img/>", {src: "graphics/cantr/pictures/button_small_end.gif", title: tagText("js_box_close")}).click(function() {
      hideChardescBox();
    })[0]
  ]);
}

function addNameAndDesc(parentNode, charId, subjectiveName, personalDesc, ageDesc) {
  parentNode.append(
    tagText("js_events_tab_name"),
    $("<input/>", {id: "charName", type: "text"}).val(subjectiveName),
    $("<input/>", {id: "charNameChdescButton", type: "button", class: "button_charmenu"}).val("CHDESC"),
    $("<input/>", {id: "changeNameDescConfirm", type: "button", class: "button_charmenu"}).val(tagText("form_confirm")),
    $("<div></div>").html(tagText("js_events_tab_additional_desc")),
    $("<textarea></textarea>", {id: "charAdditionalDesc", rows: "3"}).html(personalDesc).autosize()
  );
  $("#charNameChdescButton").click(function() {
    $("#charName").val($("#charName").val() + "<CANTR CHARDESC>");
  });

  triggerSubmitByEnter($("#charName"), $("#changeNameDescConfirm"));

  $("#changeNameDescConfirm").click(function() {
    asyncRequest({
      dataType: "text",
      data: {
        page: "name",
        character: $("#ownCharId").val(),
        type: 1,
        target_id: charId,
        name: $("#charName").val(),
        personalDesc: $("#charAdditionalDesc").val(),
      },
      success: function(ret) {
        ret = jsonAllowEmpty(ret);
        if (isError(ret)) {
          return;
        }
        var newName = $("#charName").val().replace(/<CANTR CHARDESC>/g, ageDesc);
        $("#whisperingBookmarks > div[data-char-id='" + charId + "']").contents()
          .first().replaceWith(newName);
        $(".char_" + charId).text(newName);
        $("#charTabs").remove();
      }
    });
  });
}

/*
 * Main function which shows whole character description box
 */

function showCharDescBox(event) {
  var characterLink = $(event.target).closest("a");

  var eventNode = characterLink.closest("div");
  if ((eventNode.attr("class") + "").indexOf("eventsgroup_") == -1) {
    return;
  }

  var MOUSE_KEY_MIDDLE = 2;
  if (event.which == MOUSE_KEY_MIDDLE) {
    return;
  }
  event.preventDefault();

  var characterId = getCharIdFromLink(characterLink);

  if (event.ctrlKey) {
    addAndSelectWhisperReceiver(characterId);
    return;
  }

  if ((eventNode.children("div").length > 0) && ($("#charTabs").attr("data-char-id") == characterId)) {
    hideChardescBox();
  } else {
    $(".descBox").remove(); // remove any other descBox

    /*
     * Creating tabs
     */

    // tabs selection
    var newElement = $('<div></div>', {id: "charTabs", "data-char-id": characterId})
      .addClass("descBox")
      .append(
        $("<ul></ul>", {id: "charTabsList"}).append(
          $("<li></li>", {id: "charTabGeneralButton"}).append(
            $("<a></a>", {href: "#charTabGeneral"}).text(tagText("js_events_tab_general"))
          ),
          $("<li></li>", {id: "charTabAppearanceButton"}).append(
            $("<a></a>", {href: "#charTabAppearance"}).text(tagText("js_events_tab_appearance"))
          ),
          $("<li></li>", {id: "charTabNamingButton"}).append(
            $("<a></a>", {href: "#charTabNaming"}).text(tagText("js_events_tab_naming"))
          )
        )
      );

    // tabs contents
    newElement.append(
      $("<div></div>", {id: "charTabGeneral", class: "charTabPage"}),
      $("<div></div>", {id: "charTabAppearance", class: "charTabPage"}),
      $("<div></div>", {id: "charTabNaming", class: "charTabPage"})
    );

    $(".charTabPage").text(tagText("js_events_tab_loading"));

    eventNode.append(newElement.hide());

    // additional buttons: whisper, hit etc.
    $('#charTabsList').append(getImageLinks(characterId));
    $("#charTabs").tabs({heightStyle: "content"});
    newElement.fadeIn("fast");

    if (event.shiftKey) {
      $("#charTabNamingButton > a").click();
    }

    /*
     * Loading char data with ajax
     */

    asyncRequest({
      dataType: "json",
      data: {
        page: "info.character",
        character: $("#ownCharId").val(),
        ochar: characterId,
        i_inventory: 1,
      },
      success: function(ret) {
        var char = ret.character;
        addNameAndDesc($("#charTabNaming"), characterId, char.rawName, char.personalDescription, char.ageDescription);

        if ("e" in ret) { // probably too far away, show only "Naming"
          $("#charTabGeneralButton, #charTabAppearanceButton").hide();
          $("#charTabNamingButton > a").click();
          return;
        }

        var stateDescriptions = [];
        for (var idx in char.stateDescriptions) {
          stateDescriptions.push(addDescriptionRow(char.stateDescriptions[idx])[0]);
        }

        $("#charTabGeneral").append(
          addDescriptionRow(char.ageDescription),
          addDescriptionRowIf(char.locationName, tagText("js_events_tab_at_location") + " " + char.locationName + " [" + char.locationType + "]"),
          addDescriptionRow(char.travelling),
          stateDescriptions,
          addDescriptionRowIf(char.projectName, char.projectSkillLevel + " " + tagText("char_desc_working_on_project") +
            ' <a href="index.php?page=infoproject&project=' + char.projectId + '&character=' +
            $("#ownCharId").val() + '">' + char.projectName + "</a>"),
          $("<div></div>", {class: "charStateBar"}).append($("<div></div>", {class: "stateName"}).text(tagText("char_desc_bar_damage") + ": "),
            $("<div></div>", {id: "charStateDamage"}).progressbar({value: 100 - char.states.health}),
            $("<div></div>").text(100 - char.states.health + "%")),
          $("<div></div>", {class: "charStateBar"}).append($("<div></div>", {class: "stateName"}).text(tagText("char_desc_bar_tiredness") + ": "),
            $("<div></div>", {id: "charStateTiredness"}).progressbar({value: char.states.tiredness}),
            $("<div></div>").text(char.states.tiredness + "%"))
        );
        var inventory = getObjectsList(char.inventory);
        var clothes = getObjectsList(char.clothes);
        $('#charTabAppearance').empty().append(
          (char.description ? $("<p></p>", {class: "txt"}).html(char.description) : $()),
          listWithHeader(tagText("js_events_clothes"), clothes),
          listWithHeader(tagText("js_events_inventory"), inventory)
        );
      }
    });
  }
}

$(function() {
  getWhisperingBookmarks({targets: initialWhisperingBookmarks}, 0);
  $(document).on("click", ".character", showCharDescBox);
});
