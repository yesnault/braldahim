<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Communaute {

	const COEF_TAILLE = 1;
	const COEF_TAILLE_MOBILE = 0.5;

	public static function afficheBarreConstruction($total, $enCours) {

		$largeur = (($enCours * 100) / $total);

		if ($largeur > 100) {
			$largeur = 100;
		}

		if (Zend_Registry::get("estMobile")) {
			$largeur = $largeur * self::COEF_TAILLE_MOBILE;
		} else {
			$largeur = $largeur * self::COEF_TAILLE;
		}

		$retour = "<div class='barre_entretien'><div class='barre_img img_barre_entretien' style='width:".$largeur."px'>";
		$retour .= "</div></div>";

		return $retour;
	}

	public static function afficheNiveauGrenier($niveau, $texte = null) {
		$retour = "";

		$details = 'Niveau 1 : Permet de récolter dans les champs des autres Braldûns de la communauté<br />';
		$details .= 'Niveau 2 : Permet d\'entretenir les champs des autres Braldûns de la communauté<br />';
		$details .= 'Niveau 3 : Permet de semer dans les champs des autres Braldûns de la communauté<br />';

		if ($texte == null) {
			$retour .= "<div >";
			$retour .= "Votre communauté possède un grenier de <div class='braltip alabel' style='display:inline'>niveau ".$niveau.'.';
			$retour .= Bral_Helper_Tooltip::render($details, 'Grenier');
			$retour .= '</div></div>';
		} else {
			$retour .= "<div class='braltip alabel' style='display:inline'>";
			$retour .= $texte;
			$retour .= Bral_Helper_Tooltip::render($details, 'Grenier');
			$retour .= '</div>';
		}

		return $retour;
	}

	public static function afficheNiveauBaraquement($niveau, $texte = null) {
		$retour = "";

		$details = 'Niveau 1 : Permet de placer une Académie<br />';
		$details .= 'Niveau 2 : Permet d\'avoir un état des Braldûns (position / Niv. / métier)<br />';
		$details .= 'Niveau 3 : Permet d\'avoir un état des Braldûns (PV / DLA)<br />';
		$details .= 'Niveau 4 : Permet d\'avoir un état des Braldûns (PA / BM)<br />';

		if ($texte == null) {
			$retour .= "<div >";
			$retour .= "Votre communauté possède des baraquements de <div class='braltip alabel' style='display:inline'>niveau ".$niveau.'.';
			$retour .= Bral_Helper_Tooltip::render($details, 'Baraquements');
			$retour .= '</div></div>';
		} else {
			$retour .= "<div class='braltip alabel' style='display:inline'>";
			$retour .= $texte;
			$retour .= Bral_Helper_Tooltip::render($details, 'Baraquements');
			$retour .= '</div>';
		}

		return $retour;
	}

}
