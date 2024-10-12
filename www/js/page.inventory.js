var lastReceiver = 0;

$(function() {
  $(".action_drop").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (!object.isQuantity || event.ctrlKey) {
      dropObject(object, object.maxAmount);
    } else {
      var existing = object.find(".submenu_drop");
      if (existing.length > 0) {
        existing.hideAndRemove();
      } else {
        object.find("div").hideAndRemove();
        var submenu = $("<div></div>", {
          class: "submenu_drop",
          display: "none",
        });

        submenu.text(" " + tagText("js_form_drop_amount"));

        var amountInput = createAmountInput(object);
        var submitButton = createSubmitButton(object, function() {
          return dropObject(object, amountInput.val());
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

  $(".action_give").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (event.ctrlKey && lastReceiver > 0) {
      giveObject(object, lastReceiver, object.maxAmount);
    } else {
      var existing = object.find(".submenu_give");
      if (existing.length > 0) {
        existing.hideAndRemove();
      } else {
        object.find("div").hideAndRemove();

        asyncRequest({
          dataType: "json",
          data: {
            page: "info.characters.list",
            character: $("#ownCharId").val(),
          },
          success: function(ret) {
            if (isError(ret)) {
              return;
            }

            var submenu = $("<div></div>", {class: "submenu_give"});
            submenu.append(tagText("js_form_give_receiver") + " ");
            var select = $("<select></select>");

            var charsList = $.grep(ret.characters, function(char) {
              return char.id != $("#ownCharId").val();
            });
            select.append(createOptionList(charsList));
            if ((lastReceiver > 0) && (select.find('option[value="' + lastReceiver + '"]').length > 0)) {
              select.val(lastReceiver);
            }
            submenu.append(select);

            submenu.append(" " + tagText("js_form_amount"));

            var amountInput = createAmountInput(object);
            amountInput.val(object.maxAmount);
            var submitButton = createSubmitButton(object, function() {
              return giveObject(object, select.find("option:selected").val(), amountInput.val());
            });
            triggerSubmitByEnter(amountInput, submitButton);

            submenu.append(amountInput, submitButton).hide();

            object.append(submenu);

            submenu.show("fast");
            amountInput.focus();
          },
        });
      }
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

  $(".action_fill_envelop").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_fill_envelop");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.note_storages",
          character: $("#ownCharId").val(),
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var submenu = $("<div></div>", {class: "submenu_fill_envelop"});

          submenu.append(tagText("js_form_store_into"));

          var select = $("<select></select>");
          var isNotItself = function(element) {
            return element.id != object.objectId;
          };
          var noteStorages = ret.noteStorages.filter(isNotItself);
          select.append(createOptionList(noteStorages));

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

  $(".action_repair").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (event.ctrlKey) {
      repairObject(object, -1); // full repair
      return;
    }

    var existing = object.find(".submenu_repair");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.repair",
          character: $("#ownCharId").val(),
          object: object.objectId,
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var submenu = $("<div></div>", {class: "submenu_repair"});

          submenu.append(tagText("js_form_repair_hours"));

          var amountInput = $("<input/>", {
            class: "submenu_amount",
            type: "number",
            width: "100px",
            value: ret.fullRepair,
          });

          var submitButton = createSubmitButton(object, function() {
            return repairObject(object, amountInput.val());
          });
          triggerSubmitByEnter(amountInput, submitButton);

          submenu.append(amountInput, submitButton).hide();

          object.append(submenu);

          submenu.show("fast");
          amountInput.focus();
        }
      });
    }
  });

  $(".action_ingest_all").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    if (event.ctrlKey) {
      ingestAll(object);
      return;
    }

    var existing = object.find(".submenu_ingest_all");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.ingest_all",
          character: $("#ownCharId").val(),
          object: object.objectId,
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var submenu = $("<div></div>", {class: "submenu_ingest_all"});

          var submitButton = createSubmitButton(object, function() {
            return ingestAll(object);
          });

          var resultsText = $("<span></span>").css({"margin-right": "10px"}).text(ret.results);
          var stomachText = $("<span></span>").css({float: "right"}).text(ret.stomach);
          submenu.append(resultsText, submitButton, stomachText).hide();
          object.append(submenu);
          submenu.show("fast");
        }
      });
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

  $(".action_pointat, .action_copynote").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var action_button = $(event.target);
    requestObjectAction(object, action_button);
  });

  $(".action_wear").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    asyncRequest({
      dataType: "text",
      data: {
        page: "wear",
        character: $("#ownCharId").val(),
        object_id: object.objectId,
      },
      success: function(retText) {
        return objectReduced(retText, object, 1, true);
      },
    });
  });

  $(".action_keytag").click(function(event) {
    showKeyTagInput(event);
  });

  $(".action_sethome").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_sethome");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      asyncRequest({
        dataType: "json",
        data: {
          page: "info.bird_nests",
          character: $("#ownCharId").val(),
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var setHomeSubmenu = $("<div></div>", {class: "submenu_sethome topAlignedInlineForm"});
          setHomeSubmenu.append("<span>" + tagText("js_set_messenger_home_text_1") + " </span>");

          var birdNestSelect = $("<select></select>");
          $.each(ret.birdNests, function(idx, birdNest) {
            birdNestSelect.append($("<option value='" + birdNest.id + "'>" + birdNest.name + "</option>"));
          });
          setHomeSubmenu.append(birdNestSelect);
          setHomeSubmenu.append("<span> " + tagText("js_set_messenger_home_text_2") + " </span>");

          var whichHomeRadioGroup = $("<div></div>").css("display", "inline-block");
          whichHomeRadioGroup.append($("<ul class='inlinePlain' style='padding-right:25px;'></ul>").append(
            $('<li><label><input type="radio" name="whichHome" value="1"/>#1</label></li>'),
            $('<li><label><input type="radio" name="whichHome" value="2"/>#2</label></li>')
          ));
          setHomeSubmenu.append(whichHomeRadioGroup);

          var submitButton = $("<button></button>", {class: "button_charmenu"}).text(tagText("js_form_set_messenger_home"));
          setHomeSubmenu.append(submitButton);
          object.append(setHomeSubmenu);

          submitButton.click(function() {
            var selectedWhichHome = $(".submenu_sethome input:radio[name='whichHome']:checked").val();
            var selectedBirdNestId = birdNestSelect.val();
            if (selectedWhichHome && selectedBirdNestId) {
              selectHomeForBird(object.objectId, selectedWhichHome, selectedBirdNestId, setHomeSubmenu);
            }
          });
        }
      });
    }
  });

  $(".action_turn_back_from_messenger").click(function(event) {
    return confirm(tagText("js_turn_back_from_messenger_text"));
  });


  $(".action_dispatch_messenger_bird").click(function(event) {
    var object = getObjectData($(this));
    event.preventDefault();

    var existing = object.find(".submenu_dispatch_messenger_bird");
    if (existing.length > 0) {
      existing.hideAndRemove();
    } else {
      object.find("div").hideAndRemove();

      var dispatchMessengerSubmenu = $("<div></div>", {class: "submenu_dispatch_messenger_bird topAlignedInlineForm"});
      dispatchMessengerSubmenu.append("<span>" + tagText("js_select_home_to_dispatch_text") + "</span>");

      var whichHomeRadioGroup = $("<div></div>").css("display", "inline-block");
      whichHomeRadioGroup.append($("<ul class='inlinePlain' style='padding-right:25px;'></ul>").append(
        $('<li><label><input type="radio" name="whichHome" value="1"/>#1</label></li>'),
        $('<li><label><input type="radio" name="whichHome" value="2"/>#2</label></li>')
      ));
      dispatchMessengerSubmenu.append(whichHomeRadioGroup);

      var submitButton = $("<button></button>", {class: "button_charmenu"}).text(tagText("js_form_dispatch_messenger"));
      dispatchMessengerSubmenu.append(submitButton);
      object.append(dispatchMessengerSubmenu);

      submitButton.click(function(event) {
        var selectedWhichHome = $(".submenu_dispatch_messenger_bird input:radio[name='whichHome']:checked").val();
        if (selectedWhichHome) {
          dispatchMessengerBird(object, selectedWhichHome, dispatchMessengerSubmenu);
        }
      });
    }
  });
});


