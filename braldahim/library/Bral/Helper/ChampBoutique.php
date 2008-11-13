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
class Bral_Helper_ChampBoutique {
	
	public static function afficheChampPlante($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('bouton_acheterpartieplantes').disabled = true;";
			$js .= "for (i=1; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_acheterpartieplantes').disabled = false;}}";
			
			$retour = "<input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			$retour .= "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaireVente"]." castar";
			if ($tab["prixUnitaireVente"] > 1) $retour.= "s";
			$retour .= "'> [P] </span>";
		} else {
			$retour .= $tab["prixUnitaireVente"];
		}
		return $retour;
	}
	
	public static function afficheChampMinerai($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('bouton_acheterminerais').disabled = true;";
			$js .= "for (i=1; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_acheterminerais').disabled = false;}}";
			
			$retour = "<input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			$retour .= "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaireVente"]." castar";
			if ($tab["prixUnitaireVente"] > 1) $retour.= "s";
			$retour .= "'> ".$tab["prixUnitaireVente"]."c </span>";
		} else {
			$retour .= "Prix Achat: ".$tab["prixUnitaireVente"].'c';
			$retour .= "<br>Prix Reprise: ".$tab["prixUnitaireReprise"].'c';
		}
		return $retour;
	}
}
