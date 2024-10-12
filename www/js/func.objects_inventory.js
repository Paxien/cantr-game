var MAX_INVENTORY_WEIGHT = 15000;
var KEY_ENTER = 13;
var MAX_OPTION_LENGTH = 55;
var MAX_OBJECT_DESCRIPTION_LENGTH = 320;

var lastStorage = 0;

function createSubmitButton(object, onSubmit) {
  return $("<input/>", {
    value: "Ok",
    type: "button",
    class: "button_charmenu",
  }).css("marginLeft", "5px")
    .css("marginRight", "5px")
    .click(function() {
    return onSubmit();
  });
}

function triggerSubmitByEnter(caller, callee) {
  caller.keypress(function(event) {
    if ((event.which == KEY_ENTER) && !event.shiftKey) {
      event.preventDefault();
      callee.click();
    }
  });
}

function triggerSetAmountOnOptionChange(select, object) {
  select.change(function() {
    var selectedOption = select.find("option:selected");
    if (selectedOption.length > 0) {
      var maxNeeded = selectedOption.attr("data-max-amount");
    } else {
      var maxNeeded = 0;
    }
    var maxToUse = Math.min(maxNeeded, object.maxAmount);
    object.find(".submenu_amount").val(maxToUse).focus();
  });
}

function createAmountInput(object, possibleMax) {
  possibleMax = (typeof possibleMax !== 'undefined') ? possibleMax : object.maxAmount;

  var amountInput = $("<input/>", {
    class: "submenu_amount",
    type: "number",
    width: "100px",
    value: Math.min(possibleMax, object.maxAmount),
  });

  // hack to have focus on firefox
  setTimeout(function() {
    amountInput.focus();
  }, 300);

  return amountInput;
}


function getInventoryWeight() {
  var invWeightStr = $("#inventory_weight").text();
  return Number(/(\d+)/.exec(invWeightStr)[1]);
}

function alterInventoryWeight(change) {
  var oldWeight = getInventoryWeight();
  var newWeight = oldWeight + change;

  var oldText = $("#inventory_weight").text();
  $("#inventory_weight").text(oldText.replace(oldWeight, newWeight));
}

function getInventorySpaceFor(object) {
  var unitWeight = +object.attr("data-unit-weight");
  if (unitWeight == 0) {
    return 1;
  }

  return (MAX_INVENTORY_WEIGHT - getInventoryWeight()) / unitWeight;
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
};


function createOptionList(elements) {
  var list = [];
  for (var i=0; i < elements.length; i++) {
    var newElement = ($("<option></option>", {
      "data-max-amount": elements[i].maxPossible,
      value: elements[i].id
    }).text(elements[i].name.substring(0, MAX_OPTION_LENGTH) +
      (elements[i].name.length >= MAX_OPTION_LENGTH ? "..." : "")));
    if (("initiator" in elements[i]) && (elements[i].initiator.length > 0)) {
      newElement.text(newElement.text() + " (" + elements[i].initiator + ")");
    }
    if (("description" in elements[i]) && (elements[i].description.length > 0)) {
      newElement.text(newElement.text() + " *");
      var rawDesc = $("<span></span>").html(elements[i].description).text(); // get "clean" description
      newElement.attr("title", rawDesc);
    }
    list.push(newElement.get(0));
  }
  return $(list);
}

function getObjectName(object) {
    asyncRequest({
    dataType: "json",
    data: {
      page: "info.object",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
    },
    success: function (ret) {
      if (isError(ret)) {
        return;
      }
      if (ret["name"]) {
        object.find(".obj_name").html(ret.name);
      }
    },
  });
}

function getObjectData(context) {
  var object = context.closest(".obj_object");

  object.objectId = /object_(\d+)/.exec(object.attr("id"))[1];
  object.maxAmount = object.attr("data-amount");
  object.isQuantity = object.attr("data-is-quantity") == "1";
  return object;
}

// for some actions empty response means "ok", it should be treated as {}
function jsonAllowEmpty(retText) {
  var retJson = {};
  try {
    retJson = $.parseJSON(retText);
  } catch (e) {}
  return retJson;
}

function objectReduced(retText, object, amount, toInv) {
  var retJson = jsonAllowEmpty(retText);
  if (isError(retJson)) {
    return;
  }

  object.find("div").hideAndRemove();
  if (toInv && !isInventory) {
    alterInventoryWeight(object.attr("data-unit-weight") * amount);
  } else if (!toInv && isInventory) {
    alterInventoryWeight(object.attr("data-unit-weight") * amount * -1);
  }
  if (!object.isQuantity || (amount == object.maxAmount)) {
    object.parent().remove();
  } else {
    object.attr("data-amount", object.maxAmount - amount);
    getObjectName(object);
  }
}

function storeObject(object, option, amount) {
  amount = amount ? amount : object.find(".submenu_amount").val();
  var toInventory = option.closest("optgroup").attr("class") == "target_inventory";
  asyncRequest({
    dataType: "text",
    data: {
      page: "store",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      target: option.val(),
      amount: amount,
    },
    success: function (input) {
      lastStorage = option.val();

      var target = $("#object_" + option.val());
      if (target.length) { // if seeing target storage on the same page then it should be automatically refreshed
        getObjectName(getObjectData(target));
      }

      return objectReduced(input, object, amount, toInventory);
    },
  });
}

