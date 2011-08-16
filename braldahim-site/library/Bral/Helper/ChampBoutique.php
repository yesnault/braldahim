<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_ChampBoutique {
	
	public static function afficheChampTabac($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('#bouton_achetertabac').attr('disabled', true);";
			$js .= "if (this.value > ".$tab["nbStockRestant"].") { this.value=".$tab["nbStockRestant"]."};";
			$js .= "for (i=1; i<=$('#nb_valeurs-achetertabac').val(); i++) { if ($('#valeur_' + i + '-achetertabac').val() > 0) { $('#bouton_achetertabac').attr('disabled', false);}}";
			
			$s = "";
			if ($tab["prixUnitaireVente"] > 1) $s = "s";
			
			$retour .= "<img src='".Zend_Registry::get('config')->url->static."/images/type_tabac/type_tabac_".$tab["id_type_tabac"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/><br />";
			$retour .= "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaireVente"]." castar".$s."'>";
			$retour .= "Prix ".$tab["prixUnitaireVente"]."c</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant ".$tab["nbStockRestant"]."'>Stock ".$tab["nbStockRestant"]."</span>";
			$retour .= "<br /><input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."-achetertabac' id='".$tab["id_champ"]."-achetertabac' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			
		} else {
			$retour .= "<img src='".Zend_Registry::get('config')->url->static."/images/type_tabac/type_tabac_".$tab["id_type_tabac"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/><br />";
			$retour .= "Prix Achat: ".$tab["prixUnitaireVente"].'c';
			$retour .= "<br /><span style='cursor:pointer' title='Stock Initial au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Initial: ".$tab["nbStockInitial"]."</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Restant: ".$tab["nbStockRestant"]."</span>";
		}
		return $retour;
	}
}
