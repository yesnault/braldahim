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
class Bral_Helper_DetailPotion
{

	public static function afficherPrix($e)
	{
		Zend_Loader::loadClass("Bral_Helper_DetailPrix");
		return Bral_Helper_DetailPrix::afficherPrix($e, "_echoppe_potion");
	}

	public static function afficher($p)
	{
		return "<div class='braltip'>" . self::afficherTooltip($p) . "<span>" . htmlspecialchars($p["nom"]) . ", n&deg;" . $p["id_potion"] . "</span></div>";
	}

	public static function afficherTooltip($p)
	{
		$text = htmlspecialchars($p["nom"]) . " de qualit&eacute; " . htmlspecialchars($p["qualite"]) . "<br />";

		$text .= "<label class='alabel' onclick=ouvHistoP(" . $p["id_potion"] . ")>Voir l'historique</label><br />";

		$text .= "<br />";
		$text .= $p["nom_type"] . " n&deg; " . $p["id_potion"] . "<br />";
		$text .= "Niveau : " . $p["niveau"] . "<br />";
		$text .= "Poids : " . Bral_Util_Poids::POIDS_POTION . " Kg<br />";
		if ($p["bm_type"] != null) {
			$text .= "<br /> Apporte un " . $p["bm_type"];
            $text .= " ".Bral_Util_Potion::determineBonusMalusVernis ( $p["caracteristique"], $p["bm_type"], $p );
			$text .= " sur la caract&eacute;ristique " . $p["caracteristique"];
			if ($p["bm2_type"] != null) {
				$text .= "<br /> et un " . $p["bm2_type"];
                $text .= " ".Bral_Util_Potion::determineBonusMalusVernis ( $p["caracteristique2"], $p["bm2_type"], $p );
				$text .= " sur la caract&eacute;ristique " . $p["caracteristique2"];
			}
			$text .= ".<br />";
		}
		if ($p["bm2_type"] != null || $p["bm_type"] == null) {
			$text .= "<br />Ce vernis est à appliquer sur une pièce d'équipement.";
		}
		$text .= "<br />";

		return Bral_Helper_Tooltip::render($text);
	}
}