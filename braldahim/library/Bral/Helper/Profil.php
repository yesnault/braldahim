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
class Bral_Helper_Profil {
	
    public static function afficheBarreNiveau($niveau_hobbit, $px_perso_hobbit) {
		$niveauCourantPx = ($niveau_hobbit) * 5;
		$niveauSuivantPx = ($niveau_hobbit + 1) * 5;
		$largeur = (($px_perso_hobbit * 100) / $niveauSuivantPx) * 2;
		$titre = "Exp&eacute;rience atteinte dans ce niveau";
		$texte = "Vous avez ".$px_perso_hobbit. " PX Perso.<br>";
		$texte .= "Pour passer au niveau ".($niveau_hobbit + 1).", il vous faut ";
		$texte .= " ".$niveauSuivantPx. " PX Perso.<br>";
		$texte .= "Il vous manque donc ".($niveauSuivantPx - $px_perso_hobbit)." PX Perso.<br>";
		
		$retour = "<td width='80%' >";
    	$retour .= "	<div class='barre_niveau' ".Bral_Helper_Tooltip::jsTip($texte, $titre, true).">";
    	$retour .= "<img src='/public/images/barre_niveau.gif' height='10px' width=".$largeur."></div>";
		$retour .= "</td>";
		$retour .= "<td width='10%' nowrap>";
		$retour .= intval($largeur/2) ." %";
		$retour .= "</td>";
		
		return $retour;
    }
    
	public static function afficheBarreFaim($balance_faim_hobbit, $force_bbdf_hobbit) {
		
		/*
		[0] -N/2 -> Vous mourez de faim !!!
		[1;10] -N/3 -> Votre estomac crie famine ...
		[11;30] -N/4 -> Votre ventre gargouille ...
		[31;79] 0 -> Tout va pour le mieux.
		[80;94] +N/4 -> Vous êtes en pleine forme !
		[95;100] +N/2 -> Vous avez une pêche extraordinaire !
		*/
		
		if ($balance_faim_hobbit >= 95) {
			$coef = 1;
			$info = "J\\'ai une p&ecirc;che extraordinaire ! <br>";
		} elseif ($balance_faim_hobbit >= 80) {
			$coef = 1;
			$info = "Je suis en pleine forme ! <br>";
		} elseif ($balance_faim_hobbit >= 31) {
			$div = 1;
			$coef = 0;
			$info = "Tout va pour le mieux <br>";
		} elseif ($balance_faim_hobbit >= 11) {
			$coef = -1;
			$info = "Mon ventre gargouille ... <br>";
		} elseif ($balance_faim_hobbit >= 1) {
			$coef = -1;
			$info = "Mon estomac crie famine <br>";
		} elseif ($balance_faim_hobbit < 1) {
			$coef = -1;
			$info = "Je meurs de faim !!! <br>";
		}
		
		if ($coef > 0) {
			$info1 = "Vous b&eacute;n&eacute;ficiez d\\'un bonus de ".$force_bbdf_hobbit." sur toutes vos caract&eacute;ristiques.<br><br>";
		} else if ($coef < 0) {
			$info1 = "Vous avez un malus de ".$force_bbdf_hobbit." &agrave; toutes vos caract&eacute;ristiques.<br><br>";
		} else {
			$info1 = "Aucun bonus ou malus de faim n\\'est ajout&eacute; &agrave; vos caract&eacute;ristiques.<br><br>";
		}
		
		$titre = "Information sur la balance de faim";
		$texte = "Votre balance de faim est à ".$balance_faim_hobbit."% <br>";
		$texte .= $info1.$info;
		
		$retour = "<div class='barre_faim'  ".Bral_Helper_Tooltip::jsTip($texte, $titre, true).">";
		$retour .= "<img src='/public/images/barre_faim.gif' height='10px' width='".(2*$balance_faim_hobbit)."px'></div>";
		
		return $retour;
    }
    
