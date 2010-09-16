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
		$id = uniqid();
		$retour = '<span class="tip" id="'.$id.'">';
		if ($titre != null) {
			$retour .= $titre;
		}
		
		$retour .= ' <span id="'.$id.'fix" onClick="braltipFixer(\''.$id.'\')">[Fixer]</span>';
		$retour .= '<span id="'.$id.'clos" style="display:none" onClick="braltipDeFixer(\''.$id.'\')">[Ne plus fixer]</span>';
		$retour .= '<span id="'.$id.'dep" style="display:none">[Déplacer]</span>';
		$retour .= "<hr />";
		$retour .= $texte;
		
		/*$retour .= "<br /><br /><span onClick=\"
		new Draggable('".$id."');Draggables.unregister('". $id."'); \">[Déplacer]</span>";*/
		$retour .= '</span>';
		return $retour;
	}
}
