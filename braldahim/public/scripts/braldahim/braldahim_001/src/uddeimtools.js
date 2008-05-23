
function textCount(field,counterfield,max) {
	if (field.value.length > max) // if too long...trim it!
		field.value = field.value.substring(0, max);
	else
		counterfield.value = max - field.value.length;
}
function wiglwogl(uddeElement) { 
	uddeForm = uddeElement.form; 
	uddeElement = uddeForm.elements[uddeElement.name]; 
	if (uddeElement.length) { 
		bChecked = uddeElement[0].checked; 
		for(i = 1; i < uddeElement.length; i++) {
			uddeElement[i].checked = bChecked; 
		}
	}
} 
function uddeidswap(id) {
	bb = document.getElementById(id);
	if (bb.style.visibility == 'visible') {
		bb.style.visibility = 'hidden';
	} else {
		bb.style.visibility = 'visible';
	}
}

function uddeIMaddToSelection( frmName, srcListName, tgtListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	var tgtList = eval( 'form.' + tgtListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );

	var srcLen = srcList.length;
	var tgtLen = tgtList.length;
	var tgt = "x";

	var idjoin = new Array();
	
	//build array of target items
	for ( var i=tgtLen-1; i > -1; i-- ) {
		tgt += "," + tgtList.options[i].value + ","
	}

	//Pull selected resources and add them to list	
	for ( var i=0; i < srcLen; i++ ) {
		if ( srcList.options[i].selected && tgt.indexOf( "," + srcList.options[i].value + "," ) == -1 ) {
			if ( srcList.options[i].value == 0 || ( tgtLen != 0 && tgtList.options[0].value == 0 ) ) {
				for ( var j = tgtLen-1; j > -1; j-- ) {
					tgtList.options[j] = null;						
				}
			} 
			opt = new Option( srcList.options[i].text, srcList.options[i].value );
			tgtList.options[tgtList.length] = opt;			
		}
	}
	for ( var i=0; i < tgtList.length; i++ ) {
		idjoin[i] = tgtList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function uddeIMremoveFromSelection( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );
	var idjoin = new Array();

	var srcLen = srcList.length;

	for ( var i=srcLen-1; i > -1; i-- ) {
		if ( srcList.options[i].selected ) {
			srcList.options[i] = null;
			break;
		}
	}
	
	for ( var i=0; i < srcList.length; i++ ) {
		idjoin[i] = srcList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function userlistdblclick( sel, frmName, srcListName, tgtListName ) {
	uddeIMaddToSelection( frmName, srcListName, tgtListName );
}
function selectionlistdblclick( sel, frmName, srcListName ) {
	uddeIMremoveFromSelection( frmName, srcListName );
}
