var visList = false;

function setLanguage(abbreviation, page) {
  location.href = '/' + abbreviation + '/index.php?page=' + page;
}
  

function toggleLangList(){
  if (!visList){
    var lang_div = document.getElementById('lang_div');
    lang_div.style.top = (document.getElementById('lang_dock').offsetTop*1 + 25) + 'px';
    lang_div.style.left = document.getElementById('lang_dock').offsetLeft + 'px';
    lang_div.style.display = 'block';
    visList = true;
  }
  else {
    document.getElementById('lang_div').style.display = 'none';
    visList = false;
  }
}