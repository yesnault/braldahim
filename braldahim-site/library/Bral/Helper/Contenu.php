<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Contenu {

	public static function affichePlante($tab) {
		$retour = "";
		if ($tab["possible"] == false) {
			$retour .= "-";
		} else {
			if (array_key_exists("quantite", $tab)) {
				if (array_key_exists("poids", $tab)) {
					$poids = 0;
					if ($tab["quantite"] > 0) {
						$poids = $tab["poids"]/$tab["quantite"];
					}
					$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".$poids." Kg, Poids total : ".$tab["poids"]." Kg'>";
				}
				$retour .= $tab["quantite"]. " ";
				$p = "";
				if (array_key_exists("estPreparee", $tab)) {
					if ($tab["estPreparee"] == true) {
						$p = "_p";
					}
					$retour .= "<img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_partieplante/type_partieplante_".$tab["id_type_partieplante"].$p.".png' alt=\"image\"/>";
				}
				if (array_key_exists("poids", $tab)) {
					$retour .= "</span>";
				}
			}
		}
		return $retour;
	}

	public static function afficheMinerai($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
			if (array_key_exists("poids", $tab)) {
				$poids = 0;
				if ($tab["quantite"] > 0) {
					$poids = $tab["poids"]/$tab["quantite"];
				}
				$retour .= " <span style='cursor:pointer' title='Poids unitaire : ".$poids." Kg, Poids total : ".$tab["poids"]." Kg'>";
			}
			$retour .= $tab["quantite"];
			$p = "";
			if (array_key_exists("estLingot", $tab)) {
				if ($tab["estLingot"] == true) {
					$p = "_p";
				}
			}
			$retour .= " <img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_minerai/type_minerai_".$tab["id_type_minerai"]."$p.png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
			if (array_key_exists("poids", $tab)) {
				$retour .= "</span>";
			}
		}
		return $retour;
	}

	public static function afficheIngredient($tab) {
		$retour = "";
		if (array_key_exists("poids", $tab)) {
			$poids = 0;
			if ($tab["quantite"] > 0) {
				$poids = $tab["poids"]/$tab["quantite"];
			}
			$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".$poids." Kg, Poids total : ".$tab["poids"]." Kg'>";
		}
		$retour .= $tab["quantite"];
		$retour .= "<img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_ingredient/type_ingredient_".$tab["id_type_ingredient"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
		if (array_key_exists("poids", $tab)) {
			$retour .= "</span>";
		}
		return $retour;
	}

	public static function afficheGraine($tab) {
		$retour = "";
		if (array_key_exists("poids", $tab)) {
			$poids = 0;
			if ($tab["quantite"] > 0) {
				$poids = $tab["poids"] / $tab["quantite"];
			}
			$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".$poids." Kg, Poids total : ".$tab["poids"]." Kg'>";
		}
		$s = '';
		if ($tab["quantite"] > 1) {
			$s = "s";
		}
		$retour .= $tab["quantite"]. ' poignée'.$s;
		$retour .= "<br /><img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_graine/type_graine_".$tab["id_type_graine"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
		if (array_key_exists("poids", $tab)) {
			$retour .= "</span>";
		}
		return $retour;
	}

	public static function afficheMunition($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
			$retour .= "<img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_munition/type_munition_".$tab["id_type_munition"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
			if (array_key_exists("poids", $tab)) {
				$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".($tab["poids"]/$tab["quantite"])." Kg, Poids total : ".$tab["poids"]." Kg'>";
			}
			$retour .= $tab["quantite"];
			if (array_key_exists("poids", $tab)) {
				$retour .= "</span>";
			}
		}
		return $retour;
	}

	public static function afficheTabac($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
			$retour .= $tab["quantite"]." ";
			$retour .= "<img src='".Zend_Registry::get('config')->static->url."/styles/braldahim_defaut/images/type_tabac/type_tabac_".$tab["id_type_tabac"].".png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
		} else {
			$retour .= Bral_Helper_ChampBoutique::afficheChampTabac($tab);
		}
		return $retour;
	}
}
