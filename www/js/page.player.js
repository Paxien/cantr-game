$(function() {
  var socket = io(location.hostname, {
    "query": $.param({
      "notificationType": "highlightedCharacters"
    }),
    "reconnectionAttempts": 3,
    "reconnectionDelay": 5000,
    "reconnectionDelayMax": 15000,
    "path": '/real-time/socket.io'
  });

  var initialTitleValue = document.title;
  socket.on("highlighted-characters", function(result) {
    $.each(result.characters, function(charId, shouldBeHighlighted) {
      $(".characterOnList[data-charid='" + charId + "']").toggleClass("characterOnList-inactive", !shouldBeHighlighted);
    });
    $.each(result.introCharacters, function(charId, shouldBeHighlighted) {
      $(".characterOnList[data-introcharid='" + charId + "']").toggleClass("characterOnList-inactive", !shouldBeHighlighted);
    });

    showHighlightedCharactersInTitle(result.characters, result.introCharacters, initialTitleValue);
  });

  // initial run
  var characters = {};
  $(".characterOnList[charid]").each(function() {
    characters[$(this).data("charid")] = !$(this).hasClass("characterOnList-inactive");
  });
  var introCharacters = {};
  $(".characterOnList[introcharid]").each(function() {
    introCharacters[$(this).data("introcharid")] = !$(this).hasClass("characterOnList-inactive");
  });

  showHighlightedCharactersInTitle(characters, introCharacters, initialTitleValue);
});

function showHighlightedCharactersInTitle(characters, introCharacters, initialTitleValue) {
  var highlightedCharacterNames = [];
  $.each(characters, function(charId, highlighted) {
    if (highlighted) {
      var nameText = $(".characterOnList[data-charid=" + charId + "]").find("span").text();
      highlightedCharacterNames.push(nameText);
    }
  });

  $.each(introCharacters, function(charId, highlighted) {
    if (highlighted) {
      var nameText = $(".characterOnList[data-introcharid=" + charId + "]").find("span").text();
      highlightedCharacterNames.push(nameText);
    }
  });

  if (highlightedCharacterNames.length > 0) {
    document.title = "(" + highlightedCharacterNames.length + ") "
      + highlightedCharacterNames.join(", ") + " " + tagText("js_chars_with_new_events") + " | " + initialTitleValue;
  } else {
    document.title = initialTitleValue;
  }
}
