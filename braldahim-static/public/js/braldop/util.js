
function getUrlParameter(name, defaultValue) {
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	if( results == null ) return defaultValue;
	else return results[1];
}

// remplace la fonction parseInt, trop capricieuse ( parseInt("05")==5 mais parseInt("08")==0 )
function atoi(s) {
	if (!s) return undefined;
	s = s.trim();
	while(s.charAt(0)=='0' || s.charAt(0)==':') {
		s = s.substring(1, s.length);
		if (s.length==0) return 0;
	 }
	return parseInt(s);
}

function formatDate(timestamp) {
	if (timestamp==0) return "";
	var d = new Date(timestamp);
	return d.getDate()+"/"+(d.getMonth()<9?("0"+(d.getMonth()+1)):(d.getMonth()+1))+" "+d.getHours()+"h"+(d.getMinutes()<10?("0"+d.getMinutes()):d.getMinutes());
}
