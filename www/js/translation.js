function getVar(varName) {
  varValue = "";
  location.search.replace('?', '').split('&').forEach(function (val) {
      split = val.split("=", 2);
      if (split[0] == varName) {
        varValue = split[1];
      }
  });
  return varValue;
}

$(function (){
  $("#formTranslate").submit(function() {
    if (getVar("action") != "add1" && this.language.value!=getVar("lang1")) {
     return !!confirm(tagText("js_confirm_language"));
    }
  });
});

$(function (){
  $("#formDownload").submit(function(event) {
    event.preventDefault();
    var selected = $("input[type='radio'][name='download_option']:checked");
    var downloadOption = "";
    if (selected.length > 0) {
      downloadOption = selected.val();
    }
    var lang1 = $("select[name='lang1']").val();
    var lang2 = $("select[name='lang2']").val();
    $("#downloadFrame").attr("src","index.php?page=managetranslations&action=downloadfile&downloadoption="+downloadOption+"&lang1="+lang1+"&lang2="+lang2);
  });
});