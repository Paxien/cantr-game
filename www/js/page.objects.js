var lastDragGoal = 0;
var lastDragAmount = 0;

$(function() {
  $(".action_take").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (!object.isQuantity || event.ctrlKey) {
      takeObject(object, Math.min(getInventorySpaceFor(object), object.maxAmount));
    } else {
      var existing = object.find(".submenu_take");
      if (existing.length > 0) {
        existing.hideAndRemove();
      } else {
        object.find("div").hideAndRemove();
        var submenu = $("<div></div>", {
          class: "submenu_take",
          display: "none",
        });

        submenu.text(" " + tagText("js_form_take_amount"));

        var amountInput = createAmountInput(object, getInventorySpaceFor(object));
        var submitButton = createSubmitButton(object, function() {
          return takeObject(object, amountInput.val());
        });

        triggerSubmitByEnter(amountInput, submitButton);

        submenu.append(amountInput, submitButton).hide();
        object.append(submenu);

        submenu.show("fast");
        amountInput.focus();
      }
    }
  });

  $(".action_useraw").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_useraw");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.projects",
          character: $("#ownCharId").val(),
          object_id: object.objectId,
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }
          var submenu = $("<div></div>", {class: "submenu_useraw"});
          submenu.append(tagText("js_form_use_project") + " ");
          var select = $("<select></select>");
          select.append(createOptionList(ret.projects));

          triggerSetAmountOnOptionChange(select, object);
          submenu.append(select);

          submenu.append(" " + tagText("js_form_amount") + " ");

          var amountInput = createAmountInput(object);
          var submitButton = createSubmitButton(object, function() {
            return useObject(object, select.find("option:selected").val(), amountInput.val());
          });
          triggerSubmitByEnter(amountInput, submitButton);

          submenu.append(amountInput, submitButton).hide();

          object.append(submenu);
          select.change();
          submenu.show("fast");
          amountInput.focus();
        },
      });
    }
  });

  $(".action_drag").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_drag");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.locations",
          character: $("#ownCharId").val(),
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var submenu = $("<div></div>", {class: "submenu_drag"});
          submenu.append(tagText("js_form_drag_goal"));
          var select = $("<select></select>");
          select.append(createOptionList(ret.outside.concat(ret.sublocations)));

          if ((lastDragGoal > 0) && (select.find('option[value="' + lastDragGoal + '"]').length > 0)) {
            select.val(lastDragGoal);
          }

          submenu.append(select);
          submenu.append(" " + tagText("js_form_amount"));

          var amountInput = createAmountInput(object);

          amountInput.val(object.maxAmount);
          if ((lastDragAmount > 0) && (lastDragAmount < +object.maxAmount)) {
            amountInput.val(lastDragAmount);
          }

          var submitButton = createSubmitButton(object, function() {
            return dragObject(object, select.find("option:selected").val(), amountInput.val());
          });
          triggerSubmitByEnter(amountInput, submitButton);

          submenu.append(amountInput, submitButton).hide();
          object.append(submenu);
          submenu.show("fast");

          amountInput.focus();
        },
      });
    }
  });

  $(".action_store").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_store");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.storages",
          character: $("#ownCharId").val(),
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var submenu = $("<div></div>", {class: "submenu_store"});

          submenu.append(tagText("js_form_store_into"));

          var select = $("<select></select>");
          var isNotItself = function(element) {
            return element.id != object.objectId;
          };
          var objInInv = ret.storages.storagesInInventory.filter(isNotItself);
          var objOnGround = ret.storages.storagesOnGround.filter(isNotItself);
          if (objInInv.length > 0) {
            var optGroup = $("<optgroup></optgroup>", {
              label: tagText("js_form_storages_in_inventory"),
              class: "target_inventory",
            });
            select.append(optGroup.append(createOptionList(objInInv)));
          }
          if (objOnGround.length > 0) {
            var optGroup = $("<optgroup></optgroup>", {
              label: tagText("js_form_storages_on_ground"),
              class: "target_ground",
            });
            select.append(optGroup.append(createOptionList(objOnGround)));
          }
          triggerSetAmountOnOptionChange(select, object);

          if ((lastStorage > 0) && (select.find('option[value="' + lastStorage + '"]').length > 0)) {
            select.val(lastStorage);
          }

          submenu.append(select);

          submenu.append(" " + tagText("js_form_amount"));

          var amountInput = createAmountInput(object);
          var submitButton = createSubmitButton(object, function() {
            return storeObject(object, select.find("option:selected"), amountInput.val());
          });
          triggerSubmitByEnter(amountInput, submitButton);

          submenu.append(amountInput, submitButton).hide();

          object.append(submenu);
          select.change();
          submenu.show("fast");
          amountInput.focus();
        }
      });
    }
  });

  $(".action_pointat, .action_copynote").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var action_button = $(event.target);
    requestObjectAction(object, action_button);
  });

  $(".action_keytag").click(function(event) {
    showKeyTagInput(event);
  });
});


function dragObject(object, locationId, amount) {
  amount = amount ? amount : object.find(".submenu_amount").val();
  asyncRequest({
    dataType: "text",
    data: {
      page: "drag",
      character: $("#ownCharId").val(),
      data: "yes",
      object_id: object.objectId,
      goal: locationId,
      amount: amount,
    },
    success: function(input) {
      var result = jsonAllowEmpty(input);
      if (isError(result)) {
        return;
      }

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.character",
          character: $("#ownCharId").val(),
          ochar: $("#ownCharId").val(),
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }
          if (!ret.character.dragging) {
            lastDragGoal = locationId;

            if ((object.isQuantity) && (+amount < +object.maxAmount)) {
              lastDragAmount = +amount;
            } else {
              lastDragAmount = 0;
            }

            // try to guess what happened with the dragging
            asyncRequest({
              dataType: "json",
              data: {
                page: "info.object",
                character: $("#ownCharId").val(),
                object_id: object.objectId,
              },
              success: function(objRet) {
                if ("e" in objRet) { // object probably doesn't exist or away
                  object.parent().remove();
                } else {
                  if (objRet.amount == object.maxAmount) { // new and old amount are the same, so dragging failed
                    errorReport.add(tagText("js_dragging_failed"));
                  } else { // dragging success
                    object.attr("data-amount", objRet.amount);
                    object.find(".obj_name").html(objRet.name);
                  }
                  object.find("div").hideAndRemove();
                }
              },
            });

          } else {
            $("#draggingPanelName").text(ret.character.dragging);
            $("#draggingPanel").show();
            object.find("div").hideAndRemove();
          }
        }
      });
    },
  });
}

function takeObject(object, amount) {
  amount = amount ? amount : object.find(".submenu_amount").val();

  asyncRequest({
    dataType: "text",
    data: {
      page: "take",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      amount: amount,
    },
    success: function(input) {
      return objectReduced(input, object, amount, true);
    },
  });
}
