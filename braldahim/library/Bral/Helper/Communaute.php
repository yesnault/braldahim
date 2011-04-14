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
		$details = 'Niveau 1 : Permet de récolter dans les champs des autres Braldûns de la communauté<br />';
		$details .= 'Niveau 2 : Permet d\'entretenir les champs des autres Braldûns de la communauté<br />';
		$details .= 'Niveau 3 : Permet de semer dans les champs des autres Braldûns de la communauté<br />';
		$retour = self::getNiveauTexte($texte, "un grenier", "Grenier", $niveau, $details);
		return $retour;
	}

	public static function afficheNiveauBaraquement($niveau, $texte = null) {
		$details = 'Niveau 1 : Permet de placer une Académie<br />';
		$details .= 'Niveau 2 : Permet d\'avoir un état des Braldûns (position / Niv. / métier)<br />';
		$details .= 'Niveau 3 : Permet d\'avoir un état des Braldûns (PV / DLA)<br />';
		$details .= 'Niveau 4 : Permet d\'avoir un état des Braldûns (PA / BM)<br />';
		$retour = self::getNiveauTexte($texte, "des baraquements", "Baraquements", $niveau, $details);
		return $retour;
	}

	public static function afficheNiveauAtelier($niveau, $texte = null) {
		$details = 'Niveau 1 : Permet de placer un Assembleur<br />';
		$details .= 'Niveau 2 : Permet de placer un Joaillier<br />';
		$details .= 'Niveau 3 : Permet de rechercher des mots runiques sur l\'atelier, en prenant toutes les runes du coffre de Communauté<br />';
		$retour = self::getNiveauTexte($texte, "un atelier", "Atelier", $niveau, $details);
		return $retour;
	}

	public static function afficheNiveauTribune($niveau, $texte = null) {
		$details = 'Niveau 1 : Permet de placer une gare<br />';
		$details .= 'Niveau 2 : Permet de placer un Office Notarial<br />';
		$details .= 'Niveau 3 : Permet d\'obtenir une CSS personnalisée pour les Braldûns de la Communauté<br />';
		$retour = self::getNiveauTexte($texte, "une tribune", "Tribune", $niveau, $details);
		return $retour;
	}

	public static function afficheNiveauMarche($niveau, $texte = null) {
		$details = 'Niveau 1 : Permet de placer une banque autour du Hall<br />';
		$details .= 'Niveau 2 : Permet de placer un Hôtel des Ventes autour du Hall<br />';
		$details .= 'Niveau 3 : Permet d\'effectuer des ordres Coffre perso <-> Coffre commun à distance<br />';
		$details .= 'Niveau 4 : Permet d\'effectuer des ordres Coffre perso -> autres coffres de Braldûns<br />';
		$details .= 'Niveau 5 : Permet d\'effecter des ordres Coffre commun -> Hôtel des Ventes<br />';
		$details .= "<br />Les effets sur les Bâtiments en construction ne sont pas actifs.";
		$retour = self::getNiveauTexte($texte, "un marché couvert", "Marché couvert", $niveau, $details);
		return $retour;
	}

	private static function getNiveauTexte($texte, $mot, $titre, $niveau, $details) {
		$retour = "";
		if ($texte == null) {
			$retour .= "<div >";
			if ($niveau == Bral_Util_Communaute::NIVEAU_EN_CONSTRUCTION) {
				$retour .= "Votre communauté possède ".$mot." <div class='braltip alabel' style='display:inline'>en construction.";
			} else {
				$retour .= "Votre communauté possède ".$mot." de <div class='braltip alabel' style='display:inline'>niveau ".$niveau.'.';
			}
			$retour .= Bral_Helper_Tooltip::render($details, $titre);
			$retour .= '</div>';
		} else {
			$retour .= "<div class='braltip alabel' style='display:inline'>";
			$retour .= $texte;
			$retour .= Bral_Helper_Tooltip::render($details, $titre);
		}
		$retour .= '</div>';
		return $retour;
	}

}
