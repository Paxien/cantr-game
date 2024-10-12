var errorReport;

var KEY_ENTER = 13;

// error reporting
ErrorReport.prototype = {
  constructor: ErrorReport,
  add: function(text) {
    var closeButton = $("<input/>", {value: "x", type: "button"});
    closeButton.css("marginTop", "-3px");
    closeButton.click(function(e) {
      $(e.target).parent().remove();
    });

    var textSpan = $("<span></span>").text(text);
    var newError = $("<li></li>", {class: "errorMessage"});
    newError.append(closeButton, textSpan);

    $("#errorsList").append(newError);
  }
}

function ErrorReport() {
  var errorsList = $("<li></li>", {id: "errorsList"});

  $("body").append(errorsList);
}

$(function() {
  errorReport = new ErrorReport();
});

/* 
 * ajax requests handling
 */

// for some actions empty response means "ok", it should be treated as {}
function jsonAllowEmpty(retText) {
  var retJson = {};
  try {
    retJson = $.parseJSON(retText);
  } catch (e) {
  }
  return retJson;
}

function isError(ret) {
  if ("e" in ret) {
    errorReport.add(ret["e"]);
    return true;
  }
  return false;
}

$.fn.hideAndRemove = function() {
  var that = this;
  this.hide("fast", function() {
    that.remove();
  });
}

$.fn.classList = function() {
  return (this.attr("class") + "").split(/\s+/);
};

function triggerSubmitByEnter(caller, callee) {
  caller.keypress(function(event) {
    if ((event.which == KEY_ENTER) && !event.shiftKey) {
      event.preventDefault();
      callee.click();
    }
  });
}

function asyncRequest(requestData) {
  // default values
  if (!("url" in requestData)) {
    requestData.url = "liteindex.php";
  }
  if (!("cache" in requestData)) {
    requestData.cache = false;
  }
  if (!("type" in requestData)) {
    requestData.type = "POST";
  }

  $.ajax(requestData);
}

/**
 * Global function to handle translation system.
 * @param name tag name
 * @param args object key-value pairs being parameters for interpolation
 * @returns string of tag for specific language or undefined.
 */
function tagText(name, args) {
  var text = translations[name];
  if (text && args) {
    Object.keys(args).forEach(function(tagKey) {
      text = text.replace("#" + tagKey + "#", args[tagKey]);
    });
  }
  return text;
}


/*
 * Utility functions
 */

function addDescriptionRow(data) {
  if (data == null) {
    return $();
  }
  return $("<div></div>").html(data);
}

function addDescriptionRowIf(cond, data) {
  if (cond) {
    return addDescriptionRow(data);
  } else {
    return $();
  }
}


function imageLink(imgSrc, link, ownCharId, imageTitle) {
  imageTitle = typeof imageTitle !== 'undefined' ? imageTitle : "";
  return $("<a></a>", {href: link + "&character=" + ownCharId}).append(
    $("<img/>", {src: "graphics/cantr/pictures/" + imgSrc + ".gif"}).attr("title", imageTitle)
  );
}