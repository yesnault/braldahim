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
			$info = "J'ai une p&ecirc;che extraordinaire ! <br>";
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
			$info1 = "Vous un malus de ".$force_bbdf_hobbit." &agrave; toutes vos caract&eacute;ristiques.<br><br>";
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
		$texte = "Vous avez ".$pv_restant_hobbit." / ".$totalPvSansBm."+".$pv_max_bm_hobbit." PV.<br><br>";
		
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
    
}
