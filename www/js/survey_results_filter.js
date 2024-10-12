function getLang() {
	var s = document.getElementById('lang_sel');
	var lang = s.options[s.selectedIndex].value;
	
	var LANGUAGES_COUNT = s.options[s.length-1].value;
	
	var css_filter = document.getElementById('survey_filter');
	
	css_filter.innerHTML = '';
	for (var i=0;i<=LANGUAGES_COUNT;i++){
		if (lang == i || lang==0)
			css_filter.innerHTML += '.ans_lang_'+i+' {display:visible;} \n';
		else css_filter.innerHTML += '.ans_lang_'+i+' {display:none;} \n';
	}
}
