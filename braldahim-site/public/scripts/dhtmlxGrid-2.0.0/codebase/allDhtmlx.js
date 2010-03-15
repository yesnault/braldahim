//commun + grid + cell + dhtmlxgrid_srnd.js + dhtmlxgrid_excell_link.js
//v.2.0 build 81009

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/
dhtmlxAjax={get:function(url,callback){var t=new dtmlXMLLoaderObject(true);t.async=(arguments.length<3);t.waitCall=callback;t.loadXML(url)
 return t},
 post:function(url,post,callback){var t=new dtmlXMLLoaderObject(true);t.async=(arguments.length<4);t.waitCall=callback;t.loadXML(url,true,post)
 return t},
 getSync:function(url){return this.get(url,null,true)
 },
 postSync:function(url,post){return this.post(url,post,null,true)}};function dtmlXMLLoaderObject(funcObject, dhtmlObject, async, rSeed){this.xmlDoc="";if (typeof (async)!= "undefined")
 this.async=async;else
 this.async=true;this.onloadAction=funcObject||null;this.mainObject=dhtmlObject||null;this.waitCall=null;this.rSeed=rSeed||false;return this};dtmlXMLLoaderObject.prototype.waitLoadFunction=function(dhtmlObject){var once = true;this.check=function (){if ((dhtmlObject)&&(dhtmlObject.onloadAction != null)){if ((!dhtmlObject.xmlDoc.readyState)||(dhtmlObject.xmlDoc.readyState == 4)){if (!once)return;once=false;if (typeof dhtmlObject.onloadAction == "function")dhtmlObject.onloadAction(dhtmlObject.mainObject, null, null, null, dhtmlObject);if (dhtmlObject.waitCall){dhtmlObject.waitCall.call(this,dhtmlObject);dhtmlObject.waitCall=null}}}};return this.check};dtmlXMLLoaderObject.prototype.getXMLTopNode=function(tagName, oldObj){if (this.xmlDoc.responseXML){var temp = this.xmlDoc.responseXML.getElementsByTagName(tagName);if(temp.length==0 && tagName.indexOf(":")!=-1)
 var temp = this.xmlDoc.responseXML.getElementsByTagName((tagName.split(":"))[1]);var z = temp[0]}else
 var z = this.xmlDoc.documentElement;if (z){this._retry=false;return z};if ((_isIE)&&(!this._retry)){var xmlString = this.xmlDoc.responseText;var oldObj = this.xmlDoc;this._retry=true;this.xmlDoc=new ActiveXObject("Microsoft.XMLDOM");this.xmlDoc.async=false;this.xmlDoc["loadXM"+"L"](xmlString);return this.getXMLTopNode(tagName, oldObj)};dhtmlxError.throwError("LoadXML", "Incorrect XML", [
 (oldObj||this.xmlDoc),
 this.mainObject
 ]);return document.createElement("DIV")};dtmlXMLLoaderObject.prototype.loadXMLString=function(xmlString){{
 try{var parser = new DOMParser();this.xmlDoc=parser.parseFromString(xmlString, "text/xml")}catch (e){this.xmlDoc=new ActiveXObject("Microsoft.XMLDOM");this.xmlDoc.async=this.async;this.xmlDoc["loadXM"+"L"](xmlString)}};this.onloadAction(this.mainObject, null, null, null, this);if (this.waitCall){this.waitCall();this.waitCall=null}};dtmlXMLLoaderObject.prototype.loadXML=function(filePath, postMode, postVars, rpc){if (this.rSeed)filePath+=((filePath.indexOf("?") != -1) ? "&" : "?")+"a_dhx_rSeed="+(new Date()).valueOf();this.filePath=filePath;if ((!_isIE)&&(window.XMLHttpRequest))
 this.xmlDoc=new XMLHttpRequest();else {if (document.implementation&&document.implementation.createDocument){this.xmlDoc=document.implementation.createDocument("", "", null);this.xmlDoc.onload=new this.waitLoadFunction(this);this.xmlDoc.load(filePath);return}else
 this.xmlDoc=new ActiveXObject("Microsoft.XMLHTTP")};if (this.async)this.xmlDoc.onreadystatechange=new this.waitLoadFunction(this);this.xmlDoc.open(postMode ? "POST" : "GET", filePath, this.async);if (rpc){this.xmlDoc.setRequestHeader("User-Agent", "dhtmlxRPC v0.1 ("+navigator.userAgent+")");this.xmlDoc.setRequestHeader("Content-type", "text/xml")}else if (postMode)this.xmlDoc.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');this.xmlDoc.setRequestHeader("X-Requested-With","XMLHttpRequest");this.xmlDoc.send(null||postVars);if (!this.async)(new this.waitLoadFunction(this))()};dtmlXMLLoaderObject.prototype.destructor=function(){this.onloadAction=null;this.mainObject=null;this.xmlDoc=null;return null};dtmlXMLLoaderObject.prototype.xmlNodeToJSON = function(node){var t={};for (var i=0;i<node.attributes.length;i++)t[node.attributes[i].name]=node.attributes[i].value;t["_tagvalue"]=node.firstChild?node.firstChild.nodeValue:"";for (var i=0;i<node.childNodes.length;i++){var name=node.childNodes[i].tagName;if (name){if (!t[name])t[name]=[];t[name].push(this.xmlNodeToJSON(node.childNodes[i]))}};return t};function callerFunction(funcObject, dhtmlObject){this.handler=function(e){if (!e)e=window.event;funcObject(e, dhtmlObject);return true};return this.handler};function getAbsoluteLeft(htmlObject){var xPos = htmlObject.offsetLeft;var temp = htmlObject.offsetParent;while (temp != null){xPos+=temp.offsetLeft;temp=temp.offsetParent};return xPos};function getAbsoluteTop(htmlObject){var yPos = htmlObject.offsetTop;var temp = htmlObject.offsetParent;while (temp != null){yPos+=temp.offsetTop;temp=temp.offsetParent};return yPos};function convertStringToBoolean(inputString){if (typeof (inputString)== "string")
 inputString=inputString.toLowerCase();switch (inputString){case "1":
 case "true":
 case "yes":
 case "y":
 case 1:
 case true:
 return true;break;default: return false}};function getUrlSymbol(str){if (str.indexOf("?")!= -1)
 return "&"
 else
 return "?"
};function dhtmlDragAndDropObject(){if (window.dhtmlDragAndDrop)return window.dhtmlDragAndDrop;this.lastLanding=0;this.dragNode=0;this.dragStartNode=0;this.dragStartObject=0;this.tempDOMU=null;this.tempDOMM=null;this.waitDrag=0;window.dhtmlDragAndDrop=this;return this};dhtmlDragAndDropObject.prototype.removeDraggableItem=function(htmlNode){htmlNode.onmousedown=null;htmlNode.dragStarter=null;htmlNode.dragLanding=null};dhtmlDragAndDropObject.prototype.addDraggableItem=function(htmlNode, dhtmlObject){htmlNode.onmousedown=this.preCreateDragCopy;htmlNode.dragStarter=dhtmlObject;this.addDragLanding(htmlNode, dhtmlObject)};dhtmlDragAndDropObject.prototype.addDragLanding=function(htmlNode, dhtmlObject){htmlNode.dragLanding=dhtmlObject};dhtmlDragAndDropObject.prototype.preCreateDragCopy=function(e){if (e&&(e||event).button == 2)
 return;if (window.dhtmlDragAndDrop.waitDrag){window.dhtmlDragAndDrop.waitDrag=0;document.body.onmouseup=window.dhtmlDragAndDrop.tempDOMU;document.body.onmousemove=window.dhtmlDragAndDrop.tempDOMM;return false};window.dhtmlDragAndDrop.waitDrag=1;window.dhtmlDragAndDrop.tempDOMU=document.body.onmouseup;window.dhtmlDragAndDrop.tempDOMM=document.body.onmousemove;window.dhtmlDragAndDrop.dragStartNode=this;window.dhtmlDragAndDrop.dragStartObject=this.dragStarter;document.body.onmouseup=window.dhtmlDragAndDrop.preCreateDragCopy;document.body.onmousemove=window.dhtmlDragAndDrop.callDrag;if ((e)&&(e.preventDefault)){e.preventDefault();return false};return false};dhtmlDragAndDropObject.prototype.callDrag=function(e){if (!e)e=window.event;dragger=window.dhtmlDragAndDrop;if ((e.button == 0)&&(_isIE))
 return dragger.stopDrag();if (!dragger.dragNode&&dragger.waitDrag){dragger.dragNode=dragger.dragStartObject._createDragNode(dragger.dragStartNode, e);if (!dragger.dragNode)return dragger.stopDrag();dragger.gldragNode=dragger.dragNode;document.body.appendChild(dragger.dragNode);document.body.onmouseup=dragger.stopDrag;dragger.waitDrag=0;dragger.dragNode.pWindow=window;dragger.initFrameRoute()};if (dragger.dragNode.parentNode != window.document.body){var grd = dragger.gldragNode;if (dragger.gldragNode.old)grd=dragger.gldragNode.old;grd.parentNode.removeChild(grd);var oldBody = dragger.dragNode.pWindow;if (_isIE){var div = document.createElement("Div");div.innerHTML=dragger.dragNode.outerHTML;dragger.dragNode=div.childNodes[0]}else
 dragger.dragNode=dragger.dragNode.cloneNode(true);dragger.dragNode.pWindow=window;dragger.gldragNode.old=dragger.dragNode;document.body.appendChild(dragger.dragNode);oldBody.dhtmlDragAndDrop.dragNode=dragger.dragNode};dragger.dragNode.style.left=e.clientX+15+(dragger.fx
 ? dragger.fx*(-1)
 : 0)
 +(document.body.scrollLeft||document.documentElement.scrollLeft)+"px";dragger.dragNode.style.top=e.clientY+3+(dragger.fy
 ? dragger.fy*(-1)
 : 0)
 +(document.body.scrollTop||document.documentElement.scrollTop)+"px";if (!e.srcElement)var z = e.target;else
 z=e.srcElement;dragger.checkLanding(z, e)};dhtmlDragAndDropObject.prototype.calculateFramePosition=function(n){if (window.name){var el = parent.frames[window.name].frameElement.offsetParent;var fx = 0;var fy = 0;while (el){fx+=el.offsetLeft;fy+=el.offsetTop;el=el.offsetParent};if ((parent.dhtmlDragAndDrop)){var ls = parent.dhtmlDragAndDrop.calculateFramePosition(1);fx+=ls.split('_')[0]*1;fy+=ls.split('_')[1]*1};if (n)return fx+"_"+fy;else
 this.fx=fx;this.fy=fy};return "0_0"};dhtmlDragAndDropObject.prototype.checkLanding=function(htmlObject, e){if ((htmlObject)&&(htmlObject.dragLanding)){if (this.lastLanding)this.lastLanding.dragLanding._dragOut(this.lastLanding);this.lastLanding=htmlObject;this.lastLanding=this.lastLanding.dragLanding._dragIn(this.lastLanding, this.dragStartNode, e.clientX,
 e.clientY, e);this.lastLanding_scr=(_isIE ? e.srcElement : e.target)}else {if ((htmlObject)&&(htmlObject.tagName != "BODY"))
 this.checkLanding(htmlObject.parentNode, e);else {if (this.lastLanding)this.lastLanding.dragLanding._dragOut(this.lastLanding, e.clientX, e.clientY, e);this.lastLanding=0;if (this._onNotFound)this._onNotFound()}}};dhtmlDragAndDropObject.prototype.stopDrag=function(e, mode){dragger=window.dhtmlDragAndDrop;if (!mode){dragger.stopFrameRoute();var temp = dragger.lastLanding;dragger.lastLanding=null;if (temp)temp.dragLanding._drag(dragger.dragStartNode, dragger.dragStartObject, temp, (_isIE
 ? event.srcElement
 : e.target))};dragger.lastLanding=null;if ((dragger.dragNode)&&(dragger.dragNode.parentNode == document.body))
 dragger.dragNode.parentNode.removeChild(dragger.dragNode);dragger.dragNode=0;dragger.gldragNode=0;dragger.fx=0;dragger.fy=0;dragger.dragStartNode=0;dragger.dragStartObject=0;document.body.onmouseup=dragger.tempDOMU;document.body.onmousemove=dragger.tempDOMM;dragger.tempDOMU=null;dragger.tempDOMM=null;dragger.waitDrag=0};dhtmlDragAndDropObject.prototype.stopFrameRoute=function(win){if (win)window.dhtmlDragAndDrop.stopDrag(1, 1);for (var i = 0;i < window.frames.length;i++)if ((window.frames[i] != win)&&(window.frames[i].dhtmlDragAndDrop))
 window.frames[i].dhtmlDragAndDrop.stopFrameRoute(window);if ((parent.dhtmlDragAndDrop)&&(parent != window)&&(parent != win))
 parent.dhtmlDragAndDrop.stopFrameRoute(window)};dhtmlDragAndDropObject.prototype.initFrameRoute=function(win, mode){if (win){window.dhtmlDragAndDrop.preCreateDragCopy();window.dhtmlDragAndDrop.dragStartNode=win.dhtmlDragAndDrop.dragStartNode;window.dhtmlDragAndDrop.dragStartObject=win.dhtmlDragAndDrop.dragStartObject;window.dhtmlDragAndDrop.dragNode=win.dhtmlDragAndDrop.dragNode;window.dhtmlDragAndDrop.gldragNode=win.dhtmlDragAndDrop.dragNode;window.document.body.onmouseup=window.dhtmlDragAndDrop.stopDrag;window.waitDrag=0;if (((!_isIE)&&(mode))&&((!_isFF)||(_FFrv < 1.8)))
 window.dhtmlDragAndDrop.calculateFramePosition()};if ((parent.dhtmlDragAndDrop)&&(parent != window)&&(parent != win))
 parent.dhtmlDragAndDrop.initFrameRoute(window);for (var i = 0;i < window.frames.length;i++)if ((window.frames[i] != win)&&(window.frames[i].dhtmlDragAndDrop))
 window.frames[i].dhtmlDragAndDrop.initFrameRoute(window, ((!win||mode) ? 1 : 0))};var _isFF = false;var _isIE = false;var _isOpera = false;var _isKHTML = false;var _isMacOS = false;if (navigator.userAgent.indexOf('Macintosh')!= -1)
 _isMacOS=true;if ((navigator.userAgent.indexOf('Safari')!= -1)||(navigator.userAgent.indexOf('Konqueror') != -1)){var _KHTMLrv = parseFloat(navigator.userAgent.substr(navigator.userAgent.indexOf('Safari')+7, 5));if (_KHTMLrv > 525){_isFF=true;var _FFrv = 1.9}else
 _isKHTML=true}else if (navigator.userAgent.indexOf('Opera')!= -1){_isOpera=true;_OperaRv=parseFloat(navigator.userAgent.substr(navigator.userAgent.indexOf('Opera')+6, 3))}else if (navigator.appName.indexOf("Microsoft")!= -1)
 _isIE=true;else {_isFF=true;var _FFrv = parseFloat(navigator.userAgent.split("rv:")[1])
};function isIE(){if (navigator.appName.indexOf("Microsoft")!= -1)
 if (navigator.userAgent.indexOf('Opera')== -1)
 return true;return false};dtmlXMLLoaderObject.prototype.doXPath=function(xpathExp, docObj, namespace, result_type){if ((_isKHTML))
 return this.doXPathOpera(xpathExp, docObj);if (_isIE){if (!docObj)if (!this.xmlDoc.nodeName)docObj=this.xmlDoc.responseXML
 else
 docObj=this.xmlDoc;if (!docObj)dhtmlxError.throwError("LoadXML", "Incorrect XML", [
 (docObj||this.xmlDoc),
 this.mainObject
 ]);if (namespace != null)docObj.setProperty("SelectionNamespaces", "xmlns:xsl='"+namespace+"'");if (result_type == 'single'){return docObj.selectSingleNode(xpathExp)}else {return docObj.selectNodes(xpathExp)||new Array(0)}}else {var nodeObj = docObj;if (!docObj){if (!this.xmlDoc.nodeName){docObj=this.xmlDoc.responseXML
 }else {docObj=this.xmlDoc}};if (!docObj)dhtmlxError.throwError("LoadXML", "Incorrect XML", [
 (docObj||this.xmlDoc),
 this.mainObject
 ]);if (docObj.nodeName.indexOf("document")!= -1){nodeObj=docObj}else {nodeObj=docObj;docObj=docObj.ownerDocument};var retType = XPathResult.ANY_TYPE;if (result_type == 'single')retType=XPathResult.FIRST_ORDERED_NODE_TYPE
 var rowsCol = new Array();var col = docObj.evaluate(xpathExp, nodeObj, function(pref){return namespace
 }, retType, null);if (retType == XPathResult.FIRST_ORDERED_NODE_TYPE){return col.singleNodeValue};var thisColMemb = col.iterateNext();while (thisColMemb){rowsCol[rowsCol.length]=thisColMemb;thisColMemb=col.iterateNext()};return rowsCol}};function _dhtmlxError(type, name, params){if (!this.catches)this.catches=new Array();return this};_dhtmlxError.prototype.catchError=function(type, func_name){this.catches[type]=func_name};_dhtmlxError.prototype.throwError=function(type, name, params){if (this.catches[type])return this.catches[type](type, name, params);if (this.catches["ALL"])return this.catches["ALL"](type, name, params);alert("Error type: "+arguments[0]+"\nDescription: "+arguments[1]);return null};window.dhtmlxError=new _dhtmlxError();dtmlXMLLoaderObject.prototype.doXPathOpera=function(xpathExp, docObj){var z = xpathExp.replace(/[\/]+/gi, "/").split('/');var obj = null;var i = 1;if (!z.length)return [];if (z[0] == ".")obj=[docObj];else if (z[0] == ""){obj=(this.xmlDoc.responseXML||this.xmlDoc).getElementsByTagName(z[i].replace(/\[[^\]]*\]/g, ""));i++}else
 return [];for (i;i < z.length;i++)obj=this._getAllNamedChilds(obj, z[i]);if (z[i-1].indexOf("[")!= -1)
 obj=this._filterXPath(obj, z[i-1]);return obj};dtmlXMLLoaderObject.prototype._filterXPath=function(a, b){var c = new Array();var b = b.replace(/[^\[]*\[\@/g, "").replace(/[\[\]\@]*/g, "");for (var i = 0;i < a.length;i++)if (a[i].getAttribute(b))
 c[c.length]=a[i];return c};dtmlXMLLoaderObject.prototype._getAllNamedChilds=function(a, b){var c = new Array();if (_isKHTML)b=b.toUpperCase();for (var i = 0;i < a.length;i++)for (var j = 0;j < a[i].childNodes.length;j++){if (_isKHTML){if (a[i].childNodes[j].tagName&&a[i].childNodes[j].tagName.toUpperCase()== b)
 c[c.length]=a[i].childNodes[j]}else if (a[i].childNodes[j].tagName == b)c[c.length]=a[i].childNodes[j]};return c};function dhtmlXHeir(a, b){for (var c in b)if (typeof (b[c])== "function")
 a[c]=b[c];return a};function dhtmlxEvent(el, event, handler){if (el.addEventListener)el.addEventListener(event, handler, false);else if (el.attachEvent)el.attachEvent("on"+event, handler)};dtmlXMLLoaderObject.prototype.xslDoc=null;dtmlXMLLoaderObject.prototype.setXSLParamValue=function(paramName, paramValue, xslDoc){if (!xslDoc)xslDoc=this.xslDoc

 if (xslDoc.responseXML)xslDoc=xslDoc.responseXML;var item =
 this.doXPath("/xsl:stylesheet/xsl:variable[@name='"+paramName+"']", xslDoc,
 "http:/\/www.w3.org/1999/XSL/Transform", "single");if (item != null)item.firstChild.nodeValue=paramValue
};dtmlXMLLoaderObject.prototype.doXSLTransToObject=function(xslDoc, xmlDoc){if (!xslDoc)xslDoc=this.xslDoc;if (xslDoc.responseXML)xslDoc=xslDoc.responseXML

 if (!xmlDoc)xmlDoc=this.xmlDoc;if (xmlDoc.responseXML)xmlDoc=xmlDoc.responseXML

 
 if (!isIE()){if (!this.XSLProcessor){this.XSLProcessor=new XSLTProcessor();this.XSLProcessor.importStylesheet(xslDoc)};var result = this.XSLProcessor.transformToDocument(xmlDoc)}else {var result = new ActiveXObject("Msxml2.DOMDocument.3.0");xmlDoc.transformNodeToObject(xslDoc, result)};return result};dtmlXMLLoaderObject.prototype.doXSLTransToString=function(xslDoc, xmlDoc){return this.doSerialization(this.doXSLTransToObject(xslDoc, xmlDoc))};dtmlXMLLoaderObject.prototype.doSerialization=function(xmlDoc){if (!xmlDoc)xmlDoc=this.xmlDoc;if (xmlDoc.responseXML)xmlDoc=xmlDoc.responseXML
 if (!isIE()){var xmlSerializer = new XMLSerializer();return xmlSerializer.serializeToString(xmlDoc)}else
 return xmlDoc.xml};// (c)dhtmlx ltd. www.dhtmlx.com
// v.2.0 build 81009

/*
 * Copyright DHTMLX LTD. http://www.dhtmlx.com You allowed to use this component
 * or parts of it under GPL terms To use it on other terms or get Professional
 * edition of the component please contact us at sales@dhtmlx.com
 */
// v.2.0 build 81107

 /*
	 * Copyright DHTMLX LTD. http://www.dhtmlx.com You allowed to use this
	 * component or parts of it under GPL terms To use it on other terms or get
	 * Professional edition of the component please contact us at
	 * sales@dhtmlx.com
	 */


 var globalActiveDHTMLGridObject;String.prototype._dhx_trim=function(){return this.replace(/&nbsp;/g, " ").replace(/(^[ \t]*)|([ \t]*$)/g, "")};function dhtmlxArray(ar){return dhtmlXHeir((ar||new Array()), dhtmlxArray._master)};dhtmlxArray._master={_dhx_find:function(pattern){for (var i = 0;i < this.length;i++){if (pattern == this[i])return i};return -1},
  _dhx_insertAt:function(ind, value){this[this.length]=null;for (var i = this.length-1;i >= ind;i--)this[i]=this[i-1]
  this[ind]=value
  },
  _dhx_removeAt:function(ind){this.splice(ind,1)
  },
  _dhx_swapItems:function(ind1, ind2){var tmp = this[ind1];this[ind1]=this[ind2]
  this[ind2]=tmp}};function dhtmlXGridObject(id){if (_isIE)try{document.execCommand("BackgroundImageCache", false, true)}catch (e){};if (id){if (typeof (id)== 'object'){this.entBox=id
  this.entBox.id="cgrid2_"+this.uid()}else
  this.entBox=document.getElementById(id)}else {this.entBox=document.createElement("DIV");this.entBox.id="cgrid2_"+this.uid()};this.entBox.innerHTML="";this.dhx_Event();var self = this;this._wcorr=0;this.cell=null;this.row=null;this.iconURL="";this.editor=null;this._f2kE=true;this._dclE=true;this.combos=new Array(0);this.defVal=new Array(0);this.rowsAr={};this.rowsBuffer=dhtmlxArray();this.rowsCol=dhtmlxArray();this._data_cache={};this._ecache={};this._ud_enabled=true;this.xmlLoader=new dtmlXMLLoaderObject(this.doLoadDetails, this, true, this.no_cashe);this._maskArr=[];this.selectedRows=dhtmlxArray();this.UserData={};this._sizeFix=this._borderFix=0;this.entBox.className+=" gridbox";this.entBox.style.width=this.entBox.getAttribute("width")
  ||(window.getComputedStyle
  ? (this.entBox.style.width||window.getComputedStyle(this.entBox, null)["width"])
  : (this.entBox.currentStyle
  ? this.entBox.currentStyle["width"]
  : this.entBox.style.width||0))
  ||"100%";this.entBox.style.height=this.entBox.getAttribute("height")
  ||(window.getComputedStyle
  ? (this.entBox.style.height||window.getComputedStyle(this.entBox, null)["height"])
  : (this.entBox.currentStyle
  ? this.entBox.currentStyle["height"]
  : this.entBox.style.height||0))
  ||"100%";this.entBox.style.cursor='default';this.entBox.onselectstart=function(){return false
  };this.obj=document.createElement("TABLE");this.obj.cellSpacing=0;this.obj.cellPadding=0;this.obj.style.width="100%";this.obj.style.tableLayout="fixed";this.obj.className="c_obj".substr(2);this.hdr=document.createElement("TABLE");this.hdr.style.border="1px solid gray";this.hdr.cellSpacing=0;this.hdr.cellPadding=0;if ((!_isOpera)||(_OperaRv >= 8.5))
  this.hdr.style.tableLayout="fixed";this.hdr.className="c_hdr".substr(2);this.hdr.width="100%";this.xHdr=document.createElement("TABLE");this.xHdr.className="xhdr";this.xHdr.cellPadding=0;this.xHdr.cellSpacing=0;this.xHdr.style.width='100%'
  var r = this.xHdr.insertRow(0)
  var c = r.insertCell(0);r.insertCell(1).innerHTML="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";r.childNodes[1].style.width='100%';r.childNodes[1].className='xhdr_last';c.appendChild(this.hdr)
  this.objBuf=document.createElement("DIV");this.objBuf.appendChild(this.obj);this.entCnt=document.createElement("TABLE");this.entCnt.insertRow(0).insertCell(0)
  this.entCnt.insertRow(1).insertCell(0);this.entCnt.cellPadding=0;this.entCnt.cellSpacing=0;this.entCnt.width="100%";this.entCnt.height="100%";this.entCnt.style.tableLayout="fixed";this.objBox=document.createElement("DIV");this.objBox.style.width="100%";this.objBox.style.height=this.entBox.style.height;this.objBox.style.overflow="auto";this.objBox.style.position="relative";this.objBox.appendChild(this.objBuf);this.objBox.className="objbox";this.hdrBox=document.createElement("DIV");this.hdrBox.style.width="100%"

  if (((_isOpera)&&(_OperaRv < 9)))
  this.hdrSizeA=25;else
  this.hdrSizeA=200;this.hdrBox.style.height=this.hdrSizeA+"px";if (_isIE)this.hdrBox.style.overflowX="hidden";else
  this.hdrBox.style.overflow="hidden";this.hdrBox.style.position="relative";this.hdrBox.appendChild(this.xHdr);this.preloadImagesAr=new Array(0)

  this.sortImg=document.createElement("IMG")
  this.sortImg.style.display="none";this.hdrBox.insertBefore(this.sortImg, this.xHdr)
  this.entCnt.rows[0].cells[0].style.verticalAlign="top";this.entCnt.rows[0].cells[0].appendChild(this.hdrBox);this.entCnt.rows[1].cells[0].appendChild(this.objBox);this.entBox.appendChild(this.entCnt);this.entBox.grid=this;this.objBox.grid=this;this.hdrBox.grid=this;this.obj.grid=this;this.hdr.grid=this;this.cellWidthPX=new Array(0);this.cellWidthPC=new Array(0);this.cellWidthType=this.entBox.cellwidthtype||"px";this.delim=this.entBox.delimiter||",";this._csvDelim=",";this.hdrLabels=[];this.columnIds=[];this.columnColor=[];this.cellType=dhtmlxArray();this.cellAlign=[];this.initCellWidth=[];this.fldSort=[];this.imgURL="./";this.isActive=false;this.isEditable=true;this.useImagesInHeader=false;this.pagingOn=false;this.rowsBufferOutSize=0;dhtmlxEvent(window, "unload", function(){try{self.destructor()}catch (e){}});this.setSkin=function(name){this.entBox.className="gridbox gridbox_"+name;this.enableAlterCss("ev_"+name, "odd_"+name, this.isTreeGrid())
  this._fixAlterCss()

  this._sizeFix=this._borderFix=0;switch (name){case "clear":
  this._topMb=document.createElement("DIV");this._topMb.className="topMumba";this._topMb.innerHTML="<img style='left:0px' src='"+this.imgURL
  +"skinC_top_left.gif'><img style='right:0px' src='"+this.imgURL+"skinC_top_right.gif'>";this.entBox.appendChild(this._topMb);this._botMb=document.createElement("DIV");this._botMb.className="bottomMumba";this._botMb.innerHTML="<img style='left:0px' src='"+this.imgURL
  +"skinD_bottom_left.gif'><img style='right:0px' src='"+this.imgURL+"skinD_bottom_right.gif'>";this.entBox.appendChild(this._botMb);this.entBox.style.position="relative";this._gcCorr=20;break;case "glassy_blue":
  this.forceDivInHeader=true;break;case "dhx_black":
  case "dhx_blue":
  case "modern":
  case "light":
  this._sizeFix=1;this.forceDivInHeader=true;break;case "xp":
  this.forceDivInHeader=true;this._srdh=22;if ((_isIE)&&(document.compatMode != "BackCompat"))
  this._srdh=25;this._sizeFix=1;break;case "mt":
  this._srdh=22;if ((_isIE)&&(document.compatMode != "BackCompat"))
  this._srdh=25;this._sizeFix=1;this._borderFix=(_isIE ? 1 : 0);break;case "gray":
  if ((_isIE)&&(document.compatMode != "BackCompat"))
  this._srdh=22;this._sizeFix=1;this._borderFix=(_isIE ? 1 : 0);break;case "sbdark":
  if (_isFF)this._gcCorr=1;break};if (_isIE&&this.hdr){var d = this.hdr.parentNode;d.removeChild(this.hdr);d.appendChild(this.hdr)};this.setSizes()};if (_isIE)this.preventIECaching(true);if (window.dhtmlDragAndDropObject)this.dragger=new dhtmlDragAndDropObject();this._doOnScroll=function(e, mode){this.callEvent("onScroll", [
  this.objBox.scrollLeft,
  this.objBox.scrollTop
  ]);this.doOnScroll(e, mode)};this.doOnScroll=function(e, mode){this.hdrBox.scrollLeft=this.objBox.scrollLeft;if (this.ftr)this.ftr.parentNode.scrollLeft=this.objBox.scrollLeft;if (mode)return;if (this._srnd){if (this._dLoadTimer)window.clearTimeout(this._dLoadTimer);this._dLoadTimer=window.setTimeout(function(){self._update_srnd_view()}, 100)}};this.attachToObject=function(obj){obj.appendChild(this.entBox)
  this.objBox.style.height=this.entBox.style.height};this.init=function(fl){if ((this.isTreeGrid())&&(!this._h2)){this._h2=new dhtmlxHierarchy();if ((this._fake)&&(!this._realfake))
  this._fake._h2=this._h2;this._tgc={imgURL: null
  }};if (!this._hstyles)return;this.editStop()
  
  this.lastClicked=null;this.resized=null;this.fldSorted=this.r_fldSorted=null;this.gridWidth=0;this.gridHeight=0;this.cellWidthPX=new Array(0);this.cellWidthPC=new Array(0);if (this.hdr.rows.length > 0){this.clearAll(true)};var hdrRow = this.hdr.insertRow(0);for (var i = 0;i < this.hdrLabels.length;i++){hdrRow.appendChild(document.createElement("TH"));hdrRow.childNodes[i]._cellIndex=i;hdrRow.childNodes[i].style.height="0px"};if (_isIE)hdrRow.style.position="absolute";else
  hdrRow.style.height='auto';var hdrRow = this.hdr.insertRow(_isKHTML ? 2 : 1);hdrRow._childIndexes=new Array();var col_ex = 0;for (var i = 0;i < this.hdrLabels.length;i++){hdrRow._childIndexes[i]=i-col_ex;if ((this.hdrLabels[i] == this.splitSign)&&(i != 0)){if (_isKHTML)hdrRow.insertCell(i-col_ex);hdrRow.cells[i-col_ex-1].colSpan=(hdrRow.cells[i-col_ex-1].colSpan||1)+1;hdrRow.childNodes[i-col_ex-1]._cellIndex++;col_ex++;hdrRow._childIndexes[i]=i-col_ex;continue};hdrRow.insertCell(i-col_ex);hdrRow.childNodes[i-col_ex]._cellIndex=i;hdrRow.childNodes[i-col_ex]._cellIndexS=i;this.setColumnLabel(i, this.hdrLabels[i])};if (col_ex == 0)hdrRow._childIndexes=null;this._cCount=this.hdrLabels.length;if (_isIE)window.setTimeout(function(){self.setSizes()}, 1);if (!this.obj.firstChild)this.obj.appendChild(document.createElement("TBODY"));var tar = this.obj.firstChild;if (!tar.firstChild){tar.appendChild(document.createElement("TR"));tar=tar.firstChild;if (_isIE)tar.style.position="absolute";else
  tar.style.height='auto';for (var i = 0;i < this.hdrLabels.length;i++){tar.appendChild(document.createElement("TH"));tar.childNodes[i].style.height="0px"}};this._c_order=null;if (this.multiLine != true)this.obj.className+=" row20px";this.sortImg.style.position="absolute";this.sortImg.style.display="none";this.sortImg.src=this.imgURL+"sort_desc.gif";this.sortImg.defLeft=0;this.entCnt.rows[0].style.display='' 

  if (this.noHeader){this.entCnt.rows[0].style.display='none'}else {this.noHeader=false
  };this.attachHeader();this.attachHeader(0, 0, "_aFoot");this.setSizes();if (fl)this.parseXML()
  this.obj.scrollTop=0

  if (this.dragAndDropOff)this.dragger.addDragLanding(this.entBox, this);if (this._initDrF)this._initD();if (this._init_point)this._init_point()};this.setSizes=function(fl){if ((!this.hdr.rows[0]))
  return;if (!this.entBox.offsetWidth){if (this._sizeTime)window.clearTimeout(this._sizeTime);this._sizeTime=window.setTimeout(function(){self.setSizes()
  }, 250);return};if (((_isFF)&&(this.entBox.style.height == "100%"))||(this._fixLater)){this.entBox.style.height=this.entBox.parentNode.clientHeight;this._fixLater=true};if (fl&&this.gridWidth == this.entBox.offsetWidth&&this.gridHeight == this.entBox.offsetHeight){return false
  }else if (fl){this.gridWidth=this.entBox.offsetWidth
  this.gridHeight=this.entBox.offsetHeight
  };if ((!this.hdrBox.offsetHeight)&&(this.hdrBox.offsetHeight > 0))
  this.entCnt.rows[0].cells[0].height=this.hdrBox.offsetHeight+"px";var gridWidth = parseInt(this.entBox.offsetWidth)-(this._gcCorr||0);var gridHeight = parseInt(this.entBox.offsetHeight)-(this._sizeFix||0);var _isVSroll = (this.objBox.scrollHeight > this.objBox.offsetHeight);if (((!this._ahgr)&&(_isVSroll))||((this._ahgrM)&&(this._ahgrM < this.objBox.scrollHeight)))
  gridWidth-=(this._scrFix||(_isFF ? 17 : 17));var len = this.hdr.rows[0].cells.length
  

  for (var i = 0;i < this._cCount;i++){if (this.cellWidthType == 'px'&&this.cellWidthPX.length < len){this.cellWidthPX[i]=this.initCellWidth[i]-this._wcorr}else if (this.cellWidthType == '%'&&this.cellWidthPC.length < len){this.cellWidthPC[i]=this.initCellWidth[i]};if (this.cellWidthType == '%'&&this.cellWidthPC.length != 0&&this.cellWidthPC[i]){this.cellWidthPX[i]=parseInt(gridWidth*this.cellWidthPC[i] / 100)}};var wcor = this.entBox.offsetWidth-this.entBox.clientWidth;var summ = 0;var fcols = new Array();for (var i = 0;i < this._cCount;i++)if ((this.initCellWidth[i] == "*")&&((!this._hrrar)||(!this._hrrar[i])))
  fcols[fcols.length]=i;else
  summ+=parseInt(this.cellWidthPX[i]);if (fcols.length){var ms = Math.floor((gridWidth-summ-wcor) / fcols.length);if (ms < 0)ms=1;for (var i = 0;i < fcols.length;i++){var min = (this._drsclmW ? this._drsclmW[fcols[i]] : 0);this.cellWidthPX[fcols[i]]=(min ? (min > ms ? min : ms) : ms)-this._wcorr;summ+=ms};if (!this._rseb){this._setAutoResize();this._rseb=true}};var summ = 0;for (var i = 0;i < this._cCount;i++)summ+=parseInt(this.cellWidthPX[i])

  if (_isOpera)summ-=1;this.chngCellWidth();if ((this._awdth)&&(this._awdth[0])){if (this.cellWidthType == '%'){this.cellWidthType="px";this.cellWidthPC=[]};var gs = (summ > this._awdth[1]
  ? this._awdth[1]
  : (summ < this._awdth[2]
  ? this._awdth[2]
  : summ))
  +(this._borderFix||0)*2;if (this._fake)for (var i = 0;i < this._fake._cCount;i++)gs+=parseInt(this._fake.cellWidthPX[i])
  this.entBox.style.width=gs+((_isVSroll&&(!this._ahgr||(this._ahgrM && this._ahgrM < this.objBox.scrollHeight) )) ? (_isFF ? 20 : 18) : 0)+"px";if (this._fake && !this._realfake)this._fake._correctSplit()};this.objBuf.style.width=summ+"px";if ((this.ftr)&&(!this._realfake))
  this.ftr.style.width=summ+"px";this.objBuf.childNodes[0].style.width=summ+"px";this.doOnScroll(0, 1);this.hdr.style.border="0px solid gray";var zheight = this.hdr.offsetHeight+(this._borderFix ? this._borderFix : 0);if (this.ftr)zheight+=this.ftr.offsetHeight;if (this._ahgr)if (this.objBox.scrollHeight){if (_isIE)var z2 = this.objBox.scrollHeight;else
  var z2 = this.objBox.childNodes[0].scrollHeight;var scrfix =
  this.parentGrid
  ? 1
  : ((this.objBox.offsetWidth < this.objBox.scrollWidth)
  ? (_isFF
  ? 20
  : 18)
  : 1);if (this._ahgrMA)z2=this.entBox.parentNode.offsetHeight-zheight-scrfix-(this._sizeFix ? this._sizeFix : 0)*2;if (((this._ahgrM)&&((this._ahgrF ? (z2+zheight+scrfix) : z2) > this._ahgrM)))
  gridHeight=this._ahgrM*1+(this._ahgrF ? 0 : (zheight+scrfix));else
  gridHeight=z2+zheight+scrfix;this.entBox.style.height=gridHeight+"px"};if (this.ftr)zheight-=this.ftr.offsetHeight;var aRow = this.entCnt.rows[1].cells[0].childNodes[0];if (!this.noHeader)aRow.style.top=(zheight-this.hdrBox.offsetHeight)+"px";if (this._topMb){this._topMb.style.top=(zheight||0)+"px";this._topMb.style.width=(gridWidth+20)+"px"};if (this._botMb){this._botMb.style.top=(gridHeight-3)+"px";this._botMb.style.width=(gridWidth+20)+"px"};aRow.style.height=Math.max((((gridHeight-zheight-1) < 0&&_isIE)
  ? 20
  : (gridHeight-zheight-1))
  -(this.ftr
  ? this.ftr.offsetHeight
  : 0)-(_isIE&&(document.compatMode == "BackCompat")&&this._fake?(this._sizeFix||0)*2:0),0)
  +"px";if (this.ftr&&this.entBox.offsetHeight > this.ftr.offsetHeight)this.entCnt.style.height=this.entBox.offsetHeight-this.ftr.offsetHeight+"px";if (this._srdh)this.doOnScroll()};this.chngCellWidth=function(){if ((_isOpera)&&(this.ftr))
  this.ftr.width=this.objBox.scrollWidth+"px";var l = this._cCount;for (var i = 0;i < l;i++){this.hdr.rows[0].cells[i].style.width=this.cellWidthPX[i]+"px";this.obj.rows[0].childNodes[i].style.width=this.cellWidthPX[i]+"px";if (this.ftr)this.ftr.rows[0].cells[i].style.width=this.cellWidthPX[i]+"px"}};this.setDelimiter=function(delim){this.delim=delim};this.setInitWidthsP=function(wp){this.cellWidthType="%";this.initCellWidth=wp.split(this.delim.replace(/px/gi, ""));this._setAutoResize()};this._setAutoResize=function(){var el = window;var self = this;if (el.addEventListener){if ((_isFF)&&(_FFrv < 1.8))
  el.addEventListener("resize", function(){if (!self.entBox)return;var z = self.entBox.style.width;self.entBox.style.width="1px";window.setTimeout(function(){self.entBox.style.width=z;self.setSizes();if (self._fake)self._fake._correctSplit()}, 10)}, false);else
  el.addEventListener("resize", function(){if (self.setSizes)self.setSizes();if (self._fake)self._fake._correctSplit()}, false)}else if (el.attachEvent)el.attachEvent("onresize", function(){if (self._resize_timer)window.clearTimeout(self._resize_timer);if (self.setSizes)self._resize_timer=window.setTimeout(function(){self.setSizes();if (self._fake)self._fake._correctSplit()}, 500)});this._setAutoResize=function(){}};this.setInitWidths=function(wp){this.cellWidthType="px";this.initCellWidth=wp.split(this.delim);if (_isFF){for (var i = 0;i < this.initCellWidth.length;i++)if (this.initCellWidth[i] != "*")this.initCellWidth[i]=parseInt(this.initCellWidth[i])-2}};this.enableMultiline=function(state){this.multiLine=convertStringToBoolean(state)};this.enableMultiselect=function(state){this.selMultiRows=convertStringToBoolean(state)};this.setImagePath=function(path){this.imgURL=path};this.setImagesPath=this.setImagePath;this.setIconPath=function(path){this.iconURL=path};this.setIconsPath=this.setIconPath;this.changeCursorState=function(ev){var el = ev.target||ev.srcElement;if (el.tagName != "TD")el=this.getFirstParentOfType(el, "TD")

  if ((el.tagName == "TD")&&(this._drsclmn)&&(!this._drsclmn[el._cellIndex]))
  return el.style.cursor="default";var check = (ev.layerX||0)+(((!_isIE)&&(ev.target.tagName == "DIV")) ? el.offsetLeft : 0);if ((el.offsetWidth-(ev.offsetX||(parseInt(this.getPosition(el, this.hdrBox))-check)*-1)) < (_isOpera?20:10)){el.style.cursor="E-resize"}else{el.style.cursor="default"};if (_isOpera)this.hdrBox.scrollLeft=this.objBox.scrollLeft};this.startColResize=function(ev){this.resized=null;var el = ev.target||ev.srcElement;if (el.tagName != "TD")el=this.getFirstParentOfType(el, "TD")
  var x = ev.clientX;var tabW = this.hdr.offsetWidth;var startW = parseInt(el.offsetWidth)

  if (el.tagName == "TD"&&el.style.cursor != "default"){if ((this._drsclmn)&&(!this._drsclmn[el._cellIndex]))
  return;this.entBox.onmousemove=function(e){this.grid.doColResize(e||window.event, el, startW, x, tabW)
  };document.body.onmouseup=function(){self.stopColResize()}}};this.stopColResize=function(){this.entBox.onmousemove="";document.body.onmouseup="";this.setSizes();this.doOnScroll(0, 1)
  this.callEvent("onResizeEnd", [this])};this.doColResize=function(ev, el, startW, x, tabW){el.style.cursor="E-resize";this.resized=el;var fcolW = startW+(ev.clientX-x);var wtabW = tabW+(ev.clientX-x)

  if (!(this.callEvent("onResize", [
  el._cellIndex,
  fcolW,
  this
  ])))
  return;if (_isIE)this.objBox.scrollLeft=this.hdrBox.scrollLeft;if (el.colSpan > 1){var a_sizes = new Array();for (var i = 0;i < el.colSpan;i++)a_sizes[i]=Math.round(fcolW*this.hdr.rows[0].childNodes[el._cellIndexS+i].offsetWidth/el.offsetWidth);for (var i = 0;i < el.colSpan;i++)this._setColumnSizeR(el._cellIndexS+i*1, a_sizes[i])}else
  this._setColumnSizeR(el._cellIndex, fcolW);this.doOnScroll(0, 1);this.objBuf.childNodes[0].style.width="";if (_isOpera || _isFF)this.setSizes()};this._setColumnSizeR=function(ind, fcolW){if (fcolW > ((this._drsclmW&&!this._notresize)? (this._drsclmW[ind]||10) : 10)){this.obj.rows[0].childNodes[ind].style.width=fcolW+"px";this.hdr.rows[0].childNodes[ind].style.width=fcolW+"px";if (this.ftr)this.ftr.rows[0].childNodes[ind].style.width=fcolW+"px";if (this.cellWidthType == 'px'){this.cellWidthPX[ind]=fcolW}else {var gridWidth = parseInt(this.entBox.offsetWidth);if (this.objBox.scrollHeight > this.objBox.offsetHeight)gridWidth-=(this._scrFix||(_isFF ? 17 : 17));var pcWidth = Math.round(fcolW / gridWidth*100)
  this.cellWidthPC[ind]=pcWidth};if (this.sortImg.style.display!="none")this.setSortImgPos()}};this.setSortImgState=function(state, ind, order, row){order=(order||"asc").toLowerCase();if (!convertStringToBoolean(state)){this.sortImg.style.display="none";this.fldSorted=null;return};if (order == "asc")this.sortImg.src=this.imgURL+"sort_asc.gif";else
  this.sortImg.src=this.imgURL+"sort_desc.gif";this.sortImg.style.display="";this.fldSorted=this.hdr.rows[0].childNodes[ind];var r = this.hdr.rows[row||1];for (var i = 0;i < r.childNodes.length;i++)if (r.childNodes[i]._cellIndex == ind)this.r_fldSorted=r.childNodes[i];this.setSortImgPos()};this.setSortImgPos=function(ind, mode, hRowInd, el){if (this._hrrar && this._hrrar[this.r_fldSorted?this.r_fldSorted._cellIndex:ind])return;if (!el){if (!ind)var el = this.r_fldSorted;else
  var el = this.hdr.rows[hRowInd||0].cells[ind]};if (el != null){var pos = this.getPosition(el, this.hdrBox)
  var wdth = el.offsetWidth;this.sortImg.style.left=Number(pos[0]+wdth-13)+"px";this.sortImg.defLeft=parseInt(this.sortImg.style.left)
  this.sortImg.style.top=Number(pos[1]+5)+"px";if ((!this.useImagesInHeader)&&(!mode))
  this.sortImg.style.display="inline";this.sortImg.style.left=this.sortImg.defLeft+"px"}};this.setActive=function(fl){if (arguments.length == 0)var fl = true;if (fl == true){if (globalActiveDHTMLGridObject&&(globalActiveDHTMLGridObject != this))
  globalActiveDHTMLGridObject.editStop();globalActiveDHTMLGridObject=this;this.isActive=true}else {this.isActive=false}};this._doClick=function(ev){var selMethod = 0;var el = this.getFirstParentOfType(_isIE ? ev.srcElement : ev.target, "TD");if (!el)return;var fl = true;if (this.markedCells){var markMethod = 0;if (ev.shiftKey||ev.metaKey){markMethod=1};if (ev.ctrlKey){markMethod=2};this.doMark(el, markMethod);return true};if (this.selMultiRows != false){if (ev.shiftKey&&this.row != null){selMethod=1};if (ev.ctrlKey||ev.metaKey){selMethod=2}};this.doClick(el, fl, selMethod)
  };this._doContClick=function(ev){var el = this.getFirstParentOfType(_isIE ? ev.srcElement : ev.target, "TD");if ((!el)||( typeof (el.parentNode.idd) == "undefined"))
  return true;if (ev.button == 2||(_isMacOS&&ev.ctrlKey)){if (!this.callEvent("onRightClick", [
  el.parentNode.idd,
  el._cellIndex,
  ev
  ])){var z = function(e){document.body.oncontextmenu=Function("return true;");(e||event).cancelBubble=true;return false};if (_isIE)ev.srcElement.oncontextmenu=z;else if (!_isMacOS)document.body.oncontextmenu=z;return false};if (this._ctmndx){if (!(this.callEvent("onBeforeContextMenu", [
  el.parentNode.idd,
  el._cellIndex,
  this
  ])))
  return true;if (_isIE)ev.srcElement.oncontextmenu=function(){event.cancelBubble=true;return false};if (this._ctmndx.showContextMenu){var dEl0=window.document.documentElement;var dEl1=window.document.body;var corrector = new Array((dEl0.scrollLeft||dEl1.scrollLeft),(dEl0.scrollTop||dEl1.scrollTop));if (_isIE){var x= ev.clientX+corrector[0];var y = ev.clientY-corrector[1]}else {var x= ev.pageX;var y = ev.pageY};this._ctmndx.showContextMenu(ev.clientX-1,ev.clientY-1)
  this.contextID=this._ctmndx.contextMenuZoneId=el.parentNode.idd+"_"+el._cellIndex;this._ctmndx._skip_hide=true}else {el.contextMenuId=el.parentNode.idd+"_"+el._cellIndex;el.contextMenu=this._ctmndx;el.a=this._ctmndx._contextStart;el.a(el, ev);el.a=null}}}else if (this._ctmndx){if (this._ctmndx.hideContextMenu)this._ctmndx.hideContextMenu()
  else
  this._ctmndx._contextEnd()};return true};this.doClick=function(el, fl, selMethod, show){var psid = this.row ? this.row.idd : 0;this.setActive(true);if (!selMethod)selMethod=0;if (this.cell != null)this.cell.className=this.cell.className.replace(/cellselected/g, "");if (el.tagName == "TD"){if (this.checkEvent("onSelectStateChanged"))
  var initial = this.getSelectedId();var prow = this.row;if (selMethod == 1){var elRowIndex = this.rowsCol._dhx_find(el.parentNode)
  var lcRowIndex = this.rowsCol._dhx_find(this.lastClicked)

  if (elRowIndex > lcRowIndex){var strt = lcRowIndex;var end = elRowIndex}else {var strt = elRowIndex;var end = lcRowIndex};for (var i = 0;i < this.rowsCol.length;i++)if ((i >= strt&&i <= end)){if (this.rowsCol[i]&&(!this.rowsCol[i]._sRow)){if (this.rowsCol[i].className.indexOf("rowselected")== -1&&this.callEvent("onBeforeSelect", [
  this.rowsCol[i].idd,
  psid
  ])){this.rowsCol[i].className+=" rowselected";this.selectedRows[this.selectedRows.length]=this.rowsCol[i]
  }}else {this.clearSelection();return this.doClick(el, fl, 0, show)}}}else if (selMethod == 2){if (el.parentNode.className.indexOf("rowselected")!= -1){el.parentNode.className=el.parentNode.className.replace(/rowselected/g, "");this.selectedRows._dhx_removeAt(this.selectedRows._dhx_find(el.parentNode))
  var skipRowSelection = true}};this.editStop()
  if (typeof (el.parentNode.idd)== "undefined")
  return true;if ((!skipRowSelection)&&(!el.parentNode._sRow)){if (this.callEvent("onBeforeSelect", [
  el.parentNode.idd,
  psid
  ])){if (selMethod == 0)this.clearSelection();this.cell=el;if ((prow == el.parentNode)&&(this._chRRS))
  fl=false;this.row=el.parentNode;this.row.className+=" rowselected"

  if (this.selectedRows._dhx_find(this.row)== -1)
  this.selectedRows[this.selectedRows.length]=this.row}};if (this.cell && this.cell.parentNode.className.indexOf("rowselected")!= -1)
  this.cell.className=this.cell.className.replace(/cellselected/g, "")+" cellselected";if (selMethod != 1)if (!this.row)return;this.lastClicked=el.parentNode;var rid = this.row.idd;var cid = this.cell;if (fl&& typeof (rid)!= "undefined" && cid)
  self.onRowSelectTime=setTimeout(function(){self.callEvent("onRowSelect", [
  rid,
  cid._cellIndex
  ])}, 100)

  if (this.checkEvent("onSelectStateChanged")){var afinal = this.getSelectedId();if (initial != afinal)this.callEvent("onSelectStateChanged", [afinal,initial])}};this.isActive=true;if (show !== false && this.cell && this.cell.parentNode.idd)this.moveToVisible(this.cell)
  };this.selectAll=function(){this.clearSelection();this.selectedRows=dhtmlxArray([].concat(this.rowsCol));for (var i = this.rowsCol.length-1;i >= 0;i--){if (this.rowsCol[i]._cntr)this.selectedRows.splice(i, 1);else
  this.rowsCol[i].className+=" rowselected"};if (this.selectedRows.length){this.row=this.selectedRows[0];this.cell=this.row.cells[0]};if ((this._fake)&&(!this._realfake))
  this._fake.selectAll()};this.selectCell=function(r, cInd, fl, preserve, edit, show){if (!fl)fl=false;if (typeof (r)!= "object")
  r=this.render_row(r)
  if (!r || r==-1)return null;var c = r.childNodes[cInd];if (!c)c=r.childNodes[0];if (preserve)this.doClick(c, fl, 3, show)
  else
  this.doClick(c, fl, 0, show)

  if (edit)this.editCell()};this.moveToVisible=function(cell_obj, onlyVScroll){if (!cell_obj.offsetHeight){var h=this.rowsBuffer._dhx_find(cell_obj.parentNode)*this._srdh;return this.objBox.scrollTop=h};try{var distance = cell_obj.offsetLeft+cell_obj.offsetWidth+20;var scrollLeft = 0;if (distance > (this.objBox.offsetWidth+this.objBox.scrollLeft)){if (cell_obj.offsetLeft > this.objBox.scrollLeft)scrollLeft=cell_obj.offsetLeft-5
  }else if (cell_obj.offsetLeft < this.objBox.scrollLeft){distance-=cell_obj.offsetWidth*2/3;if (distance < this.objBox.scrollLeft)scrollLeft=cell_obj.offsetLeft-5
  };if ((scrollLeft)&&(!onlyVScroll))
  this.objBox.scrollLeft=scrollLeft;var distance = cell_obj.offsetTop+cell_obj.offsetHeight+20;if (distance > (this.objBox.offsetHeight+this.objBox.scrollTop)){var scrollTop = distance-this.objBox.offsetHeight}else if (cell_obj.offsetTop < this.objBox.scrollTop){var scrollTop = cell_obj.offsetTop-5
  };if (scrollTop)this.objBox.scrollTop=scrollTop}catch (er){}};this.editCell=function(){if (this.editor&&this.cell == this.editor.cell)return;this.editStop();if ((this.isEditable != true)||(!this.cell))
  return false;var c = this.cell;if (c.parentNode._locked)return false;this.editor=this.cells4(c);if (this.editor != null){if (this.editor.isDisabled()){this.editor=null;return false};if (this.callEvent("onEditCell", [
  0,
  this.row.idd,
  this.cell._cellIndex
  ])!= false&&this.editor.edit){this._Opera_stop=(new Date).valueOf();c.className+=" editable";this.editor.edit();this.callEvent("onEditCell", [
  1,
  this.row.idd,
  this.cell._cellIndex
  ])
  }else {this.editor=null}}};this.editStop=function(mode){if (_isOpera)if (this._Opera_stop){if ((this._Opera_stop*1+50)> (new Date).valueOf())
  return;this._Opera_stop=null};if (this.editor&&this.editor != null){this.editor.cell.className=this.editor.cell.className.replace("editable", "");if (mode){var t = this.editor.val;this.editor.detach();this.editor.setValue(t);this.editor=null;return};if (this.editor.detach())
  this.cell.wasChanged=true;var g = this.editor;this.editor=null;var z = this.callEvent("onEditCell", [
  2,
  this.row.idd,
  this.cell._cellIndex,
  g.getValue(),
  g.val
  ]);if (( typeof (z)== "string")||( typeof (z) == "number"))
  g[g.setImage ? "setLabel" : "setValue"](z);else if (!z)g[g.setImage ? "setLabel" : "setValue"](g.val)}};this._nextRowCell=function(row, dir, pos){row=this._nextRow(this.rowsCol._dhx_find(row), dir);if (!row)return null;return row.childNodes[row._childIndexes ? row._childIndexes[pos] : pos]};this._getNextCell=function(acell, dir, i){acell=acell||this.cell;var arow = acell.parentNode;if (this._tabOrder){i=this._tabOrder[acell._cellIndex];if (typeof i != "undefined")if (i < 0)acell=this._nextRowCell(arow, dir, Math.abs(i)-1);else
  acell=arow.childNodes[i]}else {var i = acell._cellIndex+dir;if (i >= 0&&i < this._cCount){if (arow._childIndexes)i=arow._childIndexes[acell._cellIndex]+dir;acell=arow.childNodes[i]}else {acell=this._nextRowCell(arow, dir, (dir == 1 ? 0 : (this._cCount-1)))}};if (!acell){if ((dir == 1)&&this.tabEnd){this.tabEnd.focus();this.tabEnd.focus()};if ((dir == -1)&&this.tabStart){this.tabStart.focus();this.tabStart.focus()};return null};if (acell.style.display != "none"
  &&(!this.smartTabOrder||!this.cells(acell.parentNode.idd, acell._cellIndex).isDisabled()))
  return acell;return this._getNextCell(acell, dir)};this._nextRow=function(ind, dir){var r = this.render_row(ind+dir);if (!r || r==-1)return null;if (r&&r.style.display == "none")return this._nextRow(ind+dir, dir);return r};this.scrollPage=function(dir){var master = this._realfake?this._fake:this;var new_ind = Math.floor((master._r_select||this.getRowIndex(this.row.idd)||0)+(dir)*this.objBox.offsetHeight / (this._srdh||20));if (new_ind < 0)new_ind=0;if (new_ind >= this.rowsBuffer.length)new_ind=this.rowsBuffer.length-1;if (this._srnd && !this.rowsBuffer[new_ind]){this.objBox.scrollTop+=Math.floor((dir)*this.objBox.offsetHeight / (this._srdh||20))*(this._srdh||20);master._r_select=new_ind}else {this.selectCell(new_ind, this.cell._cellIndex, true, false,false,(this.multiLine || this._srnd));if (!this.multiLine && !this._srnd)this.objBox.scrollTop=this.getRowById(this.getRowId(new_ind)).offsetTop;master._r_select=null}};this.doKey=function(ev){if (!ev)return true;if ((ev.target||ev.srcElement).value !== window.undefined){var zx = (ev.target||ev.srcElement);if ((!zx.parentNode)||(zx.parentNode.className.indexOf("editable") == -1))
  return true};if ((globalActiveDHTMLGridObject)&&(this != globalActiveDHTMLGridObject))
  return globalActiveDHTMLGridObject.doKey(ev);if (this.isActive == false){return true};if (this._htkebl)return true;if (!this.callEvent("onKeyPress", [
  ev.keyCode,
  ev.ctrlKey,
  ev.shiftKey,
  ev
  ]))
  return false;var code = "k"+ev.keyCode+"_"+(ev.ctrlKey ? 1 : 0)+"_"+(ev.shiftKey ? 1 : 0);if (this.cell){if (this._key_events[code]){if (false === this._key_events[code].call(this))
  return true;if (ev.preventDefault)ev.preventDefault();ev.cancelBubble=true;return false};if (this._key_events["k_other"])this._key_events.k_other.call(this, ev)};return true};this.getRow=function(cell){if (!cell)cell=window.event.srcElement;if (cell.tagName != 'TD')cell=cell.parentElement;r=cell.parentElement;if (this.cellType[cell._cellIndex] == 'lk')eval(this.onLink+"('"+this.getRowId(r.rowIndex)+"',"+cell._cellIndex+")");this.selectCell(r, cell._cellIndex, true)
  };this.selectRow=function(r, fl, preserve, show){if (typeof (r)!= 'object')
  r=this.render_row(r);this.selectCell(r, 0, fl, preserve, false, show)
  };this.wasDblClicked=function(ev){var el = this.getFirstParentOfType(_isIE ? ev.srcElement : ev.target, "TD");if (el){var rowId = el.parentNode.idd;return this.callEvent("onRowDblClicked", [
  rowId,
  el._cellIndex
  ])}};this._onHeaderClick=function(e, el){var that = this.grid;el=el||that.getFirstParentOfType(_isIE ? event.srcElement : e.target, "TD");if (this.grid.resized == null){if (!(this.grid.callEvent("onHeaderClick", [
  el._cellIndexS,
  (e||window.event)])))
  return false;that.sortField(el._cellIndexS, false, el)

  }};this.deleteSelectedRows=function(){var num = this.selectedRows.length 

  if (num == 0)return;var tmpAr = this.selectedRows;this.selectedRows=dhtmlxArray()
  for (var i = num-1;i >= 0;i--){var node = tmpAr[i]

  if (!this.deleteRow(node.idd, node)){this.selectedRows[this.selectedRows.length]=node}else {if (node == this.row){var ind = i}}};if (ind){try{if (ind+1 > this.rowsCol.length)ind--;this.selectCell(ind, 0, true)
  }catch (er){this.row=null
  this.cell=null
  }}};this.getSelectedRowId=function(){var selAr = new Array(0);var uni = {};for (var i = 0;i < this.selectedRows.length;i++){var id = this.selectedRows[i].idd;if (uni[id])continue;selAr[selAr.length]=id;uni[id]=true};if (selAr.length == 0)return null;else
  return selAr.join(this.delim)};this.getSelectedCellIndex=function(){if (this.cell != null)return this.cell._cellIndex;else
  return -1};this.getColWidth=function(ind){return parseInt(this.cellWidthPX[ind])+((_isFF) ? 2 : 0)};this.setColWidth=function(ind, value){if (this.cellWidthType == 'px')this.cellWidthPX[ind]=parseInt(value)-+((_isFF) ? 2 : 0);else
  this.cellWidthPC[ind]=parseInt(value);this.setSizes()};this.getRowIndex=function(row_id){for (var i = 0;i < this.rowsBuffer.length;i++)if (this.rowsBuffer[i]&&this.rowsBuffer[i].idd == row_id)return i};this.getRowId=function(ind){return this.rowsBuffer[ind] ? this.rowsBuffer[ind].idd : this.undefined};this.setRowId=function(ind, row_id){this.changeRowId(this.getRowId(ind), row_id)
  };this.changeRowId=function(oldRowId, newRowId){if (oldRowId == newRowId)return;var row = this.rowsAr[oldRowId]
  row.idd=newRowId;if (this.UserData[oldRowId]){this.UserData[newRowId]=this.UserData[oldRowId]
  this.UserData[oldRowId]=null};if (this._h2&&this._h2.get[oldRowId]){this._h2.get[newRowId]=this._h2.get[oldRowId];this._h2.get[newRowId].id=newRowId;delete this._h2.get[oldRowId]};this.rowsAr[oldRowId]=null;this.rowsAr[newRowId]=row;for (var i = 0;i < row.childNodes.length;i++)if (row.childNodes[i]._code)row.childNodes[i]._code=this._compileSCL(row.childNodes[i]._val, row.childNodes[i]);this.callEvent("onRowIdChange",[oldRowId,newRowId])};this.setColumnIds=function(ids){this.columnIds=ids.split(this.delim)
  };this.setColumnId=function(ind, id){this.columnIds[ind]=id};this.getColIndexById=function(id){for (var i = 0;i < this.columnIds.length;i++)if (this.columnIds[i] == id)return i};this.getColumnId=function(cin){return this.columnIds[cin]};this.getColumnLabel=function(cin, ind){var z = this.hdr.rows[(ind||0)+1];for (var i=0;i<z.cells.length;i++)if (z.cells[i]._cellIndexS==cin)return(_isIE ? z.cells[i].innerText : z.cells[i].textContent);return ""};this.setRowTextBold=function(row_id){var r=this.getRowById(row_id)
  if (r)r.style.fontWeight="bold"};this.setRowTextStyle=function(row_id, styleString){var r = this.getRowById(row_id)
  if (!r)return;for (var i = 0;i < r.childNodes.length;i++){var pfix = "";if (_isIE)r.childNodes[i].style.cssText=pfix+"width:"+r.childNodes[i].style.width+";"+styleString;else
  r.childNodes[i].style.cssText=pfix+"width:"+r.childNodes[i].style.width+";"+styleString}};this.setRowColor=function(row_id, color){var r = this.getRowById(row_id)

  for (var i = 0;i < r.childNodes.length;i++)r.childNodes[i].bgColor=color};this.setCellTextStyle=function(row_id, ind, styleString){var r = this.getRowById(row_id)

  if (!r)return;var cell = r.childNodes[r._childIndexes ? r._childIndexes[ind] : ind];if (!cell)return;var pfix = "";if (_isIE)cell.style.cssText=pfix+"width:"+cell.style.width+";"+styleString;else
  cell.style.cssText=pfix+"width:"+cell.style.width+";"+styleString};this.setRowTextNormal=function(row_id){var r=this.getRowById(row_id);if (r)r.style.fontWeight="normal"};this.doesRowExist=function(row_id){if (this.getRowById(row_id)!= null)
  return true
  else
  return false
  };this.getColumnsNum=function(){return this._cCount};this.moveRowUp=function(row_id){var r = this.getRowById(row_id)

  if (this.isTreeGrid())
  return this.moveRowUDTG(row_id, -1);var rInd = this.rowsCol._dhx_find(r)
  if ((r.previousSibling)&&(rInd != 0)){r.parentNode.insertBefore(r, r.previousSibling)
  this.rowsCol._dhx_swapItems(rInd, rInd-1)
  this.setSizes();var bInd=this.rowsBuffer._dhx_find(r);this.rowsBuffer._dhx_swapItems(bInd,bInd-1);if (this._cssEven)this._fixAlterCss(rInd-1)}};this.moveRowDown=function(row_id){var r = this.getRowById(row_id)

  if (this.isTreeGrid())
  return this.moveRowUDTG(row_id, 1);var rInd = this.rowsCol._dhx_find(r);if (r.nextSibling){this.rowsCol._dhx_swapItems(rInd, rInd+1)

  if (r.nextSibling.nextSibling)r.parentNode.insertBefore(r, r.nextSibling.nextSibling)
  else
  r.parentNode.appendChild(r)
  this.setSizes();var bInd=this.rowsBuffer._dhx_find(r);this.rowsBuffer._dhx_swapItems(bInd,bInd+1);if (this._cssEven)this._fixAlterCss(rInd)}};this.getCombo=function(col_ind){if (!this.combos[col_ind]){this.combos[col_ind]=new dhtmlXGridComboObject()};return this.combos[col_ind]};this.setUserData=function(row_id, name, value){try{if (row_id == "")row_id="gridglobaluserdata";if (!this.UserData[row_id])this.UserData[row_id]=new Hashtable()
  this.UserData[row_id].put(name, value)
  }catch (er){alert("UserData Error:"+er.description)
  }};this.getUserData=function(row_id, name){this.getRowById(row_id);if (row_id == "")row_id="gridglobaluserdata";var z = this.UserData[row_id];return(z ? z.get(name) : "")};this.setEditable=function(fl){this.isEditable=convertStringToBoolean(fl)};this.selectRowById=function(row_id, multiFL, show, call){if (!call)call=false;this.selectCell(this.getRowById(row_id), 0, call, multiFL, false, show)};this.clearSelection=function(){this.editStop()

  for (var i = 0;i < this.selectedRows.length;i++){var r = this.rowsAr[this.selectedRows[i].idd];if (r)r.className=r.className.replace(/rowselected/g, "")};this.selectedRows=dhtmlxArray()
  this.row=null;if (this.cell != null){this.cell.className=this.cell.className.replace(/cellselected/g, "");this.cell=null}};this.copyRowContent=function(from_row_id, to_row_id){var frRow = this.getRowById(from_row_id)

  if (!this.isTreeGrid())
  for (var i = 0;i < frRow.cells.length;i++){this.cells(to_row_id, i).setValue(this.cells(from_row_id, i).getValue())
  }else
  this._copyTreeGridRowContent(frRow, from_row_id, to_row_id);if (!_isIE)this.getRowById(from_row_id).cells[0].height=frRow.cells[0].offsetHeight
  };this.setColumnLabel=function(c, label, ind){var z = this.hdr.rows[ind||1];var col = (z._childIndexes ? z._childIndexes[c] : c);if (!z.cells[col])return;if (!this.useImagesInHeader){var hdrHTML = "<div class='hdrcell'>"

  if (label.indexOf('img:[')!= -1){var imUrl = label.replace(/.*\[([^>]+)\].*/, "$1");label=label.substr(label.indexOf("]")+1, label.length)
  hdrHTML+="<img width='18px' height='18px' align='absmiddle' src='"+imUrl+"' hspace='2'>"
  };hdrHTML+=label;hdrHTML+="</div>";z.cells[col].innerHTML=hdrHTML;if (this._hstyles[col])z.cells[col].style.cssText=this._hstyles[col]}else {z.cells[col].style.textAlign="left";z.cells[col].innerHTML="<img src='"+this.imgURL+""+label+"' onerror='this.src = \""+this.imgURL
  +"imageloaderror.gif\"'>";var a = new Image();a.src=this.imgURL+""+label.replace(/(\.[a-z]+)/, ".desc$1");this.preloadImagesAr[this.preloadImagesAr.length]=a;var b = new Image();b.src=this.imgURL+""+label.replace(/(\.[a-z]+)/, ".asc$1");this.preloadImagesAr[this.preloadImagesAr.length]=b};if ((label||"").indexOf("#") != -1){var t = label.match(/(^|{)#([^}]+)(}|$)/);if (t){var tn = "_in_header_"+t[2];if (this[tn])this[tn]((this.forceDivInHeader ? z.cells[col].firstChild : z.cells[col]), col, label.split(t[0]))}}};this.clearAll=function(header){if (!this.obj.rows[0])return;if (this._h2){this._h2=new dhtmlxHierarchy();if (this._fake){if (this._realfake)this._h2=this._fake._h2;else
  this._fake._h2=this._h2}};this.limit=this._limitC=0;this.editStop(true);if (this._dLoadTimer)window.clearTimeout(this._dLoadTimer);if (this._dload){this.objBox.scrollTop=0;this.limit=this._limitC||0;this._initDrF=true};var len = this.rowsCol.length;len=this.obj.rows.length;for (var i = len-1;i > 0;i--){var t_r = this.obj.rows[i];t_r.parentNode.removeChild(t_r)};if (header){this._master_row=null;this.obj.rows[0].parentNode.removeChild(this.obj.rows[0]);for (var i = this.hdr.rows.length-1;i >= 0;i--){var t_r = this.hdr.rows[i];t_r.parentNode.removeChild(t_r)};if (this.ftr){this.ftr.parentNode.removeChild(this.ftr);this.ftr=null};this._aHead=this.ftr=this._aFoot=null;this._hrrar=[];this.columnIds=[];this.combos=[]};this.row=null;this.cell=null;this.rowsCol=dhtmlxArray()
  this.rowsAr=[];this._RaSeCol=[];this.rowsBuffer=dhtmlxArray()
  this.UserData=[]
  this.selectedRows=dhtmlxArray();if (this.pagingOn || this._srnd)this.xmlFileUrl="";if (this.pagingOn)this.changePage(1);if (this._contextCallTimer)window.clearTimeout(this._contextCallTimer);if (this._sst)this.enableStableSorting(true);this._fillers=this.undefined;this.setSortImgState(false);this.setSizes();this.callEvent("onClearAll", [])};this.sortField=function(ind, repeatFl, r_el){if (this.getRowsNum()== 0)
  return false;var el = this.hdr.rows[0].cells[ind];if (!el)return;if (el.tagName == "TH"&&(this.fldSort.length-1)>= el._cellIndex
  &&this.fldSort[el._cellIndex] != 'na'){var data=this.getSortingState();var sortType= ( data[0]==ind && data[1]=="asc" ) ? "des" : "asc";if (!this.callEvent("onBeforeSorting", [
  ind,
  this.fldSort[ind],
  sortType
  ]))
  return;this.sortImg.src=this.imgURL+"sort_"+(sortType == "asc" ? "asc" : "desc")+".gif";if (this.useImagesInHeader){var cel = this.hdr.rows[1].cells[el._cellIndex].firstChild;if (this.fldSorted != null){var celT = this.hdr.rows[1].cells[this.fldSorted._cellIndex].firstChild;celT.src=celT.src.replace(/\.[ascde]+\./, ".")};cel.src=cel.src.replace(/(\.[a-z]+)/, "."+sortType+"$1")
  };this.sortRows(el._cellIndex, this.fldSort[el._cellIndex], sortType)
  this.fldSorted=el;this.r_fldSorted=r_el;var c = this.hdr.rows[1];var c = r_el.parentNode;var real_el = c._childIndexes ? c._childIndexes[el._cellIndex] : el._cellIndex;this.setSortImgPos(false, false, false, r_el)}};this.enableHeaderImages=function(fl){this.useImagesInHeader=fl};this.setHeader=function(hdrStr, splitSign, styles){if (typeof (hdrStr)!= "object")
  var arLab = this._eSplit(hdrStr);else
  arLab=[].concat(hdrStr);var arWdth = new Array(0);var arTyp = new dhtmlxArray(0);var arAlg = new Array(0);var arVAlg = new Array(0);var arSrt = new Array(0);for (var i = 0;i < arLab.length;i++){arWdth[arWdth.length]=Math.round(100 / arLab.length);arTyp[arTyp.length]="ed";arAlg[arAlg.length]="left";arVAlg[arVAlg.length]="";arSrt[arSrt.length]="na"};this.splitSign=splitSign||"#cspan";this.hdrLabels=arLab;this.cellWidth=arWdth;this.cellType=arTyp;this.cellAlign=arAlg;this.cellVAlign=arVAlg;this.fldSort=arSrt;this._hstyles=styles||[]};this._eSplit=function(str){if (![].push)return str.split(this.delim);var a = "r"+(new Date()).valueOf();var z = this.delim.replace(/([\|\+\*\^])/g, "\\$1")
  return(str||"").replace(RegExp(z, "g"), a).replace(RegExp("\\\\"+a, "g"), this.delim).split(a)};this.getColType=function(cInd){return this.cellType[cInd]};this.getColTypeById=function(cID){return this.cellType[this.getColIndexById(cID)]};this.setColTypes=function(typeStr){this.cellType=dhtmlxArray(typeStr.split(this.delim));this._strangeParams=new Array();for (var i = 0;i < this.cellType.length;i++){if ((this.cellType[i].indexOf("[")!= -1)){var z = this.cellType[i].split(/[\[\]]+/g);this.cellType[i]=z[0];this.defVal[i]=z[1];if (z[1].indexOf("=")== 0){this.cellType[i]="math";this._strangeParams[i]=z[0]}};if (!window["eXcell_"+this.cellType[i]])dhtmlxError.throwError("Configuration","Incorrect cell type: "+this.cellType[i],[this,this.cellType[i]])}};this.setColSorting=function(sortStr){this.fldSort=sortStr.split(this.delim)

  };this.setColAlign=function(alStr){this.cellAlign=alStr.split(this.delim)
  };this.setColVAlign=function(valStr){this.cellVAlign=valStr.split(this.delim)
  };this.setNoHeader=function(fl){this.noHeader=convertStringToBoolean(fl)};this.showRow=function(rowID){this.getRowById(rowID)
  if (this.pagingOn){var newPage=Math.floor(this.getRowIndex(rowID) / this.rowsBufferOutSize)+1;if (newPage!=this.currentPage)this.changePage(newPage)};if (this._h2)this.openItem(this._h2.get[rowID].parent.id);var c = this.getRowById(rowID).childNodes[0];while (c&&c.style.display == "none")c=c.nextSibling;if (c)this.moveToVisible(c, true)
  };this.setStyle=function(ss_header, ss_grid, ss_selCell, ss_selRow){this.ssModifier=[
  ss_header,
  ss_grid,
  ss_selCell,
  ss_selCell,
  ss_selRow
  ];var prefs = ["#"+this.entBox.id+" table.hdr td", "#"+this.entBox.id+" table.obj td",
  "#"+this.entBox.id+" table.obj tr.rowselected td.cellselected",
  "#"+this.entBox.id+" table.obj td.cellselected", "#"+this.entBox.id+" table.obj tr.rowselected td"];for (var i = 0;i < prefs.length;i++)if (this.ssModifier[i]){if (_isIE)document.styleSheets[0].addRule(prefs[i], this.ssModifier[i]);else
  document.styleSheets[0].insertRule(prefs[i]+" {"+this.ssModifier[i]+" };", 0)}};this.setColumnColor=function(clr){this.columnColor=clr.split(this.delim)
  };this.enableAlterCss=function(cssE, cssU, perLevel, levelUnique){if (cssE||cssU)this.attachEvent("onGridReconstructed",function(){if (!this._cssSP){this._fixAlterCss();if (this._fake)this._fake._fixAlterCss()}});this._cssSP=perLevel;this._cssSU=levelUnique;this._cssEven=cssE;this._cssUnEven=cssU};this._fixAlterCss=function(ind){if (this._cssSP&&this._h2)return this._fixAlterCssTGR(ind);if (!this._cssEven && !this._cssUnEven)return;ind=ind||0;var j = ind;for (var i = ind;i < this.rowsCol.length;i++){if (!this.rowsCol[i])continue;if (this.rowsCol[i].style.display != "none"){if (this.rowsCol[i].className.indexOf("rowselected")!= -1){if (j%2 == 1)this.rowsCol[i].className=this._cssUnEven+" rowselected "+(this.rowsCol[i]._css||"");else
  this.rowsCol[i].className=this._cssEven+" rowselected "+(this.rowsCol[i]._css||"")}else {if (j%2 == 1)this.rowsCol[i].className=this._cssUnEven+" "+(this.rowsCol[i]._css||"");else
  this.rowsCol[i].className=this._cssEven+" "+(this.rowsCol[i]._css||"")};j++}}};this.getPosition=function(oNode, pNode){if (!pNode)var pNode = document.body

  var oCurrentNode = oNode;var iLeft = 0;var iTop = 0;while ((oCurrentNode)&&(oCurrentNode != pNode)){iLeft+=oCurrentNode.offsetLeft-oCurrentNode.scrollLeft;iTop+=oCurrentNode.offsetTop-oCurrentNode.scrollTop;oCurrentNode=oCurrentNode.offsetParent};if (pNode == document.body){if (_isIE){if (document.documentElement.scrollTop)iTop+=document.documentElement.scrollTop;if (document.documentElement.scrollLeft)iLeft+=document.documentElement.scrollLeft}else if (!_isFF){iLeft+=document.body.offsetLeft;iTop+=document.body.offsetTop}};return new Array(iLeft, iTop)};this.getFirstParentOfType=function(obj, tag){while (obj&&obj.tagName != tag&&obj.tagName != "BODY"){obj=obj.parentNode};return obj};this.objBox.onscroll=function(){this.grid._doOnScroll()};if ((!_isOpera)||(_OperaRv > 8.5)){this.hdr.onmousemove=function(e){this.grid.changeCursorState(e||window.event)};this.hdr.onmousedown=function(e){return this.grid.startColResize(e||window.event)}};this.obj.onmousemove=this._drawTooltip;this.obj.onclick=function(e){this.grid._doClick(e||window.event);if (this.grid._sclE)this.grid.editCell(e||window.event);(e||event).cancelBubble=true};if (_isMacOS){this.entBox.oncontextmenu=function(e){return this.grid._doContClick(e||window.event)}}else
  this.entBox.onmousedown=function(e){return this.grid._doContClick(e||window.event)};this.obj.ondblclick=function(e){if (!this.grid.wasDblClicked(e||window.event)) 
  return false;if (this.grid._dclE)this.grid.editCell(e||window.event);(e||event).cancelBubble=true;if (_isOpera)return false};this.hdr.onclick=this._onHeaderClick;this.sortImg.onclick=function(){self._onHeaderClick.apply({grid: self
  }, [
  null,
  self.r_fldSorted
  ])};this.hdr.ondblclick=this._onHeaderDblClick;if (!document.body._dhtmlxgrid_onkeydown){dhtmlxEvent(document, _isOpera?"keypress":"keydown",function(e){if (globalActiveDHTMLGridObject)return globalActiveDHTMLGridObject.doKey(e||window.event)});document.body._dhtmlxgrid_onkeydown=true};dhtmlxEvent(document.body, "click", function(){if (self.editStop)self.editStop()});this.entBox.onbeforeactivate=function(){this._still_active=null;this.grid.setActive();event.cancelBubble=true};this.entBox.onbeforedeactivate=function(){if (this.grid._still_active)this.grid._still_active=null;else 
  this.grid.isActive=false;event.cancelBubble=true};if (this.entBox.style.height.toString().indexOf("%") != -1)
  this._setAutoResize();this.setColHidden=this.setColumnsVisibility
  this.enableCollSpan = this.enableColSpan
  this.setMultiselect=this.enableMultiselect;this.setMultiLine=this.enableMultiline;this.deleteSelectedItem=this.deleteSelectedRows;this.getSelectedId=this.getSelectedRowId;this.getHeaderCol=this.getColumnLabel;this.isItemExists=this.doesRowExist;this.getColumnCount=this.getColumnsNum;this.setSelectedRow=this.selectRowById;this.setHeaderCol=this.setColumnLabel;this.preventIECashing=this.preventIECaching;this.enableAutoHeigth=this.enableAutoHeight;this.getUID=this.uid;return this};dhtmlXGridObject.prototype={getRowAttribute: function(id, name){return this.getRowById(id)._attrs[name]},
  setRowAttribute: function(id, name, value){this.getRowById(id)._attrs[name]=value},
  
  isTreeGrid:function(){return(this.cellType._dhx_find("tree") != -1)},
  

  
  setRowHidden:function(id, state){var f = convertStringToBoolean(state);var row = this.getRowById(id) 
  
  if (!row)return;if (row.expand === "")this.collapseKids(row);if ((state)&&(row.style.display != "none")){row.style.display="none";var z = this.selectedRows._dhx_find(row);if (z != -1){row.className=row.className.replace("rowselected", "");for (var i = 0;i < row.childNodes.length;i++)row.childNodes[i].className=row.childNodes[i].className.replace(/cellselected/g, "");this.selectedRows._dhx_removeAt(z)};this.callEvent("onGridReconstructed", [])};if ((!state)&&(row.style.display == "none")){row.style.display="";this.callEvent("onGridReconstructed", [])};this.setSizes()},
  




  
  enableRowsHover:function(mode, cssClass){this._hvrCss=cssClass;if (convertStringToBoolean(mode)){if (!this._elmnh){this.obj._honmousemove=this.obj.onmousemove;this.obj.onmousemove=this._setRowHover;if (_isIE)this.obj.onmouseleave=this._unsetRowHover;else
  this.obj.onmouseout=this._unsetRowHover;this._elmnh=true}}else {if (this._elmnh){this.obj.onmousemove=this.obj._honmousemove;if (_isIE)this.obj.onmouseleave=null;else
  this.obj.onmouseout=null;this._elmnh=false}}},

  
  enableEditEvents:function(click, dblclick, f2Key){this._sclE=convertStringToBoolean(click);this._dclE=convertStringToBoolean(dblclick);this._f2kE=convertStringToBoolean(f2Key)},
  

  
  enableLightMouseNavigation:function(mode){if (convertStringToBoolean(mode)){if (!this._elmn){this.entBox._onclick=this.entBox.onclick;this.entBox.onclick=function(){return true};this.obj._onclick=this.obj.onclick;this.obj.onclick=function(e){var c = this.grid.getFirstParentOfType(e ? e.target : event.srcElement, 'TD');this.grid.editStop();this.grid.doClick(c);this.grid.editCell();(e||event).cancelBubble=true};this.obj._onmousemove=this.obj.onmousemove;this.obj.onmousemove=this._autoMoveSelect;this._elmn=true}}else {if (this._elmn){this.entBox.onclick=this.entBox._onclick;this.obj.onclick=this.obj._onclick;this.obj.onmousemove=this.obj._onmousemove;this._elmn=false}}},
  
  
  
  _unsetRowHover:function(e, c){if (c)that=this;else
  that=this.grid;if ((that._lahRw)&&(that._lahRw != c)){for (var i = 0;i < that._lahRw.childNodes.length;i++)that._lahRw.childNodes[i].className=that._lahRw.childNodes[i].className.replace(that._hvrCss, "");that._lahRw=null}},
  
  
  _setRowHover:function(e){var c = this.grid.getFirstParentOfType(e ? e.target : event.srcElement, 'TD');if (c && c.parentNode!=this.grid._lahRw){this.grid._unsetRowHover(0, c);c=c.parentNode;for (var i = 0;i < c.childNodes.length;i++)c.childNodes[i].className+=" "+this.grid._hvrCss;this.grid._lahRw=c};this._honmousemove(e)},
  
  
  _autoMoveSelect:function(e){if (!this.grid.editor){var c = this.grid.getFirstParentOfType(e ? e.target : event.srcElement, 'TD');if (c.parentNode.idd)this.grid.doClick(c, true, 0)};this._onmousemove(e)},


  
  destructor:function(){if (this._sizeTime)this._sizeTime=window.clearTimeout(this._sizeTime);if (this.formInputs)for (var i = 0;i < this.formInputs.length;i++)this.parentForm.removeChild(this.formInputs[i]);var a;this.xmlLoader=this.xmlLoader.destructor();for (var i = 0;i < this.rowsCol.length;i++)if (this.rowsCol[i])this.rowsCol[i].grid=null;for (i in this.rowsAr)if (this.rowsAr[i])this.rowsAr[i]=null;this.rowsCol=new dhtmlxArray();this.rowsAr=new Array();this.entBox.innerHTML="";this.entBox.onclick=function(){};this.entBox.onmousedown=function(){};this.entBox.onbeforeactivate=function(){};this.entBox.onbeforedeactivate=function(){};this.entBox.onbeforedeactivate=function(){};this.entBox.onselectstart=function(){};this.entBox.grid=null;for (a in this){if ((this[a])&&(this[a].m_obj))
  this[a].m_obj=null;this[a]=null};if (this == globalActiveDHTMLGridObject)globalActiveDHTMLGridObject=null;return null},
  

  
  getSortingState:function(){var z = new Array();if (this.fldSorted){z[0]=this.fldSorted._cellIndex;z[1]=(this.sortImg.src.indexOf("sort_desc.gif") != -1) ? "des" : "asc"};return z},

  
  
  enableAutoHeight:function(mode, maxHeight, countFullHeight){this._ahgr=convertStringToBoolean(mode);this._ahgrF=convertStringToBoolean(countFullHeight);this._ahgrM=maxHeight||null;if (maxHeight == "auto"){this._ahgrM=null;this._ahgrMA=true;this._setAutoResize()}},

  enableStableSorting:function(mode){this._sst=convertStringToBoolean(mode);this.rowsCol.stablesort=function(cmp){var size = this.length-1;for (var i = 0;i < this.length-1;i++){for (var j = 0;j < size;j++)if (cmp(this[j], this[j+1])> 0){var temp = this[j];this[j]=this[j+1];this[j+1]=temp};size--}}},

  
  
  enableKeyboardSupport:function(mode){this._htkebl=!convertStringToBoolean(mode)},
  

  
  enableContextMenu:function(menu){this._ctmndx=menu},

  
  
  setScrollbarWidthCorrection:function(width){this._scrFix=parseInt(width)},

  
  enableTooltips:function(list){this._enbTts=list.split(",");for (var i = 0;i < this._enbTts.length;i++)this._enbTts[i]=convertStringToBoolean(this._enbTts[i])},

  

  
  enableResizing:function(list){this._drsclmn=list.split(",");for (var i = 0;i < this._drsclmn.length;i++)this._drsclmn[i]=convertStringToBoolean(this._drsclmn[i])},
  
  
  setColumnMinWidth:function(width, ind){if (arguments.length == 2){if (!this._drsclmW)this._drsclmW=new Array();this._drsclmW[ind]=width}else
  this._drsclmW=width.split(",")},

  
  
  enableCellIds:function(mode){this._enbCid=convertStringToBoolean(mode)},
  
  

  
  lockRow:function(rowId, mode){var z = this.getRowById(rowId);if (z){z._locked=convertStringToBoolean(mode);if ((this.cell)&&(this.cell.parentNode.idd == rowId))
  this.editStop()}},

  
  
  _getRowArray:function(row){var text = new Array();for (var ii = 0;ii < row.childNodes.length;ii++){var a = this.cells3(row, ii);text[ii]=a.getValue()};return text},

  

  
  
  _launchCommands:function(arr){for (var i = 0;i < arr.length;i++){var args = new Array();for (var j = 0;j < arr[i].childNodes.length;j++)if (arr[i].childNodes[j].nodeType == 1)args[args.length]=arr[i].childNodes[j].firstChild.data;this[arr[i].getAttribute("command")].apply(this, args)}},
  
  
  
  _parseHead:function(xmlDoc){var hheadCol = this.xmlLoader.doXPath("//rows/head", xmlDoc);if (hheadCol.length){var headCol = this.xmlLoader.doXPath("//rows/head/column", hheadCol[0]);var asettings = this.xmlLoader.doXPath("//rows/head/settings", hheadCol[0]);var awidthmet = "setInitWidths";var split = false;if (asettings[0]){for (var s = 0;s < asettings[0].childNodes.length;s++)switch (asettings[0].childNodes[s].tagName){case "colwidth":
  if (asettings[0].childNodes[s].firstChild&&asettings[0].childNodes[s].firstChild.data == "%")awidthmet="setInitWidthsP";break;case "splitat":
  split=(asettings[0].childNodes[s].firstChild ? asettings[0].childNodes[s].firstChild.data : false);break}};this._launchCommands(this.xmlLoader.doXPath("//rows/head/beforeInit/call", hheadCol[0]));if (headCol.length > 0){if (this.hdr.rows.length > 0)this.clearAll(true);var sets = [
  [],
  [],
  [],
  [],
  [],
  [],
  [],
  [],
  []
  ];var attrs = ["", "width", "type", "align", "sort", "color", "format", "hidden", "id"];var calls = ["setHeader", awidthmet, "setColTypes", "setColAlign", "setColSorting", "setColumnColor", "",
  "", "setColumnIds"];for (var i = 0;i < headCol.length;i++){for (var j = 1;j < attrs.length;j++)sets[j].push(headCol[i].getAttribute(attrs[j]));sets[0].push((headCol[i].firstChild
  ? headCol[i].firstChild.data
  : "").replace(/^\s*((.|\n)*.+)\s*$/gi, "$1"))};for (var i = 0;i < calls.length;i++)if (calls[i])this[calls[i]](sets[i].join(this.delim))
  
  for (var i = 0;i < headCol.length;i++){if ((this.cellType[i].indexOf('co')== 0)||(this.cellType[i] == "clist")){var optCol = this.xmlLoader.doXPath("./option", headCol[i]);if (optCol.length){var resAr = new Array();if (this.cellType[i] == "clist"){for (var j = 0;j < optCol.length;j++)resAr[resAr.length]=optCol[j].firstChild
  ? optCol[j].firstChild.data
  : "";this.registerCList(i, resAr)}else {var combo = this.getCombo(i);for (var j = 0;j < optCol.length;j++)combo.put(optCol[j].getAttribute("value"),
  optCol[j].firstChild
  ? optCol[j].firstChild.data
  : "")}}}else if (sets[6][i])if ((this.cellType[i] == "calendar")||(this.fldSort[i] == "date"))
  this.setDateFormat(sets[6][i], i);else
  this.setNumberFormat(sets[6][i], i)};this.init();var param=sets[7].join(this.delim);if (this.setColHidden && param.replace(/,/g,"")!="")
  this.setColHidden(param);if ((split)&&(this.splitAt))
  this.splitAt(split)};this._launchCommands(this.xmlLoader.doXPath("//rows/head/afterInit/call", hheadCol[0]))};var gudCol = this.xmlLoader.doXPath("//rows/userdata", xmlDoc);if (gudCol.length > 0){if (!this.UserData["gridglobaluserdata"])this.UserData["gridglobaluserdata"]=new Hashtable();for (var j = 0;j < gudCol.length;j++){this.UserData["gridglobaluserdata"].put(gudCol[j].getAttribute("name"),
  gudCol[j].firstChild
  ? gudCol[j].firstChild.data
  : "")}}},
  
  

  
  
  
  getCheckedRows:function(col_ind){var d = new Array();this.forEachRow(function(id){if (this.cells(id, col_ind).getValue() != 0)
  d.push(id)})
  return d.join(",")},

  
  _drawTooltip:function(e){var c = this.grid.getFirstParentOfType(e ? e.target : event.srcElement, 'TD');if ((this.grid.editor)&&(this.grid.editor.cell == c))
  return true;var r = c.parentNode;if (!r.idd||r.idd == "__filler__")return;var el = (e ? e.target : event.srcElement);if (r.idd == window.unknown)return true;if (!this.grid.callEvent("onMouseOver", [
  r.idd,
  c._cellIndex
  ]))
  return true;if ((this.grid._enbTts)&&(!this.grid._enbTts[c._cellIndex])){if (el.title)el.title='';return true};if (c._cellIndex >= this.grid._cCount)return;var ced = this.grid.cells(r.idd, c._cellIndex);if (!ced || !ced.cell || !ced.cell._attrs)return;if (el._title)ced.cell.title="";if (!ced.cell._attrs['title'])el._title=true;if (ced)el.title=ced.cell._attrs['title']
  ||(ced.getTitle
  ? ced.getTitle()
  : (ced.getValue()||"").toString().replace(/<[^>]*>/gi, ""));return true},

  
  enableCellWidthCorrection:function(size){if (_isFF)this._wcorr=parseInt(size)},
  
  
  
  getAllRowIds:function(separator){var ar = [];for (var i = 0;i < this.rowsBuffer.length;i++)if (this.rowsBuffer[i])ar.push(this.rowsBuffer[i].idd);return ar.join(separator||this.delim)
  },
  getAllItemIds:function(){return this.getAllRowIds()},
  

  
  
  preventIECaching:function(mode){this.no_cashe=convertStringToBoolean(mode);this.xmlLoader.rSeed=this.no_cashe},
  enableColumnAutoSize:function(mode){this._eCAS=convertStringToBoolean(mode)},
  
  _onHeaderDblClick:function(e){var that = this.grid;var el = that.getFirstParentOfType(_isIE ? event.srcElement : e.target, "TD");if (!that._eCAS)return false;that.adjustColumnSize(el._cellIndexS)
  },
  
  
  adjustColumnSize:function(cInd, complex){if (this._hrrar && this._hrrar[cInd])return;this._notresize=true;var m = 0;this._setColumnSizeR(cInd, 20);for (var j = 1;j < this.hdr.rows.length;j++){var a = this.hdr.rows[j];a=a.childNodes[(a._childIndexes) ? a._childIndexes[cInd] : cInd];if ((a)&&((!a.colSpan)||(a.colSpan < 2)) && a._cellIndex==cInd){if ((a.childNodes[0])&&(a.childNodes[0].className == "hdrcell"))
  a=a.childNodes[0];m=Math.max(m, ((_isFF||_isOpera) ? (a.textContent.length*7) : a.scrollWidth))}};var l = this.obj.rows.length;for (var i = 1;i < l;i++){var z = this.obj.rows[i];if (z._childIndexes&&z._childIndexes[cInd] != cInd || !z.childNodes[cInd])continue;if (_isFF||_isOpera||complex)z=z.childNodes[cInd].textContent.length*7;else
  z=z.childNodes[cInd].scrollWidth;if (z > m)m=z};m+=20+(complex||0);this._setColumnSizeR(cInd, m);this._notresize=false;this.setSizes()},
  

  
  detachHeader:function(index, hdr){hdr=hdr||this.hdr;var row = hdr.rows[index+1];if (row)row.parentNode.removeChild(row);this.setSizes()},
  
  
  detachFooter:function(index){this.detachHeader(index, this.ftr)},
  
  
  attachHeader:function(values, style, _type){if (typeof (values)== "string")
  values=this._eSplit(values);if (typeof (style)== "string")
  style=style.split(this.delim);_type=_type||"_aHead";if (this.hdr.rows.length){if (values)this._createHRow([
  values,
  style
  ], this[(_type == "_aHead") ? "hdr" : "ftr"]);else if (this[_type])for (var i = 0;i < this[_type].length;i++)this.attachHeader.apply(this, this[_type][i])}else {if (!this[_type])this[_type]=new Array();this[_type][this[_type].length]=[
  values,
  style,
  _type
  ]}},
  
  _createHRow:function(data, parent){if (!parent){this.entBox.style.position="relative";var z = document.createElement("DIV");z.className="c_ftr".substr(2);this.entBox.appendChild(z);var t = document.createElement("TABLE");t.cellPadding=t.cellSpacing=0;if (!_isIE){t.width="100%";t.style.paddingRight="20px"};t.style.marginRight="20px";t.style.tableLayout="fixed";z.appendChild(t);t.appendChild(document.createElement("TBODY"));this.ftr=parent=t;var hdrRow = t.insertRow(0);var thl = ((this.hdrLabels.length <= 1) ? data[0].length : this.hdrLabels.length);for (var i = 0;i < thl;i++){hdrRow.appendChild(document.createElement("TH"));hdrRow.childNodes[i]._cellIndex=i};if (_isIE)hdrRow.style.position="absolute";else
  hdrRow.style.height='auto'};var st1 = data[1];var z = document.createElement("TR");parent.rows[0].parentNode.appendChild(z);for (var i = 0;i < data[0].length;i++){if (data[0][i] == "#cspan"){var pz = z.cells[z.cells.length-1];pz.colSpan=(pz.colSpan||1)+1;continue};if ((data[0][i] == "#rspan")&&(parent.rows.length > 1)){var pind = parent.rows.length-2;var found = false;var pz = null;while (!found){var pz = parent.rows[pind];for (var j = 0;j < pz.cells.length;j++)if (pz.cells[j]._cellIndex == i){found=j+1;break};pind--};pz=pz.cells[found-1];pz.rowSpan=(pz.rowSpan||1)+1;continue};var w = document.createElement("TD");w._cellIndex=w._cellIndexS=i;if (this._hrrar && this._hrrar[i] && !_isIE)w.style.display='none';if (this.forceDivInHeader)w.innerHTML="<div class='hdrcell'>"+data[0][i]+"</div>";else
  w.innerHTML=data[0][i];if ((data[0][i]||"").indexOf("#") != -1){var t = data[0][i].match(/(^|{)#([^}]+)(}|$)/);if (t){var tn = "_in_header_"+t[2];if (this[tn])this[tn]((this.forceDivInHeader ? w.firstChild : w), i, data[0][i].split(t[0]))}};if (st1)w.style.cssText=st1[i];z.appendChild(w)};var self = parent;if (_isKHTML){if (parent._kTimer)window.clearTimeout(parent._kTimer);parent._kTimer=window.setTimeout(function(){parent.rows[1].style.display='none';window.setTimeout(function(){parent.rows[1].style.display=''}, 1)}, 500)}},



  
  dhx_Event:function(){this.dhx_SeverCatcherPath="";this.attachEvent=function(original, catcher, CallObj){CallObj=CallObj||this;original='ev_'+original;if ((!this[original])||(!this[original].addEvent)){var z = new this.eventCatcher(CallObj);z.addEvent(this[original]);this[original]=z};return(original+':'+this[original].addEvent(catcher))};this.callEvent=function(name, arg0){if (this["ev_"+name])return this["ev_"+name].apply(this, arg0);return true};this.checkEvent=function(name){if (this["ev_"+name])return true;return false};this.eventCatcher=function(obj){var dhx_catch = new Array();var m_obj = obj;var z = function(){if (dhx_catch)var res = true;for (var i = 0;i < dhx_catch.length;i++){if (dhx_catch[i] != null){var zr = dhx_catch[i].apply(m_obj, arguments);res=res&&zr}};return res};z.addEvent=function(ev){if (typeof (ev)!= "function")
  ev=eval(ev);if (ev)return dhx_catch.push(ev)-1;return false};z.removeEvent=function(id){dhx_catch[id]=null};return z};this.detachEvent=function(id){if (id != false){var list = id.split(':');this[list[0]].removeEvent(list[1])}}},
  
  forEachRow:function(custom_code){for (var a in this.rowsAr)if (this.rowsAr[a]&&this.rowsAr[a].idd)custom_code.apply(this, [this.rowsAr[a].idd])},
  
  forEachCell:function(rowId, custom_code){var z = this.getRowById(rowId);if (!z)return;for (var i = 0;i < this._cCount;i++)custom_code(this.cells3(z, i),i)},
  
  enableAutoWidth:function(mode, max_limit, min_limit){this._awdth=[
  convertStringToBoolean(mode),
  parseInt(max_limit||99999),
  parseInt(min_limit||0)
  ]},

  
  
  updateFromXML:function(url, insert_new, del_missed, afterCall){if (typeof insert_new == "undefined")insert_new=true;this._refresh_mode=[
  true,
  insert_new,
  del_missed
  ];this.load(url,afterCall)
  },
  _refreshFromXML:function(xml){reset = false;if (window.eXcell_tree){eXcell_tree.prototype.setValueX=eXcell_tree.prototype.setValue;eXcell_tree.prototype.setValue=function(content){var r=this.grid._h2.get[this.cell.parentNode.idd]
  if (r && this.cell.parentNode.valTag){this.setLabel(content)}else
  this.setValueX(content)}};var tree = this.cellType._dhx_find("tree");xml.getXMLTopNode("rows");var pid = xml.doXPath("//rows")[0].getAttribute("parent")||0;var del = {};if (this._refresh_mode[2]){if (tree != -1)this._h2.forEachChild(pid, function(obj){del[obj.id]=true}, this);else
  this.forEachRow(function(id){del[id]=true})};var rows = xml.doXPath("//row");for (var i = 0;i < rows.length;i++){var row = rows[i];var id = row.getAttribute("id");del[id]=false;var pid = row.parentNode.getAttribute("id")||pid;if (this.rowsAr[id] && this.rowsAr[id].tagName!="TR"){if (this._h2)this._h2.get[id].buff.data=row;else
  this.rowsBuffer[this.getRowIndex(id)].data=row;this.rowsAr[id]=row}else if (this.rowsAr[id]){this._process_xml_row(this.rowsAr[id], row, -1);this._postRowProcessing(this.rowsAr[id],true)
  }else if (this._refresh_mode[1]){this.rowsBuffer.push({idd: id,
  data: row,
  _parser: this._process_xml_row,
  _locator: this._get_xml_data
  });if (this._h2){reset=true;(this._h2.add(id,(row.parentNode.getAttribute("id")||row.parentNode.getAttribute("parent")))).buff=this.rowsBuffer[this.rowsBuffer.length-1]};this.rowsAr[id]=row;row=this.render_row(this.rowsBuffer.length-1);this._insertRowAt(row,-1)
  }};if (this._refresh_mode[2])for (id in del){if (del[id]&&this.rowsAr[id])this.deleteRow(id)};this._refresh_mode=null;if (window.eXcell_tree)eXcell_tree.prototype.setValue=eXcell_tree.prototype.setValueX;if (reset)this._renderSort();this.callEvent("onXLE", [
  this,
  rows.length
  ])},


  
  getCustomCombo:function(id, ind){var cell = this.cells(id, ind).cell;if (!cell._combo)cell._combo=new dhtmlXGridComboObject();return cell._combo},

  
  setTabOrder:function(order){var t = order.split(this.delim);this._tabOrder=[];for (var i = 0;i < this._cCount;i++)t[i]={c: parseInt(t[i]),
  ind: i
  };t.sort(function(a, b){return(a.c > b.c ? 1 : -1)});for (var i = 0;i < this._cCount;i++)if (!t[i+1]||( typeof t[i].c == "undefined"))
  this._tabOrder[t[i].ind]=(t[0].ind+1)*-1;else
  this._tabOrder[t[i].ind]=t[i+1].ind},
  
  i18n:{loading: "Loading",
  decimal_separator:".",
  group_separator:","
  },
  
  
  _key_events:{k13_1_0: function(){var rowInd = this.rowsCol._dhx_find(this.row)
  this.selectCell(this.rowsCol[rowInd+1], this.cell._cellIndex, true)},
  k13_0_1: function(){var rowInd = this.rowsCol._dhx_find(this.row)
  this.selectCell(this.rowsCol[rowInd-1], this.cell._cellIndex, true)},
  k13_0_0: function(){this.editStop();this.callEvent("onEnter", [
  (this.row ? this.row.idd : null),
  (this.cell ? this.cell._cellIndex : null)
  ]);this._still_active=true},
  k9_0_0: function(){this.editStop();if (!this.callEvent("onTab",[true])) return true;var z = this._getNextCell(null, 1);if (z){this.selectCell(z.parentNode, z._cellIndex, (this.row != z.parentNode), false, true);this._still_active=true}},
  k9_0_1: function(){this.editStop();if (!this.callEvent("onTab",[false])) return false;var z = this._getNextCell(null, -1);if (z){this.selectCell(z.parentNode, z._cellIndex, (this.row != z.parentNode), false, true);this._still_active=true}},
  k113_0_0: function(){if (this._f2kE)this.editCell()},
  k32_0_0: function(){var c = this.cells4(this.cell);if (!c.changeState||(c.changeState()=== false))
  return false},
  k27_0_0: function(){this.editStop(true)},
  k33_0_0: function(){if (this.pagingOn)this.changePage(this.currentPage-1);else
  this.scrollPage(-1)},
  k34_0_0: function(){if (this.pagingOn)this.changePage(this.currentPage+1);else
  this.scrollPage(1)},
  k37_0_0: function(){if (!this.editor&&this.isTreeGrid())
  this.collapseKids(this.row)
  else
  return false},
  k39_0_0: function(){if (!this.editor&&this.isTreeGrid())
  this.expandKids(this.row)
  else
  return false},
  k40_0_0: function(){var master = this._realfake?this._fake:this;if (this.editor&&this.editor.combo)this.editor.shiftNext();else {if (!this.row.idd)return;var rowInd = Math.max((master._r_select||0),this.getRowIndex(this.row.idd))+1;if (this.rowsBuffer[rowInd]){master._r_select=null;this.selectCell(rowInd, this.cell._cellIndex, true);if (master.pagingOn)master.showRow(master.getRowId(rowInd))}else {this._key_events.k34_0_0.apply(this, []);if (this.pagingOn && this.rowsCol[rowInd])this.selectCell(rowInd, 0, true)}};this._still_active=true},
  k38_0_0: function(){var master = this._realfake?this._fake:this;if (this.editor&&this.editor.combo)this.editor.shiftPrev();else {if (!this.row.idd)return;var rowInd = this.getRowIndex(this.row.idd)+1;if (rowInd != -1 && (!this.pagingOn || (rowInd!=1))){var nrow = this._nextRow(rowInd-1, -1);this.selectCell(nrow, this.cell._cellIndex, true);if (master.pagingOn && nrow)master.showRow(nrow.idd)}else {this._key_events.k33_0_0.apply(this, [])}};this._still_active=true}},
  
  
  
  _build_master_row:function(){var t = document.createElement("DIV");var html = ["<table><tr>"];for (var i = 0;i < this._cCount;i++)html.push("<td></td>");html.push("</tr></table>");t.innerHTML=html.join("");this._master_row=t.firstChild.rows[0]},
  
  _prepareRow:function(new_id){if (!this._master_row)this._build_master_row();var r = this._master_row.cloneNode(true);for (var i = 0;i < r.childNodes.length;i++){r.childNodes[i]._cellIndex=i;if (this._enbCid)r.childNodes[i].id="c_"+new_id+"_"+i;if (this.dragAndDropOff)this.dragger.addDraggableItem(r.childNodes[i], this)};r.idd=new_id;r.grid=this;return r},
  

  _process_jsarray_row:function(r, data){r._attrs={};for (var j = 0;j < r.childNodes.length;j++)r.childNodes[j]._attrs={};this._fillRow(r, (this._c_order ? this._swapColumns(data) : data));return r},
  _get_jsarray_data:function(data, ind){return data[ind]},
  _process_json_row:function(r, data){r._attrs={};for (var j = 0;j < r.childNodes.length;j++)r.childNodes[j]._attrs={};this._fillRow(r, (this._c_order ? this._swapColumns(data.data) : data.data));return r},
  _get_json_data:function(data, ind){return data.data[ind]},
  
  _process_csv_row:function(r, data){r._attrs={};for (var j = 0;j < r.childNodes.length;j++)r.childNodes[j]._attrs={};this._fillRow(r, (this._c_order ? this._swapColumns(data.split(this.csv.cell)) : data.split(this.csv.cell)));return r},
  _get_csv_data:function(data, ind){return data.split(this.csv.cell)[ind]},


  _process_xml_row:function(r, xml){var cellsCol = this.xmlLoader.doXPath("./cell", xml);var strAr = [];r._attrs=this._xml_attrs(xml);if (this._ud_enabled){var udCol = this.xmlLoader.doXPath("./userdata", xml);for (var i = udCol.length-1;i >= 0;i--)this.setUserData(r.idd,udCol[i].getAttribute("name"), udCol[i].firstChild
  ? udCol[i].firstChild.data
  : "")};for (var j = 0;j < cellsCol.length;j++){var cellVal = cellsCol[j];var cind = r._childIndexes?r._childIndexes[j]:j;var exc = cellVal.getAttribute("type");if (r.childNodes[cind]){if (exc)r.childNodes[cind]._cellType=exc;r.childNodes[cind]._attrs=this._xml_attrs(cellVal)};if (!cellVal.getAttribute("xmlcontent")){if (cellVal.firstChild)cellVal=cellVal.firstChild.data;else
  cellVal=""};strAr.push(cellVal)};for (j < cellsCol.length;j < r.childNodes.length;j++)r.childNodes[j]._attrs={};if (r.parentNode&&r.parentNode.tagName == "row")r._attrs["parent"]=r.parentNode.getAttribute("idd");this._fillRow(r, (this._c_order ? this._swapColumns(strAr) : strAr));return r},
  _get_xml_data:function(data, ind){data=data.firstChild;while (true){if (!data)return "";if (data.tagName == "cell")ind--;if (ind < 0)break;data=data.nextSibling};return(data.firstChild ? data.firstChild.data : "")},

  _fillRow:function(r, text){if (this.editor)this.editStop();for (var i = 0;i < r.childNodes.length;i++){if ((i < text.length)||(this.defVal[i])){var ii=r.childNodes[i]._cellIndex;var val = text[ii];var aeditor = this.cells5(r.childNodes[i], (r.childNodes[i]._cellType||this.cellType[ii]));if ((this.defVal[ii])&&((val == "")||( typeof (val) == "undefined")))
  val=this.defVal[ii];aeditor.setValue(val)
  }else {var val = "&nbsp;";r.childNodes[i].innerHTML=val;r.childNodes[i]._clearCell=true}};return r},
  
  _postRowProcessing:function(r,donly){if (r._attrs["class"])r._css=r.className=r._attrs["class"];if (r._attrs.locked)r._locked=true;if (r._attrs.bgColor)r.bgColor=r._attrs.bgColor;var cor=0;for (var i = 0;i < r.childNodes.length;i++){c=r.childNodes[i];var ii=c._cellIndex;var s = c._attrs.style||r._attrs.style;if (s)c.style.cssText+=";"+s;if (c._attrs["class"])c.className=c._attrs["class"];s=c._attrs.align||this.cellAlign[ii];if (s)c.align=s;c.vAlign=c._attrs.valign||this.cellVAlign[ii];var color = c._attrs.bgColor||this.columnColor[ii];if (color)c.bgColor=color;if (c._attrs["colspan"] && !donly){this.setColspan(r.idd, i+cor, c._attrs["colspan"]);cor+=(c._attrs["colspan"]-1)};if (this._hrrar&&this._hrrar[ii]&&!donly)c.style.display="none"};this.callEvent("onRowCreated", [
  r.idd,
  r,
  null
  ])},
  
  load:function(url, call, type){this.callEvent("onXLS", [this]);if (arguments.length == 2 && typeof call != "function"){type=call;call=null};type=type||"xml";if (!this.xmlFileUrl)this.xmlFileUrl=url;this._data_type=type;this.xmlLoader.onloadAction=function(that, b, c, d, xml){xml=that["_process_"+type](xml);if (!that._contextCallTimer)that.callEvent("onXLE", [that,0,0,xml]);if (call){call();call=null}};this.xmlLoader.loadXML(url)},

  loadXML:function(url, afterCall){this.load(url, afterCall, "xml")
  },
  
  parse:function(data, call, type){if (arguments.length == 2 && typeof call != "function"){type=call;call=null};type=type||"xml";this._data_type=type;data=this["_process_"+type](data);if (!this._contextCallTimer)this.callEvent("onXLE", [this,0,0,data]);if (call)call()},
  
  xml:{top: "rows",
  row: "./row",
  cell: "./cell",
  s_row: "row",
  s_cell: "cell",
  row_attrs: [],
  cell_attrs: []
  },
  
  csv:{row: "\n",
  cell: ","
  },
  
  _xml_attrs:function(node){var data = {};if (node.attributes.length){for (var i = 0;i < node.attributes.length;i++)data[node.attributes[i].name]=node.attributes[i].value};return data},

  _process_xml:function(xml){if (!xml.doXPath){var t = new dtmlXMLLoaderObject(function(){});if (typeof xml == "string")t.loadXMLString(xml);else {if (xml.responseXML)t.xmlDoc=xml;else
  t.xmlDoc={};t.xmlDoc.responseXML=xml};xml=t};if (this._refresh_mode)return this._refreshFromXML(xml);this._parsing=true;var top = xml.getXMLTopNode(this.xml.top)
  if (top.tagName.toLowerCase()!=this.xml.top) return;this._parseHead(top);var rows = xml.doXPath(this.xml.row, top)
  var cr = parseInt(xml.doXPath("//"+this.xml.top)[0].getAttribute("pos")||0);var total = parseInt(xml.doXPath("//"+this.xml.top)[0].getAttribute("total_count")||0);if (total&&!this.rowsBuffer[total-1])this.rowsBuffer[total-1]=null;if (this.isTreeGrid())
  return this._process_tree_xml(xml);for (var i = 0;i < rows.length;i++){if (this.rowsBuffer[i+cr])continue;var id = rows[i].getAttribute("id")||(i+cr+1);this.rowsBuffer[i+cr]={idd: id,
  data: rows[i],
  _parser: this._process_xml_row,
  _locator: this._get_xml_data
  };this.rowsAr[id]=rows[i]};this.render_dataset();this._parsing=false;return xml.xmlDoc.responseXML?xml.xmlDoc.responseXML:xml.xmlDoc},


  _process_jsarray:function(data){this._parsing=true;if (data&&data.xmlDoc)eval("data="+data.xmlDoc.responseText+";");for (var i = 0;i < data.length;i++){var id = i+1;this.rowsBuffer.push({idd: id,
  data: data[i],
  _parser: this._process_jsarray_row,
  _locator: this._get_jsarray_data
  });this.rowsAr[id]=data[i]};this.render_dataset();this._parsing=false},
  
  _process_csv:function(data){this._parsing=true;if (data.xmlDoc)data=data.xmlDoc.responseText;data=data.replace(/\r/g,"");data=data.split(this.csv.row);if (this._csvHdr){this.clearAll();var thead=data.splice(0,1)[0].split(this.csv.cell);if (!this._csvAID)thead.splice(0,1);this.setHeader(thead.join(this.delim));this.init()};for (var i = 0;i < data.length;i++){if (!data[i] && i==data.length-1)continue;if (this._csvAID){var id = i+1;this.rowsBuffer.push({idd: id,
  data: data[i],
  _parser: this._process_csv_row,
  _locator: this._get_csv_data
  })}else {var temp = data[i].split(this.csv.cell);var id = temp.splice(0,1)[0];this.rowsBuffer.push({idd: id,
  data: temp,
  _parser: this._process_jsarray_row,
  _locator: this._get_jsarray_data
  })};this.rowsAr[id]=data[i]};this.render_dataset();this._parsing=false},
  
  _process_json:function(data){this._parsing=true;if (data&&data.xmlDoc)eval("data="+data.xmlDoc.responseText+";");for (var i = 0;i < data.rows.length;i++){var id = data.rows[i].id;this.rowsBuffer.push({idd: id,
  data: data.rows[i],
  _parser: this._process_json_row,
  _locator: this._get_json_data
  });this.rowsAr[id]=data[i]};this.render_dataset();this._parsing=false},

  render_dataset:function(min, max){if (this._srnd){if (this._fillers)return this._update_srnd_view();max=Math.min((this._get_view_size()+(this._srnd_pr||0)), this.rowsBuffer.length)};if (this.pagingOn){min=(this.currentPage-1)*this.rowsBufferOutSize;max=Math.min(min+this.rowsBufferOutSize, this.rowsBuffer.length)
  }else {min=min||0;max=max||this.rowsBuffer.length};for (var i = min;i < max;i++){var r = this.render_row(i)
  
  if (r == -1){if (this.xmlFileUrl){if (this.callEvent("onDynXLS",[i,(this._dpref?this._dpref:(max-i))]))
  this.load(this.xmlFileUrl+getUrlSymbol(this.xmlFileUrl)+"posStart="+i+"&count="+(this._dpref?this._dpref:(max-i)), this._data_type)};max=i;break};if (!r.parentNode||!r.parentNode.tagName){this._insertRowAt(r, i);if (r._attrs["selected"] || r._attrs["select"]){this.selectRow(r,r._attrs["call"]?true:false,true);r._attrs["selected"]=r._attrs["select"]=null}};if (this._ads_count && i-min==this._ads_count){var that=this;this._context_parsing=this._context_parsing||this._parsing;return this._contextCallTimer=window.setTimeout(function(){that._contextCallTimer=null;that.render_dataset(i,max);if (!that._contextCallTimer){if(that._context_parsing)that.callEvent("onXLE",[])
  else that._fixAlterCss();that._context_parsing=false}},this._ads_time)
  }};if (this._srnd&&!this._fillers)this._fillers=[this._add_filler(max, this.rowsBuffer.length-max)];this.setSizes()},
  
  render_row:function(ind){if (!this.rowsBuffer[ind])return -1;if (this.rowsBuffer[ind]._parser){var r = this.rowsBuffer[ind];var row = this._prepareRow(r.idd);this.rowsBuffer[ind]=row;this.rowsAr[r.idd]=row;r._parser.call(this, row, r.data);this._postRowProcessing(row);return row};return this.rowsBuffer[ind]},
  
  
  _get_cell_value:function(row, ind, method){if (row._locator){if (this._c_order)ind=this._c_order[ind];return row._locator.call(this, row.data, ind)};return this.cells3(row, ind)[method ? method : "getValue"]()},

  
  sortRows:function(col, type, order){order=(order||"asc").toLowerCase();type=(type||this.fldSort[col]);col=col||0;if (this.isTreeGrid())
  this.sortTreeRows(col, type, order)
  else {var arrTS = {};var atype = this.cellType[col];var amet = "getValue";if (atype == "link")amet="getContent";if (atype == "dhxCalendar"||atype == "dhxCalendarA")amet="getDate";for (var i = 0;i < this.rowsBuffer.length;i++)arrTS[this.rowsBuffer[i].idd]=this._get_cell_value(this.rowsBuffer[i], col, amet);this._sortRows(col, type, order, arrTS)};this.callEvent("onAfterSorting", [col,type,order])},
  
  _sortCore:function(col, type, order, arrTS, s){var sort = "sort";if (this._sst){s["stablesort"]=this.rowsCol.stablesort;sort="stablesort"};if (type == 'str'){s[sort](function(a, b){if (order == "asc")return arrTS[a.idd] > arrTS[b.idd] ? 1 : -1
  else
  return arrTS[a.idd] < arrTS[b.idd] ? 1 : -1
  })}else if (type == 'int'){s[sort](function(a, b){var aVal = parseFloat(arrTS[a.idd]);aVal=isNaN(aVal) ? -99999999999999 : aVal;var bVal = parseFloat(arrTS[b.idd]);bVal=isNaN(bVal) ? -99999999999999 : bVal;if (order == "asc")return aVal-bVal;else
  return bVal-aVal})}else if (type == 'date'){s[sort](function(a, b){var aVal = Date.parse(arrTS[a.idd])||(Date.parse("01/01/1900"));var bVal = Date.parse(arrTS[b.idd])||(Date.parse("01/01/1900"));if (order == "asc")return aVal-bVal
  else
  return bVal-aVal
  })}},
  
  _sortRows:function(col, type, order, arrTS){this._sortCore(col, type, order, arrTS, this.rowsBuffer);this._reset_view();this.callEvent("onGridReconstructed", [])},

  _reset_view:function(skip){if (!this.obj.rows[0])return;var tb = this.obj.rows[0].parentNode;var tr = tb.removeChild(tb.childNodes[0], true)
  if (_isKHTML)for (var i = tb.parentNode.childNodes.length-1;i >= 0;i--){if (tb.parentNode.childNodes[i].tagName=="TR")tb.parentNode.removeChild(tb.parentNode.childNodes[i],true)}else if (_isIE)for (var i = tb.childNodes.length-1;i >= 0;i--)tb.childNodes[i].removeNode(true);else
  tb.innerHTML="";tb.appendChild(tr)
  this.rowsCol=dhtmlxArray();if (this._sst)this.enableStableSorting(true);this._fillers=this.undefined;if (!skip){if (_isIE && this._srnd){var p=this._get_view_size;this._get_view_size=function(){return 1};this.render_dataset();this._get_view_size=p}else
  this.render_dataset()}},
  
  
  deleteRow:function(row_id, node){if (!node)node=this.getRowById(row_id)
  
  if (!node)return;this.editStop();if (this.callEvent("onBeforeRowDeleted", [row_id])== false)
  return false;var pid=0;if (this.cellType._dhx_find("tree")!= -1 && !this._realfake){pid=this._h2.get[row_id].parent.id;this._removeTrGrRow(node)}else {if (node.parentNode)node.parentNode.removeChild(node);var ind = this.rowsCol._dhx_find(node);if (ind != -1)this.rowsCol._dhx_removeAt(ind);for (var i = 0;i < this.rowsBuffer.length;i++)if (this.rowsBuffer[i]&&this.rowsBuffer[i].idd == row_id){this.rowsBuffer._dhx_removeAt(i);ind=i;break}};this.rowsAr[row_id]=null;for (var i = 0;i < this.selectedRows.length;i++)if (this.selectedRows[i].idd == row_id)this.selectedRows._dhx_removeAt(i);if (this._srnd){for (var i = 0;i < this._fillers.length;i++){var f = this._fillers[i]
  if (!f)continue;if (f[0] >= ind)f[0]=f[0]-1;else if (f[1] >= ind)f[1]=f[1]-1};this._update_srnd_view()};if (this.pagingOn)this.changePage();if (!this._realfake)this.callEvent("onAfterRowDeleted", [row_id,pid]);this.callEvent("onGridReconstructed", []);return true},
  
  _addRow:function(new_id, text, ind){if (ind == -1|| typeof ind == "undefined")ind=this.rowsBuffer.length;if (typeof text == "string")text=text.split(this.delim);var row = this._prepareRow(new_id);row._attrs={};for (var j = 0;j < row.childNodes.length;j++)row.childNodes[j]._attrs={};this.rowsAr[row.idd]=row;if (this._h2)this._h2.get[row.idd].buff=row;this._fillRow(row, text)
  this._postRowProcessing(row)
  if (this._skipInsert){this._skipInsert=false;return this.rowsAr[row.idd]=row};if (this.pagingOn){this.rowsBuffer._dhx_insertAt(ind,row);this.rowsAr[row.idd]=row;return row};if (this._fillers){this.rowsCol._dhx_insertAt(ind, null);this.rowsBuffer._dhx_insertAt(ind,row);if (this._fake)this._fake.rowsCol._dhx_insertAt(ind, null);this.rowsAr[row.idd]=row;var found = false;for (var i = 0;i < this._fillers.length;i++){var f = this._fillers[i];if (f&&f[0] <= ind&&(f[0]+f[1])>= ind){f[1]=f[1]+1;f[2].firstChild.style.height=parseInt(f[2].firstChild.style.height)+this._srdh+"px";found=true;if (this._fake)this._fake._fillers[i][1]++};if (f&&f[0] > ind){f[0]=f[0]+1
  if (this._fake)this._fake._fillers[i][0]++}};if (!found)this._fillers.push(this._add_filler(ind, 1, (ind == 0 ? {parentNode: this.obj.rows[0].parentNode,
  nextSibling: (this.rowsCol[1])
  }: this.rowsCol[ind-1])));return row};this.rowsBuffer._dhx_insertAt(ind,row);return this._insertRowAt(row, ind)},
  
  
  addRow:function(new_id, text, ind){var r = this._addRow(new_id, text, ind);if (!this.dragContext)this.callEvent("onRowAdded", [new_id]);if (this.pagingOn)this.changePage(this.currentPage)
  
  if (this._srnd)this._update_srnd_view();r._added=true;if (this._ahgr)this.setSizes();this.callEvent("onGridReconstructed", []);return r},
  
  _insertRowAt:function(r, ind, skip){this.rowsAr[r.idd]=r;if (this._skipInsert){this._skipInsert=false;return r};if ((ind < 0)||((!ind)&&(parseInt(ind) !== 0)))
  ind=this.rowsCol.length;else {if (ind > this.rowsCol.length)ind=this.rowsCol.length};if (this._cssEven){if ((this._cssSP ? this.getLevel(r.idd): ind)%2 == 1)
  r.className+=" "+this._cssUnEven+(this._cssSU ? (this._cssUnEven+"_"+this.getLevel(r.idd)) : "");else
  r.className+=" "+this._cssEven+(this._cssSU ? (" "+this._cssEven+"_"+this.getLevel(r.idd)) : "")};if (!skip)if ((ind == (this.obj.rows.length-1))||(!this.rowsCol[ind]))
  if (_isKHTML)this.obj.appendChild(r);else {this.obj.firstChild.appendChild(r)}else {this.rowsCol[ind].parentNode.insertBefore(r, this.rowsCol[ind])};this.rowsCol._dhx_insertAt(ind, r);return r},
  
  getRowById:function(id){var row = this.rowsAr[id];if (row){if (row.tagName != "TR"){for (var i = 0;i < this.rowsBuffer.length;i++)if (this.rowsBuffer[i] && this.rowsBuffer[i].idd == id)return this.render_row(i);if (this._h2)return this.render_row(null,row.idd)};return row};return null},
  

  cellById:function(row_id, col){return this.cells(row_id, col)},

  cells:function(row_id, col){if (arguments.length == 0)return this.cells4(this.cell);else
  var c = this.getRowById(row_id);var cell = (c._childIndexes ? c.childNodes[c._childIndexes[col]] : c.childNodes[col]);return this.cells4(cell)},
  
  cellByIndex:function(row_index, col){return this.cells2(row_index, col)},
  
  cells2:function(row_index, col){var c = this.render_row(row_index);var cell = (c._childIndexes ? c.childNodes[c._childIndexes[col]] : c.childNodes[col]);return this.cells4(cell)},
  
  cells3:function(row, col){var cell = (row._childIndexes ? row.childNodes[row._childIndexes[col]] : row.childNodes[col]);return this.cells4(cell)},
  
  cells4:function(cell){var type = window["eXcell_"+(cell._cellType||this.cellType[cell._cellIndex])];if (type)return new type(cell)}, 
  cells5:function(cell, type){var type = type||(cell._cellType||this.cellType[cell._cellIndex]);if (!this._ecache[type]){if (!window["eXcell_"+type])var tex = eXcell_ro;else
  var tex = window["eXcell_"+type];this._ecache[type]=new tex(cell)};this._ecache[type].cell=cell;return this._ecache[type]},
  dma:function(mode){if (!this._ecache)this._ecache={};if (mode&&!this._dma){this._dma=this.cells4;this.cells4=this.cells5}else if (!mode&&this._dma){this.cells4=this._dma;this._dma=null}},
  
  
  getRowsNum:function(){return this.rowsBuffer.length},
  
  
  
  enableEditTabOnly:function(mode){if (arguments.length > 0)this.smartTabOrder=convertStringToBoolean(mode);else
  this.smartTabOrder=true},
  
  setExternalTabOrder:function(start, end){var grid = this;this.tabStart=( typeof (start) == "object") ? start : document.getElementById(start);this.tabStart.onkeydown=function(e){var ev = (e||window.event);ev.cancelBubble=true;if (ev.keyCode == 9){grid.selectCell(0, 0, 0, 0, 1);if (grid.cells2(0, 0).isDisabled()){grid._key_events["k9_0_0"].call(grid)};return false}};this.tabEnd=( typeof (end) == "object") ? end : document.getElementById(end);this.tabEnd.onkeydown=function(e){var ev = (e||window.event);ev.cancelBubble=true;if ((ev.keyCode == 9)&&ev.shiftKey){grid.selectCell((grid.getRowsNum()-1), (grid.getColumnCount()-1), 0, 0, 1);if (grid.cells2((grid.getRowsNum()-1), (grid.getColumnCount()-1)).isDisabled()){grid._key_events["k9_0_1"].call(grid)};return false}}},
  
  uid:function(){if (!this._ui_seed)this._ui_seed=(new Date()).valueOf();return this._ui_seed++},
  
  clearAndLoad:function(){var t=this._pgn_skin;this._pgn_skin=null;this.clearAll();this._pgn_skin=t;this.load.apply(this,arguments)},
  
  getStateOfView:function(){if (this.pagingOn)return [this.currentPage, (this.currentPage-1)*this.rowsBufferOutSize, (this.currentPage-1)*this.rowsBufferOutSize+this.rowsCol.length, this.rowsBuffer.length ];return [
  Math.floor(this.objBox.scrollTop/this._srdh),
  Math.ceil(parseInt(this.objBox.offsetHeight)/this._srdh),
  this.limit
  ]}};// (c)dhtmlx ltd. www.dhtmlx.com
 // v.2.0 build 81107

 /*
	 * Copyright DHTMLX LTD. http://www.dhtmlx.com You allowed to use this
	 * component or parts of it under GPL terms To use it on other terms or get
	 * Professional edition of the component please contact us at
	 * sales@dhtmlx.com
	 */
//v.2.0 build 81107

  /*
  Copyright DHTMLX LTD. http://www.dhtmlx.com
  You allowed to use this component or parts of it under GPL terms
  To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
  */

  function dhtmlXGridCellObject(obj){this.destructor=function(){this.cell.obj=null;this.cell=null;this.grid=null;this.base=null;return null};this.cell=obj;this.getValue=function(){if ((this.cell.firstChild)&&(this.cell.firstChild.tagName == "TEXTAREA"))
   return this.cell.firstChild.value;else
   return this.cell.innerHTML._dhx_trim()};this.getMathValue=function(){if (this.cell.original)return this.cell.original;else
   return this.getValue()};this.getFont=function(){arOut=new Array(3);if (this.cell.style.fontFamily)arOut[0]=this.cell.style.fontFamily

   if (this.cell.style.fontWeight == 'bold'||this.cell.parentNode.style.fontWeight == 'bold')arOut[1]='bold';if (this.cell.style.fontStyle == 'italic'||this.cell.parentNode.style.fontWeight == 'italic')arOut[1]+='italic';if (this.cell.style.fontSize)arOut[2]=this.cell.style.fontSize
   else
   arOut[2]="";return arOut.join("-")
   };this.getTextColor=function(){if (this.cell.style.color)return this.cell.style.color
   else
   return "#000000"};this.getBgColor=function(){if (this.cell.bgColor)return this.cell.bgColor
   else
   return "#FFFFFF"};this.getHorAlign=function(){if (this.cell.style.textAlign)return this.cell.style.textAlign;else if (this.cell.style.textAlign)return this.cell.style.textAlign;else
   return "left"};this.getWidth=function(){return this.cell.scrollWidth};this.setFont=function(val){fntAr=val.split("-");this.cell.style.fontFamily=fntAr[0];this.cell.style.fontSize=fntAr[fntAr.length-1]

   if (fntAr.length == 3){if (/bold/.test(fntAr[1]))
   this.cell.style.fontWeight="bold";if (/italic/.test(fntAr[1]))
   this.cell.style.fontStyle="italic";if (/underline/.test(fntAr[1]))
   this.cell.style.textDecoration="underline"}};this.setTextColor=function(val){this.cell.style.color=val};this.setBgColor=function(val){if (val == "")val=null;this.cell.bgColor=val};this.setHorAlign=function(val){if (val.length == 1){if (val == 'c')this.cell.style.textAlign='center'

   else if (val == 'l')this.cell.style.textAlign='left';else
   this.cell.style.textAlign='right'}else
   this.cell.style.textAlign=val
   };this.wasChanged=function(){if (this.cell.wasChanged)return true;else
   return false};this.isCheckbox=function(){var ch = this.cell.firstChild;if (ch&&ch.tagName == 'INPUT'){type=ch.type;if (type == 'radio'||type == 'checkbox')return true;else
   return false}else
   return false};this.isChecked=function(){if (this.isCheckbox()){return this.cell.firstChild.checked}};this.isDisabled=function(){return this.cell._disabled};this.setChecked=function(fl){if (this.isCheckbox()){if (fl != 'true'&&fl != 1)fl=false;this.cell.firstChild.checked=fl}};this.setDisabled=function(fl){if (fl != 'true'&&fl != 1)fl=false;if (this.isCheckbox()){this.cell.firstChild.disabled=fl;if (this.disabledF)this.disabledF(fl)};this.cell._disabled=fl}};dhtmlXGridCellObject.prototype={getAttribute: function(name){return this.cell._attrs[name]},
   setAttribute: function(name, value){this.cell._attrs[name]=value}};dhtmlXGridCellObject.prototype.setValue=function(val){if (( typeof (val)!= "number")&&(!val||val.toString()._dhx_trim() == "")){val="&nbsp;"
   this.cell._clearCell=true}else
   this.cell._clearCell=false;this.setCValue(val)};dhtmlXGridCellObject.prototype.getTitle=function(){return (_isIE ? this.cell.innerText : this.cell.textContent)};dhtmlXGridCellObject.prototype.setCValue=function(val, val2){this.cell.innerHTML=val};dhtmlXGridCellObject.prototype.setCTxtValue=function(val){this.cell.innerHTML="";this.cell.appendChild(document.createTextNode(val))};dhtmlXGridCellObject.prototype.setLabel=function(val){this.cell.innerHTML=val};dhtmlXGridCellObject.prototype.getMath=function(){if (this._val)return this.val;else
   return this.getValue()};function eXcell(){this.obj=null;this.val=null;this.changeState=function(){return false
   };this.edit=function(){this.val=this.getValue()
   };this.detach=function(){return false
   };this.getPosition=function(oNode){var oCurrentNode = oNode;var iLeft = 0;var iTop = 0;while (oCurrentNode.tagName != "BODY"){iLeft+=oCurrentNode.offsetLeft;iTop+=oCurrentNode.offsetTop;oCurrentNode=oCurrentNode.offsetParent};return new Array(iLeft, iTop)}};eXcell.prototype=new dhtmlXGridCellObject;function eXcell_ed(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid};this.edit=function(){this.cell.atag=((!this.grid.multiLine)&&(_isKHTML||_isMacOS||_isFF)) ? "INPUT" : "TEXTAREA";this.val=this.getValue();this.obj=document.createElement(this.cell.atag);this.obj.setAttribute("autocomplete", "off");this.obj.style.height=(this.cell.offsetHeight-(_isIE ? 4 : 2))+"px";this.obj.className="dhx_combo_edit";this.obj.wrap="soft";this.obj.style.textAlign=this.cell.style.textAlign;this.obj.onclick=function(e){(e||event).cancelBubble=true
   };this.obj.onmousedown=function(e){(e||event).cancelBubble=true
   };this.obj.value=this.val
   this.cell.innerHTML="";this.cell.appendChild(this.obj);if (_isFF){this.obj.style.overflow="visible";if ((this.grid.multiLine)&&(this.obj.offsetHeight >= 18)&&(this.obj.offsetHeight < 40)){this.obj.style.height="36px";this.obj.style.overflow="scroll"}};this.obj.onselectstart=function(e){if (!e)e=event;e.cancelBubble=true;return true};if (_isIE){this.obj.select();this.obj.value=this.obj.value};this.obj.focus()
   };this.getValue=function(){if ((this.cell.firstChild)&&((this.cell.atag)&&(this.cell.firstChild.tagName == this.cell.atag)))
   return this.cell.firstChild.value;if (this.cell._clearCell)return "";return this.cell.innerHTML.toString()._dhx_trim()};this.detach=function(){this.setValue(this.obj.value);return this.val != this.getValue()}};eXcell_ed.prototype=new eXcell;function eXcell_edtxt(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid};this.getValue=function(){if ((this.cell.firstChild)&&((this.cell.atag)&&(this.cell.firstChild.tagName == this.cell.atag)))
   return this.cell.firstChild.value;if (this.cell._clearCell)return "";return (_isIE ? this.cell.innerText : this.cell.textContent)};this.setValue=function(val){if (!val||val.toString()._dhx_trim() == ""){val=" ";this.cell._clearCell=true}else
   this.cell._clearCell=false;this.setCTxtValue(val)}};eXcell_edtxt.prototype=new eXcell_ed;function eXcell_ch(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid;this.cell.obj=this};this.disabledF=function(fl){if ((fl == true)||(fl == 1))
   this.cell.innerHTML=this.cell.innerHTML.replace("item_chk0.", "item_chk0_dis.").replace("item_chk1.",
   "item_chk1_dis.");else
   this.cell.innerHTML=this.cell.innerHTML.replace("item_chk0_dis.", "item_chk0.").replace("item_chk1_dis.",
   "item_chk1.")};this.changeState=function(){if ((!this.grid.isEditable)||(this.cell.parentNode._locked)||(this.isDisabled()))
   return;if (this.grid.callEvent("onEditCell", [
   0,
   this.cell.parentNode.idd,
   this.cell._cellIndex
   ])){this.val=this.getValue()

   if (this.val == "1")this.setValue("0")
   else
   this.setValue("1")

   this.cell.wasChanged=true;this.grid.callEvent("onEditCell", [
   1,
   this.cell.parentNode.idd,
   this.cell._cellIndex
   ]);this.grid.callEvent("onCheckbox", [
   this.cell.parentNode.idd,
   this.cell._cellIndex,
   (this.val != '1')
   ]);this.grid.callEvent("onCheck", [
   this.cell.parentNode.idd,
   this.cell._cellIndex,
   (this.val != '1')
   ])}else {this.editor=null}};this.getValue=function(){return this.cell.chstate ? this.cell.chstate.toString() : "0"};this.isCheckbox=function(){return true};this.isChecked=function(){if (this.getValue()== "1")
   return true;else
   return false};this.setChecked=function(fl){this.setValue(fl.toString())
   };this.detach=function(){return this.val != this.getValue()};this.edit=null};eXcell_ch.prototype=new eXcell;eXcell_ch.prototype.setValue=function(val){this.cell.style.verticalAlign="middle";if (val){val=val.toString()._dhx_trim();if ((val == "false")||(val == "0"))
   val=""};if (val){val="1";this.cell.chstate="1"}else {val="0";this.cell.chstate="0"
   };var obj = this;this.setCValue("<img src='"+this.grid.imgURL+"item_chk"+val
   +".gif' onclick='new eXcell_ch(this.parentNode).changeState();(arguments[0]||event).cancelBubble=true;'>",
   this.cell.chstate)};function eXcell_ra(cell){this.base=eXcell_ch;this.base(cell)
   this.grid=cell.parentNode.grid;this.disabledF=function(fl){if ((fl == true)||(fl == 1))
   this.cell.innerHTML=this.cell.innerHTML.replace("radio_chk0.", "radio_chk0_dis.").replace("radio_chk1.",
   "radio_chk1_dis.");else
   this.cell.innerHTML=this.cell.innerHTML.replace("radio_chk0_dis.", "radio_chk0.").replace("radio_chk1_dis.",
   "radio_chk1.")};this.changeState=function(mode){if (mode===false && this.getValue()==1) return;if ((!this.grid.isEditable)||(this.cell.parentNode._locked))
   return;if (this.grid.callEvent("onEditCell", [
   0,
   this.cell.parentNode.idd,
   this.cell._cellIndex
   ])!= false){this.val=this.getValue()

   if (this.val == "1")this.setValue("0",true)
   else
   this.setValue("1",true)
   this.cell.wasChanged=true;this.grid.callEvent("onEditCell", [
   1,
   this.cell.parentNode.idd,
   this.cell._cellIndex
   ]);this.grid.callEvent("onCheckbox", [
   this.cell.parentNode.idd,
   this.cell._cellIndex,
   (this.val != '1')
   ]);this.grid.callEvent("onCheck", [
   this.cell.parentNode.idd,
   this.cell._cellIndex,
   (this.val != '1')
   ])}else {this.editor=null}};this.edit=null};eXcell_ra.prototype=new eXcell_ch;eXcell_ra.prototype.setValue=function(val){this.cell.style.verticalAlign="middle";if (val){val=val.toString()._dhx_trim();if ((val == "false")||(val == "0"))
   val=""};if (val){if (!this.grid._RaSeCol)this.grid._RaSeCol=[];if (this.grid._RaSeCol[this.cell._cellIndex]){var z = this.grid.cells4(this.grid._RaSeCol[this.cell._cellIndex]);z.setValue("0");if (arguments[1])z.cell.wasChanged=true;if (this.grid.rowsAr[z.cell.parentNode.idd])this.grid.callEvent("onEditCell", [
   1,
   z.cell.parentNode.idd,
   z.cell._cellIndex
   ])};this.grid._RaSeCol[this.cell._cellIndex]=this.cell;val="1";this.cell.chstate="1"}else {val="0";this.cell.chstate="0"
   };this.setCValue("<img src='"+this.grid.imgURL+"radio_chk"+val+".gif' onclick='new eXcell_ra(this.parentNode).changeState(false);'>",
   this.cell.chstate)};function eXcell_txt(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid};this.edit=function(){this.val=this.getValue()
   this.obj=document.createElement("TEXTAREA");this.obj.className="dhx_textarea";this.obj.onclick=function(e){(e||event).cancelBubble=true
   };var arPos = this.grid.getPosition(this.cell);if (!this.cell._clearCell)this.obj.value=this.val;this.obj.style.display="";this.obj.style.textAlign=this.cell.style.textAlign;if (_isFF){var z_ff = document.createElement("DIV");z_ff.appendChild(this.obj);z_ff.style.overflow="auto";z_ff.className="dhx_textarea";this.obj.style.margin="0px 0px 0px 0px";this.obj.style.border="0px";this.obj=z_ff};document.body.appendChild(this.obj);this.obj.onkeydown=function(e){var ev = (e||event);if (ev.keyCode == 9){globalActiveDHTMLGridObject.entBox.focus();globalActiveDHTMLGridObject.doKey({keyCode: ev.keyCode,
   shiftKey: ev.shiftKey,
   srcElement: "0"
   });return false}};this.obj.style.left=arPos[0]+"px";this.obj.style.top=arPos[1]+this.cell.offsetHeight+"px";if (this.cell.offsetWidth < 200)var pw = 200;else
   var pw = this.cell.offsetWidth;this.obj.style.width=pw+(_isFF ? 18 : 16)+"px"

   if (_isFF){this.obj.firstChild.style.width=parseInt(this.obj.style.width)+"px";this.obj.firstChild.style.height=this.obj.offsetHeight-3+"px"};if (_isIE){this.obj.select();this.obj.value=this.obj.value};if (_isFF)this.obj.firstChild.focus();else {this.obj.focus()
   }};this.detach=function(){var a_val = "";if (_isFF)a_val=this.obj.firstChild.value;else
   a_val=this.obj.value;if (a_val == ""){this.cell._clearCell=true}else
   this.cell._clearCell=false;this.setValue(a_val);document.body.removeChild(this.obj);this.obj=null;return this.val != this.getValue()};this.getValue=function(){if (this.obj){if (_isFF)return this.obj.firstChild.value;else
   return this.obj.value};if (this.cell._clearCell)return "";if ((!this.grid.multiLine))
   return this.cell._brval||this.cell.innerHTML;else
   return this.cell.innerHTML.replace(/<br[^>]*>/gi, "\n")._dhx_trim()}};eXcell_txt.prototype=new eXcell;function eXcell_txttxt(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid};this.getValue=function(){if ((this.cell.firstChild)&&(this.cell.firstChild.tagName == "TEXTAREA"))
   return this.cell.firstChild.value;if (this.cell._clearCell)return "";if ((!this.grid.multiLine)&&this.cell._brval)
   return this.cell._brval;return (_isIE ? this.cell.innerText : this.cell.textContent)};this.setValue=function(val){this.cell._brval=val;if (!val||val.toString()._dhx_trim() == ""){val=" ";this.cell._clearCell=true}else
   this.cell._clearCell=false;this.setCTxtValue(val)}};eXcell_txttxt.prototype=new eXcell_txt;eXcell_txt.prototype.setValue=function(val){if (!val||val.toString()._dhx_trim() == ""){val="&nbsp;"
   this.cell._clearCell=true}else
   this.cell._clearCell=false;this.cell._brval=val;if ((!this.grid.multiLine))
   this.setCValue(val, val);else
   this.setCValue(val.replace(/\n/g, "<br/>"), val)};function eXcell_co(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid;this.combo=(this.cell._combo||this.grid.getCombo(this.cell._cellIndex));this.editable=true
   };this.shiftNext=function(){var z = this.list.options[this.list.selectedIndex+1];if (z)z.selected=true;this.obj.value=this.list.options[this.list.selectedIndex].text;return true};this.shiftPrev=function(){if (this.list.selectedIndex != 0){var z = this.list.options[this.list.selectedIndex-1];if (z)z.selected=true;this.obj.value=this.list.options[this.list.selectedIndex].text};return true};this.edit=function(){this.val=this.getValue();this.text=this.getText()._dhx_trim();var arPos = this.grid.getPosition(this.cell) 

   this.obj=document.createElement("TEXTAREA");this.obj.className="dhx_combo_edit";this.obj.style.height=(this.cell.offsetHeight-4)+"px";this.obj.wrap="soft";this.obj.style.textAlign=this.cell.style.textAlign;this.obj.onclick=function(e){(e||event).cancelBubble=true
   };this.obj.value=this.text
   this.obj.onselectstart=function(e){if (!e)e=event;e.cancelBubble=true;return true};var editor_obj = this;this.obj.onkeyup=function(e){var val = this.readonly ? String.fromCharCode((e||event).keyCode) : this.value;var c = editor_obj.list.options;for (var i = 0;i < c.length;i++)if (c[i].text.indexOf(val)== 0)
   return c[i].selected=true};this.list=document.createElement("SELECT");this.list.className='dhx_combo_select';this.list.style.width=this.cell.offsetWidth+"px";this.list.style.left=arPos[0]+"px";this.list.style.top=arPos[1]+this.cell.offsetHeight+"px";this.list.onclick=function(e){var ev = e||window.event;var cell = ev.target||ev.srcElement

   
   if (cell.tagName == "OPTION")cell=cell.parentNode;editor_obj.setValue(cell.value);editor_obj.editable=false;editor_obj.grid.editStop()};var comboKeys = this.combo.getKeys();var fl = false
   var selOptId = 0;for (var i = 0;i < comboKeys.length;i++){var val = this.combo.get(comboKeys[i])
   this.list.options[this.list.options.length]=new Option(val, comboKeys[i]);if (comboKeys[i] == this.val){selOptId=this.list.options.length-1;fl=true}};if (fl == false){this.list.options[this.list.options.length]=new Option(this.text, this.val === null ? "" : this.val);selOptId=this.list.options.length-1};document.body.appendChild(this.list) 
   this.list.size="6";this.cstate=1;if (this.editable){this.cell.innerHTML=""}else {this.obj.style.width="1px";this.obj.style.height="1px"};this.cell.appendChild(this.obj);this.list.options[selOptId].selected=true;if ((!_isFF)||(this.editable)){this.obj.focus();this.obj.focus()};if (!this.editable){this.obj.style.visibility="hidden";this.list.focus();this.list.onkeydown=function(e){e=e||window.event;editor_obj.grid.setActive(true)

   if (e.keyCode < 30)return editor_obj.grid.doKey({target: editor_obj.cell,
   keyCode: e.keyCode,
   shiftKey: e.shiftKey,
   ctrlKey: e.ctrlKey
   })
   }}};this.getValue=function(){return ((this.cell.combo_value == window.undefined) ? "" : this.cell.combo_value)};this.detach=function(){if (this.val != this.getValue()){this.cell.wasChanged=true};if (this.list.parentNode != null){if (this.editable){if (this.list.selectedIndex&&this.list.options[this.list.selectedIndex].text == this.obj.value)this.setValue(this.list.value)
   else
   this.setValue(this.obj.value)
   }else
   this.setValue(this.list.value)
   };if (this.list.parentNode)this.list.parentNode.removeChild(this.list);if (this.obj.parentNode)this.obj.parentNode.removeChild(this.obj);return this.val != this.getValue()}};eXcell_co.prototype=new eXcell;eXcell_co.prototype.getText=function(){return this.cell.innerHTML};eXcell_co.prototype.setValue=function(val){if (typeof (val)== "object"){var optCol = this.grid.xmlLoader.doXPath("./option", val);if (optCol.length)this.cell._combo=new dhtmlXGridComboObject();for (var j = 0;j < optCol.length;j++)this.cell._combo.put(optCol[j].getAttribute("value"),
   optCol[j].firstChild
   ? optCol[j].firstChild.data
   : "");val=val.firstChild.data};if ((val||"").toString()._dhx_trim() == "")
   val=null
   this.cell.combo_value=val;if (val !== null)this.setCValue((this.cell._combo||this.grid.getCombo(this.cell._cellIndex)).get(val)||val, val);else
   this.setCValue("&nbsp;", val)};function eXcell_coro(cell){this.base=eXcell_co;this.base(cell)
   this.editable=false};eXcell_coro.prototype=new eXcell_co;function eXcell_cotxt(cell){this.base=eXcell_co;this.base(cell)
  };eXcell_cotxt.prototype=new eXcell_co;eXcell_cotxt.prototype.getText=function(){return (_isIE ? this.cell.innerText : this.cell.textContent)};eXcell_cotxt.prototype.setValue=function(val){if (typeof (val)== "object"){var optCol = this.grid.xmlLoader.doXPath("./option", val);if (optCol.length)this.cell._combo=new dhtmlXGridComboObject();for (var j = 0;j < optCol.length;j++)this.cell._combo.put(optCol[j].getAttribute("value"),
   optCol[j].firstChild
   ? optCol[j].firstChild.data
   : "");val=val.firstChild.data};if ((val||"").toString()._dhx_trim() == "")
   val=null

   if (val !== null)this.setCTxtValue((this.cell._combo||this.grid.getCombo(this.cell._cellIndex)).get(val)||val, val);else
   this.setCTxtValue(" ", val);this.cell.combo_value=val};function eXcell_corotxt(cell){this.base=eXcell_co;this.base(cell)
   this.editable=false};eXcell_corotxt.prototype=new eXcell_cotxt;function eXcell_cp(cell){try{this.cell=cell;this.grid=this.cell.parentNode.grid}catch (er){};this.edit=function(){this.val=this.getValue()
   this.obj=document.createElement("SPAN");this.obj.style.border="1px solid black";this.obj.style.position="absolute";var arPos = this.grid.getPosition(this.cell);this.colorPanel(4, this.obj)
   document.body.appendChild(this.obj);this.obj.style.left=arPos[0]+"px";this.obj.style.top=arPos[1]+this.cell.offsetHeight+"px"};this.toolDNum=function(value){if (value.length == 1)value='0'+value;return value};this.colorPanel=function(index, parent){var tbl = document.createElement("TABLE");parent.appendChild(tbl)
   tbl.cellSpacing=0;tbl.editor_obj=this;tbl.style.cursor="default";tbl.onclick=function(e){var ev = e||window.event
   var cell = ev.target||ev.srcElement;var ed = cell.parentNode.parentNode.parentNode.editor_obj
   ed.setValue(cell.style.backgroundColor)
   ed.grid.editStop()};var cnt = 256 / index;for (var j = 0;j <= (256 / cnt);j++){var r = tbl.insertRow(j);for (var i = 0;i <= (256 / cnt);i++){for (var n = 0;n <= (256 / cnt);n++){R=new Number(cnt*j)-(j == 0 ? 0 : 1)
   G=new Number(cnt*i)-(i == 0 ? 0 : 1)
   B=new Number(cnt*n)-(n == 0 ? 0 : 1)
   var rgb =
   this.toolDNum(R.toString(16))+""+this.toolDNum(G.toString(16))+""+this.toolDNum(B.toString(16));var c = r.insertCell(i);c.width="10px";c.innerHTML="&nbsp;";c.title=rgb.toUpperCase()
   c.style.backgroundColor="#"+rgb

   if (this.val != null&&"#"+rgb.toUpperCase()== this.val.toUpperCase()){c.style.border="2px solid white"
   }}}}};this.getValue=function(){return this.cell.firstChild.style ? this.cell.firstChild.style.backgroundColor : ""};this.getRed=function(){return Number(parseInt(this.getValue().substr(1, 2), 16))
   };this.getGreen=function(){return Number(parseInt(this.getValue().substr(3, 2), 16))
   };this.getBlue=function(){return Number(parseInt(this.getValue().substr(5, 2), 16))
   };this.detach=function(){if (this.obj.offsetParent != null)document.body.removeChild(this.obj);return this.val != this.getValue()}};eXcell_cp.prototype=new eXcell;eXcell_cp.prototype.setValue=function(val){this.setCValue("<div style='width:100%;height:"+(this.cell.offsetHeight-2)+";background-color:"+(val||"")
   +";border:0px;'>&nbsp;</div>",
   val)};function eXcell_img(cell){try{this.cell=cell;this.grid=this.cell.parentNode.grid}catch (er){};this.getValue=function(){if (this.cell.firstChild.tagName == "IMG")return this.cell.firstChild.src+(this.cell.titFl != null
   ? "^"+this.cell.tit
   : "");else if (this.cell.firstChild.tagName == "A"){var out = this.cell.firstChild.firstChild.src+(this.cell.titFl != null ? "^"+this.cell.tit : "");out+="^"+this.cell.lnk;if (this.cell.trg)out+="^"+this.cell.trg
   return out}}};eXcell_img.prototype=new eXcell;eXcell_img.prototype.getTitle=function(){return this.cell.tit
  };eXcell_img.prototype.setValue=function(val){var title = val;if (val.indexOf("^")!= -1){var ar = val.split("^");val=ar[0]
   title=ar[1];if (ar.length > 2){this.cell.lnk=ar[2]

   if (ar[3])this.cell.trg=ar[3]
   };this.cell.titFl="1"};this.setCValue("<img src='"+this.grid.iconURL+(val||"")._dhx_trim()+"' border='0'>", val);if (this.cell.lnk){this.cell.innerHTML="<a href='"+this.cell.lnk+"' target='"+this.cell.trg+"'>"+this.cell.innerHTML+"</a>"
   };this.cell.tit=title};function eXcell_price(cell){this.base=eXcell_ed;this.base(cell)
   this.getValue=function(){if (this.cell.childNodes.length > 1)return this.cell.childNodes[1].innerHTML.toString()._dhx_trim()
   else
   return "0"}};eXcell_price.prototype=new eXcell_ed;eXcell_price.prototype.setValue=function(val){if (isNaN(parseFloat(val))){val=this.val||0};var color = "green";if (val < 0)color="red";this.setCValue("<span>$</span><span style='padding-right:2px;color:"+color+";'>"+val+"</span>", val)};function eXcell_dyn(cell){this.base=eXcell_ed;this.base(cell)
   this.getValue=function(){return this.cell.firstChild.childNodes[1].innerHTML.toString()._dhx_trim()
   }};eXcell_dyn.prototype=new eXcell_ed;eXcell_dyn.prototype.setValue=function(val){if (!val||isNaN(Number(val))){val=0};if (val > 0){var color = "green";var img = "dyn_up.gif"}else if (val == 0){var color = "black";var img = "dyn_.gif"}else {var color = "red";var img = "dyn_down.gif"};this.setCValue("<div style='position:relative;padding-right:2px;width:100%;overflow:hidden;'><img src='"+this.grid.imgURL+""+img
   +"' height='15' style='position:absolute;top:0px;left:0px;'><span style=' padding-left:20px;width:100%;color:"+color+";'>"+val
   +"</span></div>",
   val)};function eXcell_ro(cell){if (cell){this.cell=cell;this.grid=this.cell.parentNode.grid};this.edit=function(){};this.isDisabled=function(){return true}};eXcell_ro.prototype=new eXcell;function eXcell_ron(cell){this.cell=cell;this.grid=this.cell.parentNode.grid;this.edit=function(){};this.isDisabled=function(){return true};this.getValue=function(){return this.cell._clearCell?"":this.grid._aplNFb(this.cell.innerHTML.toString()._dhx_trim(), this.cell._cellIndex)}};eXcell_ron.prototype=new eXcell;eXcell_ron.prototype.setValue=function(val){if (val === 0){}else if (!val||val.toString()._dhx_trim() == ""){this.setCValue("&nbsp;");return this.cell._clearCell=true};this.setCValue(val?this.grid._aplNF(val, this.cell._cellIndex):"0");this.cell._clearCell=false};function eXcell_rotxt(cell){this.cell=cell;this.grid=this.cell.parentNode.grid;this.edit=function(){};this.isDisabled=function(){return true};this.setValue=function(val){if (!val||val.toString()._dhx_trim() == "")
   val=" ";this.setCTxtValue(val)};this.getValue=function(){if (this.cell._clearCell)return "";return (_isIE ? this.cell.innerText : this.cell.textContent)}};eXcell_rotxt.prototype=new eXcell;function dhtmlXGridComboObject(){this.keys=new dhtmlxArray();this.values=new dhtmlxArray();this.put=function(key, value){for (var i = 0;i < this.keys.length;i++){if (this.keys[i] == key){this.values[i]=value;return true}};this.values[this.values.length]=value;this.keys[this.keys.length]=key};this.get=function(key){for (var i = 0;i < this.keys.length;i++){if (this.keys[i] == key){return this.values[i]}};return null};this.clear=function(){this.keys=new dhtmlxArray();this.values=new dhtmlxArray()};this.remove=function(key){for (var i = 0;i < this.keys.length;i++){if (this.keys[i] == key){this.keys._dhx_removeAt(i);this.values._dhx_removeAt(i);return true}}};this.size=function(){var j = 0;for (var i = 0;i < this.keys.length;i++){if (this.keys[i] != null)j++};return j};this.getKeys=function(){var keyAr = new Array(0);for (var i = 0;i < this.keys.length;i++){if (this.keys[i] != null)keyAr[keyAr.length]=this.keys[i]};return keyAr};this.save=function(){this._save=new Array();for (var i = 0;i < this.keys.length;i++)this._save[i]=[
   this.keys[i],
   this.values[i]
   ]};this.restore=function(){if (this._save){this.keys[i]=new Array();this.values[i]=new Array();for (var i = 0;i < this._save.length;i++){this.keys[i]=this._save[i][0];this.values[i]=this._save[i][1]}}};return this};function Hashtable(){this.keys=new dhtmlxArray();this.values=new dhtmlxArray();return this};Hashtable.prototype=new dhtmlXGridComboObject;//(c)dhtmlx ltd. www.dhtmlx.com
  //v.2.0 build 81107

  /*
  Copyright DHTMLX LTD. http://www.dhtmlx.com
  You allowed to use this component or parts of it under GPL terms
  To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
  */
   
 //v.2.0 build 81009

   /*
   Copyright DHTMLX LTD. http://www.dhtmlx.com
   You allowed to use this component or parts of it under GPL terms
   To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
   */


   function eXcell_link(cell){this.cell = cell;this.grid = this.cell.parentNode.grid;this.isDisabled=function(){return true};this.edit = function(){};this.getValue = function(){if(this.cell.firstChild.getAttribute)return this.cell.firstChild.innerHTML+"^"+this.cell.firstChild.getAttribute("href")
    else
    return ""};this.setValue = function(val){if((typeof(val)!="number") && (!val || val.toString()._dhx_trim()=="")){this.setCValue("&nbsp;",valsAr);return (this.cell._clearCell=true)};var valsAr = val.split("^");if(valsAr.length==1)valsAr[1] = "";else{if(valsAr.length>1){valsAr[1] = "href='"+valsAr[1]+"'";if(valsAr.length==3)valsAr[1]+= " target='"+valsAr[2]+"'";else
    valsAr[1]+= " target='_blank'"}};this.setCValue("<a "+valsAr[1]+" onclick='(_isIE?event:arguments[0]).cancelBubble = true;'>"+valsAr[0]+"</a>",valsAr)}};eXcell_link.prototype = new eXcell;eXcell_link.prototype.getTitle=function(){var z=this.cell.firstChild;return ((z&&z.tagName)?z.getAttribute("href"):"")};eXcell_link.prototype.getContent=function(){var z=this.cell.firstChild;return ((z&&z.tagName)?z.innerHTML:"")};
   //v.2.0 build 81009

   /*
   Copyright DHTMLX LTD. http://www.dhtmlx.com
   You allowed to use this component or parts of it under GPL terms
   To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
   */
    
    
  //v.2.0 build 81107

    /*
    Copyright DHTMLX LTD. http://www.dhtmlx.com
    You allowed to use this component or parts of it under GPL terms
    To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
    */

    dhtmlXGridObject.prototype.enableSmartRendering=function(mode,buffer,reserved){if (arguments.length>2){if (buffer && !this.rowsBuffer[buffer-1])this.rowsBuffer[buffer-1]=0;buffer=reserved};this._srnd=convertStringToBoolean(mode);this._srdh=this._srdh||20;this._dpref=buffer||0};dhtmlXGridObject.prototype.enablePreRendering=function(buffer){this._srnd_pr=parseInt(buffer||50)};dhtmlXGridObject.prototype.forceFullLoading=function(buffer){buffer=buffer||50;for (var i=0;i<this.rowsBuffer.length;i++)if (!this.rowsBuffer[i]){if (this.callEvent("onDynXLS",[i,buffer])){var self=this;this.load(this.xmlFileUrl+getUrlSymbol(this.xmlFileUrl)+"posStart="+i+"&count="+buffer, function(){window.setTimeout(function(){self.forceFullLoading()},100)}, this._data_type)};return}};dhtmlXGridObject.prototype.setAwaitedRowHeight = function(height) {this._srdh=parseInt(height)};dhtmlXGridObject.prototype._get_view_size=function(){return Math.floor(parseInt(this.entBox.offsetHeight)/this._srdh)+2};dhtmlXGridObject.prototype._add_filler=function(pos,len,fil){if (!len)return null;var id="__filler__";var row=this._prepareRow(id);row.firstChild.style.width="1px";for (var i=1;i<row.childNodes.length;i++)row.childNodes[i].style.display='none';row.firstChild.style.height=len*this._srdh+"px";fil=fil||this.rowsCol[pos];if (fil && fil.nextSibling)fil.parentNode.insertBefore(row,fil.nextSibling);else
     if (_isKHTML)this.obj.appendChild(row);else
     this.obj.rows[0].parentNode.appendChild(row);return [pos,len,row]};dhtmlXGridObject.prototype._update_srnd_view=function(){var min=Math.floor(this.objBox.scrollTop/this._srdh);var max=min+this._get_view_size();if (this.multiLine){var pxHeight = this.objBox.scrollTop;min = 0;while(pxHeight > 0){pxHeight-=this.rowsCol[min]?this.rowsCol[min].offsetHeight:this._srdh;min++};max=min+this._get_view_size();if (min>0)min--};max+=(this._srnd_pr||0);if (max>this.rowsBuffer.length)max=this.rowsBuffer.length;for (var j=min;j<max;j++){if (!this.rowsCol[j]){var res=this._add_from_buffer(j);if (res==-1){if (this.xmlFileUrl){this._current_load=[j,(this._dpref?this._dpref:(max-j))];if (this.callEvent("onDynXLS",[j,this._current_load[1]]))
     this.load(this.xmlFileUrl+getUrlSymbol(this.xmlFileUrl)+"posStart="+j+"&count="+this._current_load[1], this._data_type)};return}else {if (this._tgle){this._updateLine(this._h2.get[this.rowsBuffer[j].idd],this.rowsBuffer[j]);this._updateParentLine(this._h2.get[this.rowsBuffer[j].idd],this.rowsBuffer[j])};if (j && j==(this._realfake?this._fake:this)["_r_select"]){this.selectCell(j, this.cell?this.cell._cellIndex:0, true)}}}}};dhtmlXGridObject.prototype._add_from_buffer=function(ind){var row=this.render_row(ind);if (row==-1)return -1;if (row._attrs["selected"] || row._attrs["select"]){this.selectRow(row,false,true);row._attrs["selected"]=row._attrs["select"]=null};if (!this._cssSP){if (this._cssEven && ind%2 == 0 )row.className=this._cssEven+((row.className.indexOf("rowselected") != -1)?" rowselected ":" ")+(row._css||"");else if (this._cssUnEven && ind%2 == 1 )row.className=this._cssUnEven+((row.className.indexOf("rowselected") != -1)?" rowselected ":" ")+(row._css||"")}else if (this._h2){var x=this._h2.get[row.idd];row.className+=" "+((x.level%2)?(this._cssUnEven+" "+this._cssUnEven):(this._cssEven+" "+this._cssEven))+"_"+x.level+(this.rowsAr[x.id]._css||"")};for (var i=0;i<this._fillers.length;i++){var f=this._fillers[i];if (f && f[0]<=ind && (f[0]+f[1])>ind ){var pos=ind-f[0];if (pos==0){this._insert_before(ind,row,f[2]);this._update_fillers(i,-1,1)}else if (pos == f[1]-1){this._insert_after(ind,row,f[2]);this._update_fillers(i,-1,0)}else {this._fillers.push(this._add_filler(ind+1,f[1]-pos-1,f[2],1));this._insert_after(ind,row,f[2]);this._update_fillers(i,-f[1]+pos,0)};return}}};dhtmlXGridObject.prototype._update_fillers=function(ind,right,left){var f=this._fillers[ind];f[1]=f[1]+right;f[0]=f[0]+left;if (!f[1]){f[2].parentNode.removeChild(f[2]);this._fillers.splice(ind,1)}else 
     f[2].firstChild.style.height=parseFloat(f[2].firstChild.style.height)+right*this._srdh+"px"};dhtmlXGridObject.prototype._insert_before=function(ind,row,fil){fil.parentNode.insertBefore(row,fil);this.rowsCol[ind]=row};dhtmlXGridObject.prototype._insert_after=function(ind,row,fil){if (fil.nextSibling)fil.parentNode.insertBefore(row,fil.nextSibling);else
     fil.parentNode.appendChild(row);this.rowsCol[ind]=row};
    //v.2.0 build 81107

    /*
    Copyright DHTMLX LTD. http://www.dhtmlx.com
    You allowed to use this component or parts of it under GPL terms
    To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
    */