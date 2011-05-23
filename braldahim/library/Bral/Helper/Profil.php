<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Profil {

	const COEF_TAILLE = 2;
	const COEF_TAILLE_COCKPIT = 1;
	const COEF_TAILLE_MOBILE = 1.5;

	public static function afficheBarreNiveau($niveau_braldun, $px_perso_braldun) {
		$niveauCourantPx = ($niveau_braldun) * 5;
		$niveauSuivantPx = ($niveau_braldun + 1) * 5;
		$pourcentage = (($px_perso_braldun * 100) / $niveauSuivantPx);
		$titre = "Exp&eacute;rience atteinte dans ce niveau";
		$s = "";
		if ($px_perso_braldun > 1) {
			$s = "s";
		}
		$texte = "Vous avez ".$px_perso_braldun. " PX Perso".$s.".<br />";
		$texte .= "Pour passer au niveau ".($niveau_braldun + 1).", il vous faut ";

		if (($niveauSuivantPx - $px_perso_braldun) > 0) {
			$s = "";
			if (($niveauSuivantPx) > 1) {
				$s = "s";
			}
			$texte .= " ".$niveauSuivantPx. " PX Perso".$s.".<br />";
			$s = "";
			if (($niveauSuivantPx - $px_perso_braldun) > 1) {
				$s = "s";
			}
			$texte .= "Il vous manque donc ".($niveauSuivantPx - $px_perso_braldun)." PX Perso".$s.".<br />";
		} else {
			$texte .= " 0 PX Perso.<br />";
			$texte .= "Vous allez changer de niveau &agrave; la prochaine action.<br />";
		}

		$largeur = $pourcentage;

		if ($largeur > 100) {
			$largeur = 100;
		}

		if (Zend_Registry::get("estMobile")) {
			$largeur = $largeur * self::COEF_TAILLE_MOBILE;
		} else {
			$largeur = $largeur * self::COEF_TAILLE;
		}

		$retour = "<td width='80%' align='center'>";
		$retour .= "<div class='barre_niveau braltip'><div class='barre_img img_barre_niveau' style='width:".$largeur."px'>".Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "</div></div>";
		$retour .= "</td>";
		$retour .= "<td width='10%' nowrap>";
		$retour .= intval($pourcentage) ." %";
		$retour .= "</td>";

		return $retour;
	}

	public static function afficheBarreFaim($balance_faim_braldun, $force_bbdf_braldun, $cockpit = false) {

		/*
		 [0] -N/2 -> Vous mourez de faim !!!
		 [1;10] -N/3 -> Votre estomac crie famine ...
		 [11;30] -N/4 -> Votre ventre gargouille ...
		 [31;79] 0 -> Tout va pour le mieux.
		 [80;94] +N/4 -> Vous Ãªtes en pleine forme !
		 [95;100] +N/2 -> Vous avez une pÃªche extraordinaire !
		 */

		if ($balance_faim_braldun > 100) {
			$balance_faim_braldun = 100;
		}

		$info = "&quot;";
		if ($balance_faim_braldun >= 95) {
			$coef = 1;
			$info .= "J'ai une p&ecirc;che extraordinaire !";
		} elseif ($balance_faim_braldun >= 80) {
			$coef = 1;
			$info .= "Je suis en pleine forme !";
		} elseif ($balance_faim_braldun >= 31) {
			$div = 1;
			$coef = 0;
			$info .= "Tout va pour le mieux";
		} elseif ($balance_faim_braldun >= 11) {
			$coef = -1;
			$info .= "Mon ventre gargouille ...";
		} elseif ($balance_faim_braldun >= 1) {
			$coef = -1;
			$info .= "Mon estomac crie famine";
		} elseif ($balance_faim_braldun < 1) {
			$coef = -1;
			$info .= "Je meurs de faim !!!";
		}

		$info .= "&quot;<br />";

		if ($coef > 0) {
			$info1 = "Vous b&eacute;n&eacute;ficiez d'un bonus de ".$force_bbdf_braldun." sur toutes vos caract&eacute;ristiques.<br /><br />";
		} else if ($coef < 0) {
			$info1 = "Vous avez un malus de ".$force_bbdf_braldun." &agrave; toutes vos caract&eacute;ristiques.<br /><br />";
		} else {
			$info1 = "Aucun bonus ou malus de faim n'est ajout&eacute; &agrave; vos caract&eacute;ristiques.<br /><br />";
		}

		$titre = "Information sur la balance de faim";
		$texte = "Votre balance de faim est &agrave;  ".$balance_faim_braldun."%.<br />";
		$texte .= $info1.$info;

		if (Zend_Registry::get("estMobile")) {
			$largeur = $balance_faim_braldun * self::COEF_TAILLE_MOBILE;
		} else if ($cockpit) {
			$largeur = $balance_faim_braldun * self::COEF_TAILLE_COCKPIT;
		} else {
			$largeur = $balance_faim_braldun * self::COEF_TAILLE;
		}

		$suffixe = "";
		if ($cockpit) {
			$suffixe = "_cockpit";
		}

		$retour = "<div class='barre_faim".$suffixe." braltip'><div class='barre_img img_barre_faim' style='width:".$largeur."px'>".Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "</div></div>";

		return $retour;
	}

	public static function afficheBarreVie($pv_restant_braldun, $pv_base, $vigueur_base_braldun, $pv_max_coef, $pv_max_bm_braldun, $duree_prochain_tour_braldun, $pourCommunaute = false, $cockpit = false) {

		$totalPvSansBm = $pv_base + $vigueur_base_braldun * $pv_max_coef;
		$totalPv = $totalPvSansBm + $pv_max_bm_braldun;
			
		$titre = "Information sur les points de vie";
		$plus = "";
		if ($pv_max_bm_braldun >= 0) {
			$plus = "+";
		}

		if ($pourCommunaute) {
			$texte = "Reste : ".$pv_restant_braldun." / ".$totalPv ." (".$totalPvSansBm." ".$plus." ".$pv_max_bm_braldun.") PV.<br /><br />";
		} else {
			$texte = "Vous avez ".$pv_restant_braldun." / ".$totalPv ." (".$totalPvSansBm." ".$plus." ".$pv_max_bm_braldun.") PV.<br /><br />";
		}

		$pourcentage = $pv_restant_braldun * 100 / $totalPv;

		/*
		 81-100% -> "J'ai une de ces patates moi !"
		 61-80% -> "J'ai les jambes lourdes moi aujourd'hui ..."
		 41-60% -> "Mon bras droit ne rÃ©pond plus ! Aaaarg il est par terre !!!"
		 21-40% -> "Je crois qu'il est temps que nous parlementions, qu'en pensez vous ?"
		 0 -20% -> "Mais pourquoi vous prenez ma taille ? et c'est quoi toutes ces planches ?"
		 */

		if ($pourcentage >= 81) {
			$info = "J'ai une de ces patates moi !";
		} else if ($pourcentage >= 61) {
			$info = "J'ai les jambes lourdes moi aujourd'hui ...";
		} else if ($pourcentage >= 41) {
			$info = "Mon bras droit ne r&eacute;pond plus ! Aaaarg il est par terre !!!";
		} else if ($pourcentage >= 21) {
			$info = "Je crois qu'il est temps que nous parlementions, qu'en pensez vous ?";
		} else {
			$info = "Mais pourquoi vous prenez ma taille ? et c'est quoi toutes ces planches ?";
		}

		if ($pourCommunaute) {
			$info = "Sa petite pensée : <br />".$info;
		}

		if ($pourcentage < 100) {
			$minutesCourant = Bral_Util_ConvertDate::getMinuteFromHeure($duree_prochain_tour_braldun);// - 10 * $this->braldun->sagesse_base_braldun;
			$minutesAAjouter = floor($minutesCourant / (4 * $totalPvSansBm)) * ($totalPvSansBm - $pv_restant_braldun);
			$s = '';
			if ($minutesAAjouter > 1) $s = 's';
			$info .= "<br /><br />Votre prochaine DLA sera allong&eacute;e de ".$minutesAAjouter." minute".$s.".";
		}

		$texte .= $info;

		if (Zend_Registry::get("estMobile")) {
			$largeur = self::COEF_TAILLE_MOBILE * floor($pv_restant_braldun*100)/($pv_base + ($vigueur_base_braldun * $pv_max_coef) + $pv_max_bm_braldun);
		} else if ($cockpit) {
			$largeur = self::COEF_TAILLE_COCKPIT * floor($pv_restant_braldun*100)/($pv_base + ($vigueur_base_braldun * $pv_max_coef) + $pv_max_bm_braldun);
		} else {
			$largeur = self::COEF_TAILLE * floor($pv_restant_braldun*100)/($pv_base + ($vigueur_base_braldun * $pv_max_coef) + $pv_max_bm_braldun);
		}

		$suffixe = "";
		if ($pourCommunaute) {
			$largeur = $largeur * 0.25;
			$suffixe = "_communaute";
		} elseif ($cockpit) {
			$suffixe = "_cockpit";
		}

		$retour = "<div class='barre_vie".$suffixe." braltip'><div class='barre_img img_barre_vie' style='width:".$largeur."px'>".Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "</div></div>";

		return $retour;
	}


	public static function afficheBarreTour($braldun) {
		$retour = "";

		$texte = "";
		$titre = "Avancement et informations";
		$suffixeCss = "";
		$coefBase = 1;

		$c = "stdClass";
		if ($braldun instanceof $c) {

			$nomsTour = Zend_Registry::get('nomsTour');
			$nom_tour = $nomsTour[$braldun->tour_position_braldun];

			$texte .= " Position tour courant : ".$nom_tour."<br /><br />";
			$texte .= " Dur&eacute;e du tour : ".$braldun->duree_courant_tour_braldun."<br />";

			$dateDebutTourBraldun = $braldun->date_debut_tour_braldun;
			$dateFinLatenceBraldun = $braldun->date_fin_latence_braldun;
			$dateDebutCumulBraldun = $braldun->date_debut_cumul_braldun;
			$dateFinTourBraldun = $braldun->date_fin_tour_braldun;
		} else {
			$texte .= " Dur&eacute;e du tour : ".$braldun["duree_courant_tour_braldun"]."<br />";

			$dateDebutTourBraldun = $braldun["date_debut_tour_braldun"];
			$dateFinLatenceBraldun = $braldun["date_fin_latence_braldun"];
			$dateDebutCumulBraldun = $braldun["date_debut_cumul_braldun"];
			$dateFinTourBraldun = $braldun["date_fin_tour_braldun"];

			$suffixeCss = "_communaute";
			$coefBase = 0.25;
		}

		$texte .= " D&eacute;but tour : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y', $dateDebutTourBraldun)."<br />";
		$texte .= " Fin Sommeil : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y', $dateFinLatenceBraldun)."<br />";
		$texte .= " D&eacute;but Activit&eacute; : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y', $dateDebutCumulBraldun)."<br />";
		$texte .= " Date limite d'action : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y', $dateFinTourBraldun)."<br /><br />";

		$date_courante = date("Y-m-d H:i:s");
		$time_date_courante = Bral_Util_ConvertDate::get_epoch_mysql_datetime(date("Y-m-d H:i:s"));

		$width_latence = "0";
		$width_milieu = "0";
		$width_cumul = "0";

		$pourcent_latence = 0;
		$pourcent_milieu = 0;
		$pourcent_cumul = 0;

		if (Zend_Registry::get("estMobile")) {
			$coef = self::COEF_TAILLE_MOBILE;
		} else {
			$coef = self::COEF_TAILLE;
		}

		$coef = $coef * $coefBase;

		if ($date_courante <= $dateFinLatenceBraldun) {
			$time_debut_tour = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateDebutTourBraldun);
			$time_fin_latence = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateFinLatenceBraldun);
			$ecartTotal = $time_fin_latence - $time_debut_tour;
			$ecart = $time_fin_latence - $time_date_courante;

			$pourcent = 100 - ($ecart * 100 / $ecartTotal);
			$width_latence = ($pourcent * 25 / 100 ) * $coef; // latence : 25% du total , x2 pour la taille css
			$pourcent_latence = substr($pourcent, 0, 5);
		} else if ($date_courante <= $dateDebutCumulBraldun) {
			$width_latence = 25 * $coef;

			$time_fin_latence = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateFinLatenceBraldun);
			$time_debut_cumul = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateDebutCumulBraldun);
			$ecartTotal = $time_debut_cumul - $time_fin_latence;
			$ecart = $time_debut_cumul - $time_date_courante;

			$pourcent = 100 - ($ecart * 100 / $ecartTotal);
			$width_milieu = ($pourcent * 25 / 100) * $coef; // milieu : 25 % du total , x2 pour la taille css
			$pourcent_milieu = substr($pourcent, 0, 5);
			$pourcent_latence = 100;
		} else { // CUMUL
			$pourcent_latence = 100;
			$pourcent_milieu = 100;
			$width_latence = 25 * $coef;
			$width_milieu = 25 * $coef;

			$time_fin_tour = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateFinTourBraldun);
			$time_debut_cumul = Bral_Util_ConvertDate::get_epoch_mysql_datetime($dateDebutCumulBraldun);
			$ecartTotal = $time_fin_tour - $time_debut_cumul;
			$ecart = $time_fin_tour - $time_date_courante;

			$pourcent = 100 - ($ecart * 100 / $ecartTotal);
			$width_cumul = ($pourcent * 50 / 100) * $coef; // cumul, 50 % du total, x2 pour la taille css
			$pourcent_cumul = substr($pourcent, 0, 5);
			if ($width_cumul > 100 * $coefBase) {
				$width_cumul = 100 * $coefBase;
			}
		}

		$section_cumul = "Section survol&eacute;e : Activit&eacute;, termin&eacute;e &agrave; ".$pourcent_cumul." %<br /><br />";
		$section_milieu = "Section survol&eacute;e : &Eacute;veil, termin&eacute;e &agrave; ".$pourcent_milieu." %<br /><br />";
		$section_latence = "Section survol&eacute;e : Sommeil, termin&eacute;e &agrave; ".$pourcent_latence." %<br /><br />";

		$retour .= "<table border='0' margin='0' cellspacing='0' cellpadding='0' align='center' style='margin-left: auto; margin-right: auto;'><tr>";
		$retour .= "<td class='barre_tour_sommeil".$suffixeCss."'>";
		$retour .= "<div class='braltip'><div class='barre_img img_tour_sommeil' style='width:".$width_latence."px'>".Bral_Helper_Tooltip::render($section_latence.$texte, $titre);
		$retour .= "</div></div>";
		$retour .= "</td>";
		$retour .= "<td class='barre_tour_eveil".$suffixeCss."'>";
		$retour .= "<div class='braltip'><div class='barre_img img_tour_eveil' style='width:".$width_milieu."px'>".Bral_Helper_Tooltip::render($section_milieu.$texte, $titre);
		$retour .= "</div></div>";
		$retour .= "</td>";
		$retour .= "<td class='barre_tour_activite".$suffixeCss."'>";
		$retour .= "<div class='braltip'><div class='barre_img img_tour_activite' style='width:".$width_cumul."px'>".Bral_Helper_Tooltip::render($section_cumul.$texte, $titre);
		$retour .= "</div></div>";
		$retour .= "</td>";
		$retour .= "</tr></table>";

		return $retour;
	}

	public static function afficheBarrePoids($transportable, $transporte) {
		$largeur = (($transporte * 100) / $transportable);
		$titre = "Poids transportable";
		$texte = "Vous portez actuellement ".round($transporte, 3)." Kg.<br />";
		$texte .= "Vous pouvez porter jusqu'&agrave; ".round($transportable, 3)." Kg.<br />";

		if ($largeur > 100) {
			$largeur = 100;
		}

		if (Zend_Registry::get("estMobile")) {
			$largeur = $largeur * self::COEF_TAILLE_MOBILE;
		} else {
			$largeur = $largeur * self::COEF_TAILLE;
		}

		$retour = "<div class='barre_poids braltip'><div class='barre_img img_barre_poids' style='width:".$largeur."px'>".Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "</div></div>";

		return $retour;
	}
}
