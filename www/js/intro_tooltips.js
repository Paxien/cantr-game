function getText(name) {
  if (typeof tipTexts === "undefined") {
    return name;
  }
  if (name in tipTexts[language]) {
    return tipTexts[language][name];
  }
  return tipTexts[1][name];
}

$(function() {
  var intro = introJs();
  var pageName = $("#pageName").val();

  if (pageName === "char") {
    pageName = "char.events";
  }

  var tooltipTexts = {
    "char.events": [{
      intro: "tip_events_general",
    }, {
      intro: "tip_events_capital_rule_1",
    }, {
      intro: "tip_events_capital_rule_2",
    }, {
      element: "#ownCharacterInfo",
      intro: "tip_events_your_chardesc",
    }, {
      position: "top",
      element: "#eventsList:first-child",
      intro: "tip_events_events",
    }, {
      element: "#navigationPanel",
      intro: "tip_events_nav_panel",
    }
    ],
    "char.inventory": [{
      intro: "tip_inventory_general",
    }, {
      element: "#charInfoLocation",
      intro: "tip_inventory_unknown_location",
    }, {
      element: "#charInfoLocation",
      intro: "tip_inventory_agreements",
    }, {
      element: "#character_menu img[src='/graphics/cantr/pictures/button_note.gif']",
      intro: "tip_inventory_notes",
    }
    ],
    "char.description": [{
      intro: "tip_location_general",
    }, {
      intro: "tip_location_in_building_vehicle",
    }, {
      element: "#weatherData",
      intro: "tip_location_seasons",
    }, {
      element: "#animalsButton",
      intro: "tip_location_animals",
    }, {
      element: "#exitRoutes",
      position: "top",
      intro: "tip_location_exit_routes",
    }
    ],
    "char.people": [{
      intro: "tip_people_general",
    }, {
      intro: "tip_people_remember",
    }, {
      element: "table img[src='/graphics/cantr/pictures/button_small_drag.gif']:first-child",
      intro: "tip_people_drag",
    }, {
      element: "table img[src='/graphics/cantr/pictures/button_small_hit.gif']:first-child",
      intro: "tip_people_attack",
    }, {
      element: "table img[src='/graphics/cantr/pictures/button_small_hit.gif']:first-child",
      intro: "tip_people_death",
    }
    ],
    "char.projects": [{
      intro: "tip_projects_general",
    }, {
      element: "#projectsList tr:first-child td:last-child",
      intro: "tip_projects_details",
    }, {
      element: "#projectsList input[src='/graphics/cantr/pictures/button_small_info.gif']",
      intro: "tip_projects_info",
    }, {
      element: "#projectsList input[src='/graphics/cantr/pictures/button_small_end.gif']",
      intro: "tip_projects_delete",
    }, {
      position: "top",
      element: "#character_menu img[src='/graphics/cantr/pictures/button_build.gif']",
      intro: "tip_projects_buildmenu",
    }
    ],
    "build": [{
      intro: "tip_buildmenu_general",
    }, {
      intro: "tip_buildmenu_sim",
    }
    ],
  };

  // change text tags to translations
  for (var page in tooltipTexts) {
    tooltipTexts[page] = tooltipTexts[page].map(function(introText) {
      introText["intro"] = getText(introText["intro"]);
      return introText;
    });
  }

  if (pageName in tooltipTexts) { // if there's help available
    intro.setOptions({
      'nextLabel': getText("tip_next"), 'prevLabel': getText("tip_prev"),
      'skipLabel': getText("tip_skip"), 'doneLabel': getText("tip_end"),
      'exitOnOverlayClick': false
    });

    intro.setOptions({"steps": tooltipTexts[pageName]});

    intro.onafterchange(function() {
      var nextButton = $('.introjs-nextbutton');
      nextButton.toggle(!nextButton.hasClass('introjs-disabled'));
    });

    var markAsShown = function() { // every tip should be shown just once
      localStorage.setItem("tip_" + pageName, "true");
    };

    intro.oncomplete(markAsShown);
    intro.onexit(markAsShown);

    // compatibility check - don't show popups if it's not possible to remember which are already read
    // disable popup, because the data about being read is not transferred across different devices
    if (false && (localStorage !== null) && !localStorage.getItem("tip_" + pageName)) {
      intro.start();
    }

    if (typeof tipTexts !== "undefined") {
      $("#topBar").after($("<p></p>", {id: "helpIcon"}).click(function() {
          intro.start();
        }).append(
        $("<span></span>").text(getText("tip_show_help")),
        $('<img/>', {src: "/graphics/cantr/pictures/icon_bulb.gif"})).css({
          right: "30px",
          top: "10px",
          position: "fixed",
          cursor: "pointer",
        })
      );
    }
  }

  $('.front-page-root .ctaContainer').hide();
});
