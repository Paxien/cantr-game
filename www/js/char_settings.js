function loadFilter(groupId) {
  var clickedGroup =  $("#button_" + groupId);
  if (!clickedGroup.length) {
    return;
  }

  $("#eventGroupPanel").show();
  
  $('#divlist div').removeClass().addClass("greenListItem");

  clickedGroup.addClass("greenListItemactive");

  selectedGroup = groupId;
  var elements = $('[id^="group_id_"]');
  elements.prop("checked", false)
  elements.parent().removeClass('underline');
  
  var filter = filters[groupId];
  for (var i = 0; i < filter.length; i++) {
    var el = $("#group_id_" + filter[i]);
    if (!el.length) {
      continue;
    }

    var checked = filter[i];
    el.prop("checked", checked);
    if (checked) {
      el.parent().addClass('underline');
    }
  }
}

function toggleSelection(checkbox, isChecked) {
  var eventGroupId = +checkbox.prop("id").match(/group_id_(\d+)/)[1]; // groupId coded in node id
  checkbox.prop("checked", isChecked);
  var filter = filters[selectedGroup];
  if (isChecked) {
    if (filter.indexOf(eventGroupId) == -1) {
      filter[filter.length] = eventGroupId;
    }
  } else {
    var idx = filter.indexOf(eventGroupId); // Find the index
    if (idx != -1) {
      filter.splice(idx, 1); // Remove it if really found!
    }
  }
  checkbox.parent().toggleClass('underline', isChecked);
}

$(function() {
  $('.selectFilter').change(function(event) {
    var checkbox = $(event.target);
    toggleSelection(checkbox, checkbox.is(":checked"));
  });
});


function removeFilter(eventGroupId, confirmText) {
  if (confirm(confirmText)) {
    $("#button_" + eventGroupId).remove();
    delete filters[eventGroupId];
    
    var key = null;
    for (var key in filters) {
      break;
    }
    if (key) {
      loadFilter(key);
    } else {
      $("#eventGroupPanel").hide();
    }
  }
  updateToolbar();
}

function newFilter() {
  var groupName = "EventFilter";
  filters[groupCounter] = [];
  filterNames[groupCounter] = groupName;
  
  var el = $("#button_to_copy").clone();
  var label = el.children("label");
  
  el.attr("id", "button_" + groupCounter);
  var loc = groupCounter;
  el.click(function() {
    loadFilter(loc);
  });

  label.text(groupName);
  $("#divlist").append(el);
  el.show();
  
  loadFilter(groupCounter);
  groupCounter++;
  updateToolbar();
}

function editFilterName(groupId, promptText) {
  var ret = prompt(promptText, filterNames[groupId]);
  if (ret) {
    filterNames[groupId] = ret;
    $("#button_" + groupId).children("label").text(ret);
  }
}

function initializeFiltersForm() {

  $(document).on("submit", "#mainform", function(event) {

    var names = JSON.stringify(filterNames);
    var codedData = JSON.stringify(filters);
    
    $("#mainform").append($('<input/>', {name: 'names', value: names, type: 'hidden'}));
    $("#mainform").append($('<input/>', {name: 'codedData', value: codedData, type: 'hidden'}));
  });
}

function updateToolbar() {
  var toShow = !$.isEmptyObject(filters);
  $('#edit_button').toggle(toShow);
  $('#remove_button').toggle(toShow);
}

function resetFilters(confirmText, codedDocumentLocation) {
  if (confirm(confirmText)) {
    document.location = codedDocumentLocation;
  }
}

$(function() {
  $('#select_all').change(function() {
    
    var checkboxes = $(".selectFilter");
    var shouldBeChecked = $(this).is(':checked');
    checkboxes.each(function(idx, checkbox) {
      toggleSelection($(checkbox), shouldBeChecked);
    });
  });
});