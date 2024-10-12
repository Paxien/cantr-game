var DESTINATION_INVENTORY = "inventory";
var DESTINATION_GROUND = "ground";

var isInventory = isStorageInInventory();

$(function() {
  $(".action_retrieve_inventory").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var spaceForObject = isStorageInInventory() ? object.maxAmount : getInventorySpaceFor(object);

    if (!object.isQuantity || event.ctrlKey) {
      retrieveObject(object, DESTINATION_INVENTORY, Math.min(spaceForObject, object.maxAmount), !isStorageInInventory());
    } else {
      var existing = object.find(".submenu_retrieve_inventory");
      if (existing.length > 0) {
        existing.hideAndRemove();
      } else {
        object.find("div").hideAndRemove();
        var submenu = $("<div></div>", {
          class: "submenu_retrieve_inventory",
          display: "none",
        });

        submenu.text(" " + tagText("js_form_retrieve_inventory_amount"));

        var amountInput = createAmountInput(object, spaceForObject);
        var submitButton = createSubmitButton(object, function() {
          return retrieveObject(object, DESTINATION_INVENTORY, amountInput.val(), !isStorageInInventory());
        });

        triggerSubmitByEnter(amountInput, submitButton);

        submenu.append(amountInput, submitButton).hide();
        object.append(submenu);

        submenu.show("fast");
        amountInput.focus();
      }
    }
  });

  $(".action_retrieve_ground").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (!object.isQuantity || event.ctrlKey) {
      retrieveObject(object, DESTINATION_GROUND, object.maxAmount, false);
    } else {
      var existing = object.find(".submenu_retrieve_ground");
      if (existing.length > 0) {
        existing.hideAndRemove();
      } else {
        object.find("div").hideAndRemove();
        var submenu = $("<div></div>", {
          class: "submenu_retrieve_ground",
          display: "none",
        });

        submenu.text(" " + tagText("js_form_retrieve_ground_amount"));

        var amountInput = createAmountInput(object, object.maxAmount);
        var submitButton = createSubmitButton(object, function() {
          return retrieveObject(object, DESTINATION_GROUND, amountInput.val(), false);
        });

        triggerSubmitByEnter(amountInput, submitButton);

        submenu.append(amountInput, submitButton).hide();
        object.append(submenu);

        submenu.show("fast");
      }
    }
  });

  $(".action_eatraw").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (event.ctrlKey) {
      eatObject(object, object.maxAmount);
      return;
    }

    var existing = object.find(".submenu_eatraw");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.eatraw",
          character: $("#ownCharId").val(),
          object: object.objectId,
        },
        success: function(ret) {
          eatRawCallback(ret, object);
        },
      });
    }
  });

  $("#retrieve_all_button").click(function(event) {
    return confirm(tagText("js_take_all_confirmation"));
  });

  $(".action_pointat, .action_copynote").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var action_button = $(event.target);
    requestObjectAction(object, action_button);
  });
});

function isStorageInInventory() {
  return $(".action_retrieve_ground").length == 0;
}

function retrieveObject(object, destination, amount, carryMultiplier) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "retrieve",
      character: $("#ownCharId").val(),
      destination: destination,
      object_id: $("#storage_id").val(),
      retr_obj: object.objectId,
      amount: amount,
    },
    success: function(input) {
      return objectReduced(input, object, amount, carryMultiplier);
    },
  });
}

var getOrderedObjectsArray = function() {
  var objects = [];
  $(".obj_object").each(function() {
    var objId = $(this).prop("id");
    var parts = /object_(\d+)/.exec(objId);
    if (parts) {
      objects.push(parts[1]); // list of object ids
    }
  });
  return objects;
};

var updateToSpecifiedOrder = function(order) {
  for (var i = 1; i < order.length; i++) {
    var curr = $("#object_" + order[i]).closest("tr");
    var prev = $("#object_" + order[i - 1]).closest("tr");

    curr.insertAfter(prev);
  }
};


var prevObjectsOrder = [];

$(function() {
  var objectsList = $("#objectsList > tbody");
  var basicToolbarButtons = $(".basic_toolbar");
  var endOrderingButtons = $("#confirmReordering, #cancelReordering").hide();

  var deactivateReordering = function() {
    objectsList.enableSelection();
    objectsList.sortable("option", "disabled", true);
    objectsList.css("cursor", "auto");

    basicToolbarButtons.show();
    endOrderingButtons.hide();
  };

  objectsList.sortable({
    axis: "y",
    placeholder: "sortable-object-placeholder",
    disabled: true,
  });

  $("#startReordering").click(function() {
    objectsList.disableSelection();

    prevObjectsOrder = getOrderedObjectsArray();

    basicToolbarButtons.hide();
    endOrderingButtons.show();

    objectsList.sortable("option", "disabled", false);
    objectsList.css("cursor", "move");
  });

  $("#confirmReordering").click(function() {
    deactivateReordering();

    var newObjectsOrder = getOrderedObjectsArray();

    asyncRequest({
      dataType: "json",
      data: {
        page: "object_ordering",
        character: $("#ownCharId").val(),
        storage: $("#storage_id").val(),
        oldOrdering: JSON.stringify(prevObjectsOrder),
        newOrdering: JSON.stringify(newObjectsOrder),
      },
      success: function(ret) {
        if (isError(ret)) {
          return;
        }
      },
    });
  });

  $("#cancelReordering").click(function() {
    deactivateReordering();

    updateToSpecifiedOrder(prevObjectsOrder);
  });

  $("#resetReordering").click(function() {
    if (confirm(tagText("confirm_reset_objects_order"))) {
      asyncRequest({
        dataType: "json",
        data: {
          page: "object_ordering",
          character: $("#ownCharId").val(),
          storage: $("#storage_id").val(),
          oldOrdering: JSON.stringify(getOrderedObjectsArray()),
          resetToDefault: true,
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          updateToSpecifiedOrder(ret.newOrdering);
        },
      });
    }
  });

});
