var VALID_RULE_NAMES = ["recyclable", "maxpeople", "maxweight", "hit", "weapon_skill", "speed", "engine", "weightdelay", "fuelmult", "fuel",
  "describable", "dock", "signwriting", "disassemblable", "energy", "shield", "time", "addcapacity", "addpeople",
  "maxspeed", "animal_hit", "parroting"];

var DEPRECATED_RULES = {
  "stores": "'stores' is deprecated, use property 'Storage' instead",
  "view": "'view' is deprecated, use property 'AlterViewRange' instead (see telescope)"
};

$(function() {
  $("#rules_input").keydown(function(event) {
    var inputField = $(event.target);

    var errorsList = [];
    var rules = inputField.val().split(";");
    for (var i = 0; i < rules.length; i++) {
      var rule = rules[i].split(":", 2)[0];
      if (rule in DEPRECATED_RULES) {
        errorsList.push(DEPRECATED_RULES[rule]);
      } else if (VALID_RULE_NAMES.indexOf(rule) == -1) {
        errorsList.push("'" + rule + "' is unknown");
      }
    }
    $("#rules_errors").empty().append(errorsList.join("<br>"));
  });

  var lastComment = localStorage.getItem("last_objecttype_comment");
  if (lastComment) {
    $("textarea[name='comments']").prop("placeholder", lastComment);
  }

  $("#useLastComment").click(function(event) {
    event.preventDefault();
    $("textarea[name='comments']").val(lastComment);
  });

  $("#objecttypeForm").submit(function(event) {
    localStorage.setItem("last_objecttype_comment", $("textarea[name='comments']").val());
  });

  $("input[name='propDetails[]']").keyup(function(event) {
    var propInput = $(event.target);
    var errorText = propInput.parent().find(".errorText");
    errorText.css("color", "red");

    var errText = "";
    if (propInput.val()) {
      try {
        $.parseJSON(propInput.val()); // try parsing as JSON
      } catch (e) {
        errText = "invalid JSON";
      }
    }

    errorText.text(errText);
  });
});