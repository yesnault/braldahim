<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Helper_Carnet {

	public static function affiche($braldun) {
		
		$jsBefore = "if ($('loaded_carnet').value == 0) { $('loaded_carnet').value = 1; _get_('/carnet/doaction?caction=do_carnet_voir'); };";
		
		$carnet = "<div id='box_carnet' name='box_carnet'>Chargement en cours</div>";
		
		$retour = "<input type='hidden' id='loaded_carnet' value='0'>";
		$retour .= "<div id='ccarnet' style='display:none'>".Bral_Helper_Tooltip::maketip($carnet, "Carnet", true, false, 400, true, false, "copierTooltipStatic(\"carnet_html\", \"\")")."</div>";
		$retour .= "<span class='alabel' title='Carnet' ".Bral_Helper_Tooltip::jsTipFromDiv("ccarnet", $jsBefore).">";
		$retour .= "<label style='text-decoration:underline;cursor:pointer'>Carnet</label>&nbsp;";
		$retour .= "<img src='/public/images/uddeim/menu_book.gif' alt='Contacts' border='0'>"; 
		$retour .= "</span>";
			
		return $retour;
	}
}
