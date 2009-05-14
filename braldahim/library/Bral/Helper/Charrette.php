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
class Bral_Helper_Charrette {
	
	public static function afficheBarrePoids($transportable, $transporte) {
		$largeur = (($transporte * 100) / $transportable) * 2;
		$titre = "Poids transportable";
		$texte = "La charrette porte actuellement ".$transporte." Kg.<br>";
		$texte .= "Elle peut porter jusqu\'&agrave; ".$transportable." Kg.<br>";
		
		if ($largeur > 200) {
			$largeur = 200;
		}
    	$retour = "<div class='barre_poids' ".Bral_Helper_Tooltip::jsTip($texte, $titre, true).">";
    	$retour .= "<img src='/public/images/barre_poids.gif' height='10px' width=".$largeur."></div>";
		
		return $retour;
    }
}
