var MAX_PREFIX_LENGTH = 160; // 160 description characters

$(function() {
  $("span.txt-label").each(function() {
    var fullText = $(this).text();
    if (fullText.length > MAX_PREFIX_LENGTH) {
      var firstPart = fullText.substr(0, MAX_PREFIX_LENGTH);
      var secondPart = fullText.substr(MAX_PREFIX_LENGTH);
      $(this).text(firstPart);
      var restOfDesc = $("<span></span>", {
        class: "txt-label-the-rest"
      }).text(secondPart)
        .data("full-text", fullText)
        .css({display: "none"});
      $(this).append(
        restOfDesc,
        " ",
        $("<a></a>", {href: "javascript:void(0)"})
          .text("[...]").click(function(event) {
          restOfDesc.show();
          $(event.target).hide();
        })
      );
    }
  });
});
