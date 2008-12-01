//\/////
//\  overLIB v 4.02 Caption Positioning Plugin
//\  This file requires overLIB 4.00 or later.
//\
//\  You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2003. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
////////
// PRE-INIT
// Ignore these lines, configuration is below.
////////
if (typeof olInfo == 'undefined' || typeof olInfo.meets == 'undefined' || !olInfo.meets(4.14)) alert('overLIB 4.14 or later is required for the Follow Scroll Plugin.');
else {
registerCommands('positioncap,scrollbars,src,data,noborder');
////////
// DEFAULT CONFIGURATION
// You don't have to change anything here if you don't want to. All of this can be
// changed on your html page or through an overLIB call.
////////
if (typeof ol_positioncap=='undefined') var ol_positioncap='top';
if (typeof ol_scrollbars=='undefined') var ol_scrollbars=0;
if (typeof ol_src=='undefined') var ol_src='';
if (typeof ol_data=='undefined') var ol_data='';
if (typeof ol_noborder=='undefined') var ol_noborder=0;
////////
// END OF CONFIGURATION
// Don't change anything below this line, all configuration is above.
////////
////////
// INIT
////////
// Runtime variables init. Don't change for config!
var o3_positioncap='top';
var o3_scrollbars=0;
var o3_src=o3_data='';
var o3_noborder=0;
////////
// PLUGIN FUNCTIONS
////////
function setPositionCapVariables() {
	o3_positioncap=ol_positioncap;
	o3_scrollbars=ol_scrollbars;
	o3_src=ol_src;
	o3_data=ol_data;
	o3_noborder=ol_noborder;
}
// Parses POSITIONCAP commands
function parsePositionCapExtras(pf,i,ar) {
	var k=i,v;
	if (k < ar.length) {
		if (ar[k]==POSITIONCAP) { eval(pf +'positioncap="'+ar[++k]+'"'); return k; }
		if (ar[k]==SCROLLBARS) { eval(pf+'scrollbars=('+pf+'scrollbars==0 ? 1 : 0)'); return k; }
		if (ar[k]==SRC) { eval(pf +'src="'+ar[++k]+'"'); return k; }
		if (ar[k]==DATA) { eval(pf +'data="'+ar[++k]+'"'); return k; }
		if (ar[k]==NOBORDER) { eval(pf+'noborder=('+pf+'noborder==0 ? 1 : 0)'); return k; }
	}
	return -1;
}
// Makes a simple table without caption
function ol_content_simple_psncap(text) {
	var bodyTxt, sHgt=getMinimumHeight(),cpIsMultiple=/,/.test(o3_cellpad);
	if (o3_scrollbars) text=addWrapTags(text,sHgt);
	bodyTxt='<table width="100%" border="0" '+((olNs4||!cpIsMultiple) ? 'cellpadding="'+o3_cellpad+'" ' : '')+'cellspacing="0" '+(o3_fgclass ? 'class="'+o3_fgclass+'"' : o3_fgcolor+' '+o3_fgbackground+' '+o3_height)+'><tr><td valign="TOP"'+(o3_textfontclass ? ' class="'+o3_textfontclass+'">' : ((!olNs4&&cpIsMultiple) ? ' style="'+setCellPadStr(o3_cellpad)+'">' : '>'))+(o3_textfontclass ? '' : wrapStr(0,o3_textsize,'text'))+text+(o3_textfontclass ? '' : wrapStr(1,o3_textsize))+'</td></tr></table>';
	if(o3_scrollbars) bodyTxt=setScrollbarFormatting(bodyTxt, sHgt);
	txt='<table width="'+o3_width+ '" border="0" cellpadding="'+o3_border+'" cellspacing="0" '+(o3_bgclass ? 'class="'+o3_bgclass+'"' : o3_bgcolor+' '+o3_height)+'><tr><td>'+bodyTxt+'</td></tr></table>';
	set_background("");
	return txt;
}
// Makes table with caption and optional close link
function ol_content_caption_psncap(text,title,close) {
	var nameId, sHgt=getMinimumHeight(), caption, vPosn, posCap, capPosn=o3_positioncap.toUpperCase(), cpIsMultiple=/,/.test(o3_cellpad);
	posCap=(/^L/.test(capPosn) ? 'L' : (/^R/.test(capPosn) ? 'R' : ''));
	if (posCap=='L') capPosn=(/LEFT/.test(capPosn) ? capPosn.substring(4) : capPosn.substring(1));
	else if (posCap=='R') capPosn=(/RIGHT/.test(capPosn) ? capPosn.substring(5) : capPosn.substring(1));
	if (/^T/.test(capPosn)) vPosn = 'TOP';
	else if (/^B/.test(capPosn)) vPosn='BOTTOM';
	else if (/^M/.test(capPosn)) vPosn='MIDDLE';
	closing="";
	closeevent="onmouseover";
	if (o3_closeclick==1) closeevent= (o3_closetitle ? "title='" + o3_closetitle +"'" : "") + " onclick";
	if (o3_capicon!="") {
	  nameId=' hspace=\"5\"'+' align=\"middle\" alt=\"\"';
	  if (typeof o3_dragimg!='undefined'&&o3_dragimg) nameId=' hspace=\"5\"'+' name=\"'+o3_dragimg+'\" id=\"'+o3_dragimg+'\" align=\"middle\" alt=\"Drag Enabled\" title=\"Drag Enabled\"';
	  o3_capicon='<img src=\"'+o3_capicon+'\"'+nameId+' />';
	}
	if (close!="")
	 closing='<td '+(o3_closefontclass ? 'class="'+o3_closefontclass : 'align="RIGHT')+'"><a href="javascript:return '+fnRef+'cClick();" '+closeevent+'="return '+fnRef+'cClick();">'+(o3_closefontclass ? '' : wrapStr(0,o3_closesize,'close'))+close+(o3_closefontclass ? '' : wrapStr(1,o3_closesize,'close'))+'</a></td>';
	caption='<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td'+(o3_captionfontclass ? ' class="'+o3_captionfontclass+'">' : '>')+(o3_captionfontclass ? '' : '<b>'+wrapStr(0,o3_captionsize,'caption'))+o3_capicon+title+(o3_captionfontclass ? '' : wrapStr(1,o3_captionsize)+'</b>')+'</td>'+closing+'</tr></table>';
	if (!posCap) {
		if(o3_scrollbars) text=addWrapTags(text,sHgt);
		bodyTxt='<table width="100%" border="0" '+((olNs4||!cpIsMultiple) ? 'cellpadding="'+o3_cellpad+'" ' : '')+'cellspacing="0" '+(o3_fgclass ? 'class="'+o3_fgclass+'"' : o3_fgcolor+' '+o3_fgbackground+' '+o3_height)+'><tr><td valign="TOP"'+(o3_textfontclass ? ' class="'+o3_textfontclass+'">' :((!olNs4&&cpIsMultiple) ? ' style="'+setCellPadStr(o3_cellpad)+'">' : '>'))+(o3_textfontclass ? '' : wrapStr(0,o3_textsize,'text'))+text+(o3_textfontclass ? '' : wrapStr(1,o3_textsize)) + '</td></tr></table>';
		if(o3_scrollbars) bodyTxt=setScrollbarFormatting(bodyTxt, sHgt);
		txt='<table width="'+o3_width+ '" border="0" cellpadding="'+o3_border+'" cellspacing="0" '+(o3_bgclass ? 'class="'+o3_bgclass+'"' : o3_bgcolor+' '+o3_bgbackground+' '+o3_height)+'><tr><td>'+(vPosn=='TOP' ? caption : '')+bodyTxt+(vPosn!='TOP' ? caption : '')+'</td></tr></table>';
	} else {
		caption='<table width="'+o3_width+'" border="0" cellpadding="'+o3_border+'" cellspacing="0" '+(o3_bgclass ? 'class="'+o3_bgclass+'"' : o3_bgcolor+' '+o3_bgbackground+' '+o3_height)+'><tr><td>'+caption+'</td></tr></table>';
		bodyTxt=runHook('ol_content_simple',FALTERNATE,o3_css,text);
		txt='<table border="0" cellpadding="0" cellspacing="0"><tr>'+(posCap=='L' ? '<td valign="' + vPosn + '" width="'+o3_width+'">' + caption + '</td><td width="'+o3_width+'">' : '<td width="'+o3_width+'">') + bodyTxt + (posCap!='L' ? '</td><td valign="' + vPosn + '" width="'+o3_width+'">' + caption  :  '</td>')+'</tr></table>'
	}
	set_background("");
	return txt;
}
// Sets scrollbar formatting
function setScrollbarFormatting(text, hgt) {
	return /\.s?html?/.test(text) ? text : '<div style="position: relative; width: ' + o3_width + 'px; height: '+hgt+'px; overflow: auto;">'+text+'</div>';
}
function checkScrollbars() {
	if(olNs4&&o3_scrollbars) o3_scrollbars=0;  // disable for NS4.x
	if(!olNs4&&(o3_data||o3_src)) o3_scrollbars=1;
	if(o3_scrollbars) {
		if(o3_wrap) {
			o3_wrap=0;  // no wrapping with scroll bars
			o3_width=ol_width;  // reset width since it's been set to zero by WRAP command
		}
		if (!o3_sticky||(o3_sticky&&!o3_close)) {
			o3_sticky=1;  // make STICKY if there are scrollbars
			o3_mouseoff=1;  // turn MOUSEOFF on so that the popup can be closed
			opt_NOCLOSE(' ');
		}
	}
	return true;
}
function getMinimumHeight() {
	return (o3_height) ? parseInt(o3_height.match(/(\d+)/)[0]) : 100;
}
function addWrapTags(txt, hgt) {
	return !(o3_data||o3_src) ? txt : (o3_data) ? '<object data="'+o3_data+'" width="'+o3_width+'" height="'+hgt+'" type="text/html"></object>' : '<iframe src="'+o3_src+'" width="'+o3_width+'" height="'+hgt+'" scrolling="auto"'+(o3_noborder ? ' frameborder="0" border="0"' : '')+'></iframe>';
}
////////
// PLUGIN REGISTRATIONS
////////
registerRunTimeFunction(setPositionCapVariables);
registerCmdLineFunction(parsePositionCapExtras);
registerPostParseFunction(checkScrollbars);
registerHook("ol_content_caption",ol_content_caption_psncap,FREPLACE);
registerHook("ol_content_simple",ol_content_simple_psncap,FREPLACE);
if (olInfo.meets(4.14)) registerNoParameterCommands('scrollbars');
}
//end 