    public static function afficheBarreVie($pv_restant_hobbit, $pv_base, $vigueur_base_hobbit, $pv_max_coef, $pv_max_bm_hobbit, $duree_prochain_tour_hobbit) {
		
    	$totalPvSansBm = $pv_base + $vigueur_base_hobbit * $pv_max_coef;
    	$totalPv = $totalPvSansBm + $pv_max_bm_hobbit;
    	
    	$titre = "Information sur les points de vie";
    	$plus = "";
    	if ($pv_max_bm_hobbit >= 0) { 
    		$plus = "+"; 
    	}
		$texte = "Vous avez ".$pv_restant_hobbit." / ".$totalPv ." (".$totalPvSansBm." ".$plus." ".$pv_max_bm_hobbit.") PV.<br><br>";
		
		$pourcentage = $pv_restant_hobbit * 100 / $totalPv;
		
		/*
		81-100% -> "J'ai une de ces patates moi !"
		61-80% -> "J'ai les jambes lourdes moi aujourd'hui ..."
		41-60% -> "Mon bras droit ne répond plus ! Aaaarg il est par terre !!!"
		21-40% -> "Je crois qu'il est temps que nous parlementions, qu'en pensez vous ?"
		0 -20% -> "Mais pourquoi vous prenez ma taille ? et c'est quoi toutes ces planches ?"
    	*/
		
		if ($pourcentage >= 81) {
			$info = "J\\'ai une de ces patates moi !";
		} else if ($pourcentage >= 61) {
			$info = "J\\'ai les jambes lourdes moi aujourd\\'hui ...";
		} else if ($pourcentage >= 41) {
			$info = "Mon bras droit ne r&eacute;pond plus ! Aaaarg il est par terre !!!";
		} else if ($pourcentage >= 21) {
			$info = "Je crois qu\\'il est temps que nous parlementions, qu\\'en pensez vous ?";
		} else {
			$info = "Mais pourquoi vous prenez ma taille ? et c\\'est quoi toutes ces planches ?";
		}
		
		if ($pourcentage < 100) {
			$minutesCourant = Bral_Util_ConvertDate::getMinuteFromHeure($duree_prochain_tour_hobbit);// - 10 * $this->hobbit->sagesse_base_hobbit;
			$minutesAAjouter = floor($minutesCourant / (4 * $totalPvSansBm)) * ($totalPvSansBm - $pv_restant_hobbit);
			$s = '';
			if ($minutesAAjouter > 1) $s = 's';
			$info .= "<br><br>Votre prochaine DLA sera allongée de ".$minutesAAjouter." minute".$s.".";
		}
		
		$texte .= $info;
		
		$retour = "<div class='barre_vie'  ".Bral_Helper_Tooltip::jsTip($texte, $titre, true).">";
		$retour .= "<img src='/public/images/barre_vie.gif' height='10px' width='".(2*floor($pv_restant_hobbit*100)/($pv_base + ($vigueur_base_hobbit * $pv_max_coef) + $pv_max_bm_hobbit))."px'></div>";
		
		return $retour;
    }
    
    
     public static function afficheBarreTour($hobbit) {
     	$retour = "";
     	
     	$texte = "";
     	$titre = "Avancement et informations";
     	
     	$texte .= " Position tour courant : ".$hobbit->nom_tour."<br><br>";
     	
     	$texte .= " Durée du tour : ".$hobbit->duree_courant_tour_hobbit."<br>";
     	$texte .= " Position dans le tour : ".$hobbit->nom_tour."<br><br>";

     	$texte .= " Début tour : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$hobbit->date_debut_tour_hobbit)."<br>";
     	$texte .= " Fin Latence : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$hobbit->date_fin_latence_hobbit)."<br>";
     	$texte .= " Début Cumul : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$hobbit->date_debut_cumul_hobbit)."<br>";
     	$texte .= " Date limite d\\'action : ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$hobbit->date_fin_tour_hobbit)."<br><br>";
     	
     	
     	$date_courante = date("Y-m-d H:i:s");
     	$time_date_courante = Bral_Util_ConvertDate::get_epoch_mysql_datetime(date("Y-m-d H:i:s"));
     	
     	$width_latence = "0";
     	$width_milieu = "0";
     	$width_cumul = "0";
     	
     	$pourcent_latence = 0;
     	$pourcent_milieu = 0;
     	$pourcent_cumul = 0;
     	
     	if ($date_courante <= $hobbit->date_fin_latence_hobbit) {
     		$time_debut_tour = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_debut_tour_hobbit);
     		$time_fin_latence = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_fin_latence_hobbit);
     		$ecartTotal = $time_fin_latence - $time_debut_tour;
     		$ecart = $time_fin_latence - $time_date_courante; 
     		
