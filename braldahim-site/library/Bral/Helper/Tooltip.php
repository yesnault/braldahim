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
class Bral_Helper_Tooltip
{

	public static function render($texte, $titre = null, $boutonCopier = null)
	{
		$id = 'n' . uniqid();
		$retour = '<div class="tip" id="' . $id . '">';
		$retour .= '<span id="' . $id . 'fix" onclick="braltipFixer(\'' . $id . '\')">[Fixer]</span>';
		$retour .= '<span id="' . $id . 'clos" style="display:none" onclick="braltipDeFixer(\'' . $id . '\')">[Ne plus fixer]</span>&nbsp;';
		$retour .= '<span id="' . $id . 'dep" style="display:none">[Déplacer]</span>';
		$retour .= "<hr />";
		if ($titre != null) {
			$retour .= $titre;
			$retour .= "<hr />";
		}
		$retour .= $texte;
		$retour .= '</div>';
		return $retour;
	}
}
