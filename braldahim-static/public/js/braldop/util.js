
function getUrlParameter(name, defaultValue) {
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	if( results == null ) return defaultValue;
	else return results[1];
}


function formatDate(timestamp, withYear) {
	if (timestamp==0) return "";
	var d = new Date(timestamp);
	var s = d.getDate()+"/"+(d.getMonth()<9?("0"+(d.getMonth()+1)):(d.getMonth()+1))
	if (withYear) s+='/'+d.getFullYear();
	s +=" "+d.getHours()+"h"+(d.getMinutes()<10?("0"+d.getMinutes()):d.getMinutes());
	return s;
}

function StringEndsWith(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

function StringContains(str, suffix) {
    return str.indexOf(suffix, 0) !== -1;
}