     		$pourcent = 100 - ($ecart * 100 / $ecartTotal);
     		$width_latence = ($pourcent * 33 / 100 ) * 2; // latence : 33% du total , x2 pour la taille css
     		$pourcent_latence = substr($pourcent, 0, 5);
     	} else if ($date_courante <= $hobbit->date_debut_cumul_hobbit) {
     		$width_latence = "66";
     		
     		$time_fin_latence = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_fin_latence_hobbit);
     		$time_debut_cumul = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_debut_cumul_hobbit);
     		$ecartTotal = $time_debut_cumul - $time_fin_latence;
     		$ecart = $time_debut_cumul - $time_date_courante; 
     		
     		$pourcent = 100 - ($ecart * 100 / $ecartTotal);
     		$width_milieu = ($pourcent * 17 / 100) * 2; // milieu : 17 % du total , x2 pour la taille css
     		$pourcent_milieu = substr($pourcent, 0, 5);
     		$pourcent_latence = 100;
     	} else { // CUMUL
     		$pourcent_latence = 100;
     		$pourcent_milieu = 100;
     		$width_latence = "66";
     		$width_milieu = "34";
     		
     		$time_fin_tour = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_fin_tour_hobbit);
     		$time_debut_cumul = Bral_Util_ConvertDate::get_epoch_mysql_datetime($hobbit->date_debut_cumul_hobbit);
     		$ecartTotal = $time_fin_tour - $time_debut_cumul;
     		$ecart = $time_fin_tour - $time_date_courante; 
     		
     		$pourcent = 100 - ($ecart * 100 / $ecartTotal);
     		$width_cumul = ($pourcent * 50 / 100) * 2; // cumul, 50 % du total, x2 pour la taille css
     		$pourcent_cumul = substr($pourcent, 0, 5);
     		if ($width_cumul > 100) {
     			$width_cumul = 100;
     		}
     	}
     	
     	$section_cumul = "Section survolée : Cumul, termin&eacute;e &agrave; ".$pourcent_cumul." %<br><br>";
     	$section_milieu = "Section survolée : Milieu, termin&eacute;e &agrave; ".$pourcent_milieu." %<br><br>";
     	$section_latence = "Section survolée : Latence, termin&eacute;e &agrave; ".$pourcent_latence." %<br><br>";
     	
     	$retour .= "<table border='0' margin='0' cellspacing='0' cellpadding='0'><tr>";
     	$retour .= "<td>";
     	$retour .= "<div class='barre_tour_latence'  ".Bral_Helper_Tooltip::jsTip($section_latence.$texte, $titre, true).">";
		$retour .= "<img src='/public/images/barre_tour_latence.gif' height='10px' width='".$width_latence."px'></div>";
		$retour .= "</td>";
		$retour .= "<td>";
     	$retour .= "<div class='barre_tour_milieu'  ".Bral_Helper_Tooltip::jsTip($section_milieu.$texte, $titre, true).">";
		$retour .= "<img src='/public/images/barre_tour_milieu.gif' height='10px' width='".$width_milieu."px'></div>";
		$retour .= "</td>";
		$retour .= "<td>";
     	$retour .= "<div class='barre_tour_cumul'  ".Bral_Helper_Tooltip::jsTip($section_cumul.$texte, $titre, true).">";
		$retour .= "<img src='/public/images/barre_tour_cumul.gif' height='10px' width='".$width_cumul."px'></div>";
		$retour .= "</td>";
		$retour .= "</tr></table>";
     	
     	
     	return $retour;
     }
}
