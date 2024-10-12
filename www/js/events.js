var EVENT_REFRESH_DELAY = 3;
var character, lastEvent, lang;
var skipTimeout = false;
var message = "";
var unreadEvents = 0;
var isFocused = true;

var runningFallback = false;

function startEventsListener(_character, le, _lang) {
  lang = _lang;
  character = _character;
  lastEvent = le;

  var socket = io(location.hostname, {
    "query": $.param({
      "notificationType": "newEvents",
      "character": character,
      "lastEvent": lastEvent,
    }),
    "reconnectionAttempts": 3,
    "reconnectionDelay": 5000,
    "reconnectionDelayMax": 15000,
    "path": '/real-time/socket.io'
  });

  socket.on("connect_error", runFallback);
  socket.on("connect_timeout", runFallback);
  socket.on("disconnect", runFallback);

  socket.on("connect", function() {
    runningFallback = false;
  });

  socket.on("new-events", function(event, callback) {
    onNewEvent(event);
    callback();
  });
}

// BEGIN fallback ajax-based polling

function runFallback() {
  if (!runningFallback) {
    console.log("Use fallback");
    runningFallback = true;
    timerTick(EVENT_REFRESH_DELAY);
  }
}

function timerTick(time) {
  if (time >= 0 && !skipTimeout) {
    setTimeout("timerTick(" + (time - 1) + ")", 1000);
    return;
  }

  checkEvents();
}

// END fallback ajax-based polling

function checkEvents() {
  skipTimeout = false;

  asyncRequest({
    dataType: "json",
    data: {
      page: "info.new_events",
      character: character,
      le: lastEvent,
    },
    success: onNewEvent,
  });
}

function updatePageTitle() {
  var title = $(document).prop('title');
  var matches = title.match(/^\(\d+\) (.*)$/);
  if (matches) {
    title = matches[1]; // crop number of events from title
  }

  if (unreadEvents > 0) {
    title = "(" + unreadEvents + ") " + title; // add number of unread events to title
  }
  $(document).prop('title', title);
}

var eventAlreadyExists = function(newEvent) {
  return $("#" + newEvent.prop("id")).length > 0;
};

var onNewEvent = function(ret) {
  if (isError(ret)) {
    return;
  }

  if (ret["newestEventId"] > lastEvent) {
    lastEvent = ret["newestEventId"];
    for (var eventIndex = ret["events"].length - 1; eventIndex >= 0; eventIndex--) {
      var newEvent = $(ret["events"][eventIndex]);
      if (!eventAlreadyExists(newEvent)) {
        $('#eventsList').prepend(newEvent);
        if (!isFocused) {
          unreadEvents++;
        }
      }
    }
  }

  updatePageTitle();

  if (runningFallback) {
    timerTick(EVENT_REFRESH_DELAY);
  }
};

///////////////added by psychowico

function setCookie(c_name, value, exdays) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + exdays);
  var c_value = escape(value) + ( ( exdays == null ) ? "" : "; expires=" + exdate.toUTCString() );
  document.cookie = c_name + "=" + c_value;
}

function selectFilter(filterIndex) {

  var thisCharCookieName = 'event_filter_' + charId;
  for (var i = 0; i < filters.length - 1; i++) {
    $("#filter_" + i).attr("class", 'button_charmenu');
  }
  $("#filter_" + filterIndex).attr("class", 'button_charmenuactive');
  //show all
  // -2 because we have null at end our table ;)
  if (filterIndex == filters.length - 2) {
    $("#eventsList > div").show();
    setCookie(thisCharCookieName, filterIndex.toString(), 999);
    return true;
  }

  $("#eventsList > div").hide();
  for (var i = 0; i < filters[filterIndex].length; i++) {

    var divName = 'div.eventsgroup_' + filters[filterIndex][i];
    $(divName).show();
  }
  setCookie(thisCharCookieName, filterIndex.toString(), 999);
  return true;
}

var restoreMessage = function() {
  if ($('#messageField').val().length == 0) {
    $('#messageField').val(message);
  }
};

var sendSuccess = function(resp) {
  var input = jsonAllowEmpty(resp);
  skipTimeout = true;

  if ("e" in input) {
    restoreMessage();
    errorReport.add(input["e"]);
  }
};


$(function() {

  $("#messageField").focus();

  $('#submitTalk').click(function() {

    message = $('#messageField').val();
    var talkTo = $('#talk_to').val();

    if (message.length > 0) {
      asyncRequest({
        dataType: "text",
        data: {
          message: message,
          page: "talk",
          character: character,
          to: talkTo,
        },
        success: sendSuccess,
        error: restoreMessage,
      });

      $('#messageField').val('').trigger("autosize.resize");
      loadBookmarksAjax(talkTo);
    }
    return false;
  });

  $('#messageField').keyup(function(event) {
    var KEY_ESC = 27;
    if (event.which == KEY_ESC) {
      restoreMessage();
    }
  });

  triggerSubmitByEnter($("#messageField"), $("#submitTalk"));

  $("#messageField").attr("rows", 1).autosize();

  $(window).blur(function() {
    EVENT_REFRESH_DELAY = 20;
    isFocused = false;
  });

  $(window).focus(function() {
    EVENT_REFRESH_DELAY = 3;
    isFocused = true;
    skipTimeout = true; // refresh immediately

    unreadEvents = 0;
    updatePageTitle();
  });

});
