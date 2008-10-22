<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Helper_Image {

 	public static function afficherAvatar($image) {
 		if ($image != "" && $image != null && $image != "http://") {
 			return "<img src='".$image."' alt='[avatar]' width='110px' height='110px' />";
 		} else {
 			return "";
 		}
 	}
 	
 	public static function afficherBlason($image) {
 		if ($image != "" && $image != null && $image != "http://") {
 			return "<img src='".$image."' alt='[blason]' width='300px' height='400px' />";
 		} else {
 			return "";
 		}
 	}
 	
 	public static function afficherErreur() {
 		$retour = "<br /><div class='message_erreur' id='message_erreur'> 
		L'image ou l'url rentr&eacute;e pr&eacute;c&eacute;demment est invalide.<br/>
		 Veuillez v&eacute;rifier la 
		taille de l'image, son accessibilit&eacute; depuis l'adresse que vous avez rentr&eacute;e et son type (gif, png ou jpg).
		</div><br />";
		
		return $retour;
 	}
}