function useObject(object, projectId, amount) { // currently just for raws
  amount = amount ? amount : object.find(".submenu_amount").val();
  asyncRequest({
    dataType: "text",
    data: {
      page: "useraw",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      project: projectId,
      amount: amount,
    },
    success: function (input) {
      return objectReduced(input, object, amount, false);
    },
  });
}

function eatRawCallback(ret, object) {
  if (isError(ret)) {
    return;
  }

  var submenu = $("<div></div>", {class: "submenu_eatraw"});


  var suggestedToEat = 0;
  for (var i = 0; i < ret.results.length; i++) {
    suggestedToEat = Math.max(suggestedToEat, ret.results[i].toMaximize);
  }
  // eat as much as needed to maximize something; if eating not needed -> eat everything
  suggestedToEat = (suggestedToEat > 0) ? Math.min(suggestedToEat, object.maxAmount) : object.maxAmount;


  var amountInput = $("<input/>", {
    class: "submenu_amount",
    type: "number",
    width: "100px",
    value: suggestedToEat,
  });

  var submitButton = createSubmitButton(object, function() {
    return eatObject(object, amountInput.val());
  });
  triggerSubmitByEnter(amountInput, submitButton);

  var tableId = "eat_results_" + object.objectId;
  var resultsTable = $("<table></table>", {id: tableId})
    .css({display: "inline", "margin-left": "15px"})
    .addClass("table table-bordered table-condensed").append("<thead></thead>").append("<tbody></tbody>");
  var stomachText = $("<p></p>").css({float: "right"}).text(ret.stomach);

  submenu.append(tagText("page_eatraw_amount") + " ", amountInput, submitButton, resultsTable, stomachText).hide();
  object.append(submenu);


  $("#" + tableId).jsonTable({head: [tagText("state_text"), tagText("change_per_100g"), tagText("amount_to_maximize")], json: ["name", "per100g", "toMaximize"]})
    .jsonTableUpdate({ source: ret.results });
  $("#" + tableId + " td:nth-child(2)").css("text-align", "right").append("%");
  $("#" + tableId + " td:nth-child(3)").css("text-align", "right").append("g");

  submenu.show("fast");

  setTimeout(function() {
    amountInput.focus();
  }, 300);

}

function eatObject(object, amount) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "eatraw",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      amount: amount,
    },
    success: function(input) {
      return objectReduced(input, object, amount, false);
    },
  });
}

function ingestAll(object) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "ingest_all",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
    },
    success: function (retText) {
      var retJson = jsonAllowEmpty(retText);
      if (isError(retJson)) {
        return;
      }

      getObjectName(object);
      object.find(".action_ingest_all").closest("form").hideAndRemove();
      object.find("div").hideAndRemove();
    },
  });
}

function requestObjectAction(object, action_button) {
  var classes = action_button.attr("class").split(/\s+/);
  var action_name = null;

  for (var i = 0; i < classes.length; i++) {
    var parts = /action_(\S+)/.exec(classes[i]);
    if (parts) {
      action_name = parts[1];
    }
  }
  if (action_name) {
    asyncRequest({
      dataType: "text",
      data: {
        page: action_name,
        character: $("#ownCharId").val(),
        object_id: object.objectId,
      },
      success: function (retText) {
        var retJson = jsonAllowEmpty(retText);
        if (isError(retJson)) {
          return;
        }
        var action_button = object.find(".action_" + action_name);
        action_button.addClass("greyscaleFilter");

        setTimeout(function () {
          action_button.removeClass("greyscaleFilter");
        }, 1000);
      }
    });
  }
}

function setHomeForMessenger(object, previous_home) {

}


function showKeyTagInput(event) {
  var object = getObjectData($(event.target));
  event.preventDefault();

  var existing = object.find(".submenu_change_desc");
  if (existing.length > 0) {
    existing.hideAndRemove();
  } else {
    object.find("div").hideAndRemove();

    var currentDescription = object.find(".txt-label").text();
    var restOfTextNode = object.find(".txt-label-the-rest");
    if (restOfTextNode.length) {
      currentDescription = restOfTextNode.data("full-text");
    }
    if (!currentDescription) {
      currentDescription = "";
    }
    var submenu = $("<div></div>", {class: "submenu_change_desc"});
    var newDescriptionArea = $("<textarea>").css("width", "75%").val(currentDescription).autosize();
    var charactersLeftInfo = $("<span></span>");
    var submitButton = createSubmitButton(object, function() {
      return changeObjectDescription(object, newDescriptionArea.val());
    });

    var onChangeTextArea = function() {
      var charactersLeft = MAX_OBJECT_DESCRIPTION_LENGTH - newDescriptionArea.val().length;
      charactersLeftInfo.text(charactersLeft + " " + tagText("desc_chars_left"));
      submitButton.prop("disabled", charactersLeft < 0);
    };
    newDescriptionArea.on("change input paste keyup", onChangeTextArea);
    onChangeTextArea();

    submenu.append(newDescriptionArea, submitButton, charactersLeftInfo);
    object.append(submenu);
    submenu.show("fast");
  }
}

function changeObjectDescription(object, newDescription) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "changeobjdesc",
      data: "yes",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      description: newDescription,
    },
    success: function(retText) {
      var retJson = jsonAllowEmpty(retText);
      if (isError(retJson)) {
        return;
      }
      getObjectName(object);
      object.find("div").hideAndRemove();
    }
  });

}
