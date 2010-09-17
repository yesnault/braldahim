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
			$js .= "if (this.value > ".$tab["nbStockRestant"]."){this.value=".$tab["nbStockRestant"]."} ;";
			$js .= "for (i=2; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_acheterpartieplantes').disabled = false;}}";
			
			$retour = "<input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			$retour .= "<span style='cursor:pointer' title='Prix : ".$tab["prixUnitaireVente"]." castar";
			if ($tab["prixUnitaireVente"] > 1) $retour.= "s";
			$retour .= "    Stock restant : ".$tab["nbStockRestant"];
			$retour .= "'> [?] </span>";
		} else {
			$retour .= "<img src='/public/styles/braldahim_defaut/images/type_partieplante/type_partieplante_".$tab["id_type_partieplante"].".png' alt=\"image\"/><br />";
			$retour .= "Prix Achat: ".$tab["prixUnitaireVente"].'c';
			$retour .= "<br />Prix Reprise: ".$tab["prixUnitaireReprise"].'c';
			$retour .= "<br /><span style='cursor:pointer' title='Stock Initial au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Initial: ".$tab["nbStockInitial"]."</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Restant: ".$tab["nbStockRestant"]."</span>";
		}
		return $retour;
	}
	
	public static function afficheChampMinerai($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('bouton_acheterminerais').disabled = true;";
			$js .= "if (this.value > ".$tab["nbStockRestant"]."){this.value=".$tab["nbStockRestant"]."} ;";
			$js .= "for (i=2; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_acheterminerais').disabled = false;}}";
			
			$s = "";
			if ($tab["prixUnitaireVente"] > 1) $s = "s";
			
			$retour = "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaireVente"]." castar".$s."'>";
			$retour .= "Prix ".$tab["prixUnitaireVente"]."c</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant ".$tab["nbStockRestant"]."'>Stock ".$tab["nbStockRestant"]."</span>";
			$retour .= "<br /><input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			
		} else {
			$p = "";
			if ($tab["estLingot"] == true) {
				$p = "_p"; 
			}
			$retour .= "<img src='/public/styles/braldahim_defaut/images/type_minerai/type_minerai_".$tab["id_type_minerai"]."$p.png' alt=\"".htmlspecialchars($tab["type"])."\"/><br />";
			$retour .= "Prix Achat: ".$tab["prixUnitaireVente"].'c';
			$retour .= "<br />Prix Reprise: ".$tab["prixUnitaireReprise"].'c';
			$retour .= "<br /><span style='cursor:pointer' title='Stock Initial au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Initial: ".$tab["nbStockInitial"]."</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Restant: ".$tab["nbStockRestant"]."</span>";
		}
		return $retour;
	}
	
	public static function afficheChampTabac($tab) {
		$retour = "";
		if (array_key_exists("id_champ", $tab)) {
			
			$js = " $('bouton_achetertabac').disabled = true;";
			$js .= "if (this.value > ".$tab["nbStockRestant"]."){this.value=".$tab["nbStockRestant"]."} ;";
			$js .= "for (i=1; i<=$('nb_valeurs').value; i++) { if ($('valeur_' + i).value > 0) { $('bouton_achetertabac').disabled = false;}}";
			
			$s = "";
			if ($tab["prixUnitaireVente"] > 1) $s = "s";
			
			$retour = "<span style='cursor:pointer' title='Prix ".$tab["prixUnitaireVente"]." castar".$s."'>";
			$retour .= "Prix ".$tab["prixUnitaireVente"]."c</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant ".$tab["nbStockRestant"]."'>Stock ".$tab["nbStockRestant"]."</span>";
			$retour .= "<br /><input type='text' maxlength='5' size='2' name='".$tab["id_champ"]."' id='".$tab["id_champ"]."' value='0' ";
			$retour .= "onkeypress=\"chiffres(event);\"  ";
			$retour .= "onkeyup=\"".$js."\">";
			
		} else {
			$retour .= "<img src='/public/styles/braldahim_defaut/images/type_tabac/type_tabac_".$tab["id_type_tabac"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/><br />";
			$retour .= "Prix Achat: ".$tab["prixUnitaireVente"].'c';
			$retour .= "<br /><span style='cursor:pointer' title='Stock Initial au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Initial: ".$tab["nbStockInitial"]."</span>";
			$retour .= "<br /><span style='cursor:pointer' title='Stock Restant au ".Bral_Util_ConvertDate::get_date_mysql_datetime('d/m/y',$tab["dateStock"])."'>Stock Restant: ".$tab["nbStockRestant"]."</span>";
		}
		return $retour;
	}
}
