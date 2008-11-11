<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Bpartieplantes.php 612 2008-11-10 22:16:47Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-10 23:16:47 +0100 (Mon, 10 Nov 2008) $
 * $LastChangedRevision: 612 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Helper_ChampBoutiquePlante {
	
	public static function affiche($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('bouton_acheterpartieplantes').disabled = true;";
			$js .= "for (i=1; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_acheterpartieplantes').disabled = false;}}";
			
			$retour = "<input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			$retour .= "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaire"]." castar";
			if ($tab["prixUnitaire"] > 1) $retour.= "s";
			$retour .= "'> [P] </span>";
		} else {
			$retour .= $tab["prixUnitaire"];
		}
		return $retour;
	}
}