function selectHomeForBird(birdId, whichHome, birdNestId, submenu) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "set_messenger_home",
      character: $("#ownCharId").val(),
      bird_id: birdId,
      which_home: whichHome,
      bird_nest_id: birdNestId,
    },
    success: function(retText) {
      var retJson = jsonAllowEmpty(retText);
      if (isError(retJson)) {
        return;
      }
      submenu.hide();
    }
  });
}


function dispatchMessengerBird(object, whichHome, submenu) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "dispatch_messenger_bird",
      character: $("#ownCharId").val(),
      bird_id: object.objectId,
      to_which_home: whichHome
    },
    success: function(retText) {
      var retJson = jsonAllowEmpty(retText);
      if (isError(retJson)) {
        return;
      }

      object.parent().remove();
      submenu.hide();
    }
  });
}


function giveObject(object, receiverId, amount) {
  amount = amount ? amount : object.find(".submenu_amount").val();
  lastReceiver = receiverId;
  asyncRequest({
    dataType: "text",
    data: {
      page: "give",
      character: $("#ownCharId").val(),
      data: "yes",
      object_id: object.objectId,
      receiver: receiverId,
      amount: amount,
    },
    success: function(input) {
      return objectReduced(input, object, amount, false);
    }
  });
}

function dropObject(object, amount) {
  amount = amount ? amount : object.find(".submenu_amount").val();
  asyncRequest({
    dataType: "text",
    data: {
      page: "drop",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      amount: amount,
    },
    success: function(input) {
      return objectReduced(input, object, amount, false);
    },
  });
}

function repairObject(object, hours) {
  asyncRequest({
    dataType: "text",
    data: {
      page: "repair",
      character: $("#ownCharId").val(),
      object_id: object.objectId,
      repairhours: hours,
    },
    success: function(input) {
      var ret = jsonAllowEmpty(input);
      if (isError(ret)) {
        return;
      }
      object.find(".action_repair").closest("form").hideAndRemove();
      object.find("div").hideAndRemove();
    },
  });
}
