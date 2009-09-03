<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Helper_DetailMateriel {

	public static function afficherPrix($e) {
		Zend_Loader::loadClass("Bral_Helper_DetailPrix");
		return Bral_Helper_DetailPrix::afficherPrix($e, "_echoppe_materiel");
	}

	public static function afficherJs($p) {
		return Bral_Helper_Tooltip::jsTip(self::prepareDetail($p));
	}

	public static function afficherTexte($p) {
		return stripslashes(self::prepareDetail($p));
	}

	private static function prepareDetail($e) {
		$text = htmlspecialchars($e["nom"])." <br /><br />";
		$text .= "Num&eacute;ro du mat&eacute;riel : ".$e["id_materiel"]."<br />";
		$text .= "<br />Caract&eacute;ristiques : <br />";
		$text .= self::display("Capacit&eacute;", $e["capacite"]);
		$text .= self::display("Durabilit&eacute;", $e["durabilite"]);
		$text .= self::display("Usure", $e["usure"]);
		$text .= "Poids : ".$e["poids"]. " Kg";
			
		$text .= "<br />";
		return $text;
	}

	private static function display($display, $valeur, $unite = "") {
		if ($valeur != null && $valeur != 0) {
			$plus = "";
			if ($valeur > 0) {
				$plus = "+";
			}
			return $display ." : $plus".$valeur . $unite."<br />";
		} else {
			return null;
		}
	}

	/**
	 * Affiche les recettes des materiels
	 */
	public static function afficheRecette($typeMateriel) {
		$retour = "";
		if (isset($typeMateriel)) {
			$retour .= "<div id='caracs_materiel'>";
			$retour .= "<table align='center'>";
			$retour .= "<th>Capacite</th>";
			$retour .= "<th>Durabilite</th>";
			$retour .= "<th>Usure</th>";
			$retour .= "<th>Poids</th>";
			$retour .= "<tr>";
			$retour .= "<td>".$typeMateriel["capacite"]." </td>";
			$retour .= "<td>".$typeMateriel["durabilite"]." </td>";
			$retour .= "<td>".$typeMateriel["usure"]." </td>";
			$retour .= "<td>".$typeMateriel["poids"]." </td>";
			$retour .= "</tr>";
			$retour .= "</table>";
			$retour .= "</div>";
		}

		return $retour;
	}
}
