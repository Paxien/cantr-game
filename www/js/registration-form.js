$.validator.addMethod(
  "regex",
  function(value, element, regexp) {
    var check = false;
    return this.optional(element) || regexp.test(value);
  },
  "Unused text"
);


$(function() {
  $(".reactivation-link").click(function(event) {
    event.preventDefault();

    window.location.href = "index.php?page=send_reactivation_email&email=" + $("#email").val();
  });

  $("#registration-form").validate({
    rules: {
      username: {
        required: true,
        remote: {
          type: "post",
          url: "/validate_form.php",
          dataType: "json",
          data: {
            page: "validate_username",
            username: function() {
              return $("#username").val();
            }
          },
        }
      },
      email: {
        required: true,
        email: true,
        remote: {
          type: "post",
          url: "/validate_form.php",
          dataType: "json",
          data: {
            page: "validate_email",
            email: function() {
              return $("#email").val();
            }
          },
          dataFilter: function(jsonData) {
            var data = JSON.parse(jsonData);
            $(".reactivation-option").toggle(!data.activeAccount && data.inactiveAccount);

            return JSON.stringify(!data.activeAccount);
          }
        }
      },
      password: {
        required: true,
        minlength: 6
      },
      password_retype: {
        required: true,
        equalTo: "#password"
      },
      terms: {
        required: true,
      },
      privacy: {
        required: true,
      },
      email_accept: {
        required: true,
      },
      year: {
        required: true,
        minlength: 2,
        maxlength: 4
      },
      firstname: {
        required: true
      },
      lastname: {
        required: true
      },
      country: {
        required: true
      },
      charname1: {
        regex: /^[^0-9!@#$%^&*()_]+$/,
        required: true,
      },
      sex1: {
        required: true,
      },
    },
    messages: {
      username: {
        required: tagText("register_error_field_required"),
        remote: tagText("register_error_username_already_exists"),
      },
      email: {
        required: tagText("register_error_field_required"),
        email: tagText("register_error_invalid_email_format"),
        remote: tagText("register_error_email_already_exists")
      },
      password: {
        required: tagText("register_error_field_required"),
        minlength: tagText("register_error_password_min_length")
      },
      password_retype: {
        required: tagText("register_error_field_required"),
        equalTo: tagText("register_error_passwords_not_match")
      },
      terms: {
        required: tagText("register_error_field_required")
      },
      privacy: {
        required: tagText("register_error_field_required")
      },
      email_accept: {
        required: tagText("register_error_field_required")
      },
      year: {
        required: tagText("register_error_field_required"),
        minlength: tagText("register_error_year_format"),
        maxlength: tagText("register_error_year_format")
      },
      firstname: {
        required: tagText("register_error_field_required")
      },
      lastname: {
        required: tagText("register_error_field_required")
      },
      country: {
        required: tagText("register_error_field_required")
      },
      charname1: {
        required: tagText("register_error_field_required"),
        regex: tagText("register_error_character_name_format")
      },
    },
    errorPlacement: function(error, element) {
      error.addClass("register-form-error-message");
      var infoAboutRequiredField = element.siblings(".register-nec");
      if (infoAboutRequiredField.length) {
        error.insertAfter(infoAboutRequiredField);
      } else {
        error.insertAfter(element);
      }
    },
    success: function(label) {
      $(label).append(
        $("<img/>", {
          src: "/graphics/cantr/pictures/tiny_ok.gif",
        }).css({
          height: "1em",
          width: "auto"
        }).addClass("register-form-success-icon")
      );
    },
    submitHandler: function(form, event) {
      var selectedLanguageId = $(".register-language option:selected").val();
      _paq.push(['trackEvent', 'Registration', 'Submit', englishLanguageNames[selectedLanguageId]]);
      form.submit();
    }
  });

  var registrationFormWizard = $("#registration-form-wizard");

  registrationFormWizard.steps({
    headerTag: "h2",
    enablePagination: false,
    onStepChanging: function(event, currentIndex, newIndex) {
      if (currentIndex > newIndex) {
        return true;
      }
      return $("#registration-form").valid();
    },
    onStepChanged: function(event, currentIndex, priorIndex) {
      if (currentIndex === 3) {
        $(".register-next-button button").text(tagText("button_register"));
      } else {
        $(".register-next-button button").text(tagText("plain_button_next"));
      }
      if (currentIndex === 2) {
        $("#comment").attr("rows", 1).autosize();
      }
    }
  });

  registrationFormWizard.steps("insert", 0, {
    title: tagText("register_step_game_rules")
  });

  $(".register-next-button").click(function(event) {
    if (registrationFormWizard.steps("getCurrentIndex") !== 3) {
      event.preventDefault();
      registrationFormWizard.steps("next");
    }
  });

  $(".register-back-button").click(function(event) {
    if (registrationFormWizard.steps("getCurrentIndex") !== 1) {
      event.preventDefault();
      registrationFormWizard.steps("previous");
    }
  });
});