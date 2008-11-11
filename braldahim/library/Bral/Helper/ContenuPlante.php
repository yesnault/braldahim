<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Bpartieplantes.php 612 2008-11-10 22:16:47Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-10 23:16:47 +0100 (Mon, 10 Nov 2008) $
 * $LastChangedRevision: 612 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Helper_ContenuPlante {
	
	public static function affiche($tab) {
		$retour = "";
		if ($tab["possible"] == false) {
	 		$retour .= "-";
		} else {
			if (array_key_exists("quantite", $tab)) {
	 			$retour .= $tab["quantite"];
			} else {
				$retour .= Bral_Helper_ChampBoutiquePlante::affiche($tab);
			}
	 	}
		return $retour;
	}
}
