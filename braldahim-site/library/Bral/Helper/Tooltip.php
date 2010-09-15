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
class Bral_Helper_Tooltip {
	
	public static function render($texte, $titre = null, $boutonCopier = null) {
		$retour = "<span class='tip'>";
		if ($titre != null) {
			$retour .= "".$titre."<hr />";
		}
		$retour .= $texte;
		//$retour .= '<br /><span onClick="this.parent.style.display=\'none\'">Fermer</span>';
		$retour .= '</span>';
		return $retour;
	}
}
