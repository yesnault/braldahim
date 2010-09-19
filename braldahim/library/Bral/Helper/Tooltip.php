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
		Zend_Loader::loadClass("Carnet");
		
		$id = 'n'.uniqid();
		$retour = '<div class="tip" id="'.$id.'">';
		$retour .= '<span id="'.$id.'fix" onclick="braltipFixer(\''.$id.'\')">[Fixer]</span>';
		if (!Zend_Registry::get("estMobile")) {
			$retour .= '<span id="'.$id.'dep" style="display:none">[Déplacer]</span>';
		}
		$retour .= '<span id="'.$id.'clos" style="display:none" onclick="braltipDeFixer(\''.$id.'\')">[Ne plus fixer]</span>&nbsp;';
		
		$retour .= '<span id="'.$id.'enr" style="display:inline" onclick="braltipMsg(\''.$id.'\')">[Message]</span>&nbsp;';
		$retour .= '<span id="'.$id.'enr" style="display:inline" onclick="braltipDispEnr(\''.$id.'\')">[Enregistrer]</span>&nbsp;';
		
		$retour .= '<span id="'.$id.'sel" style="display:none"><br />Enregistrer dans:';
		$retour .= '<select name="'.$id.'numNote" id="'.$id.'numNote">';
		 
		for($i = 1; $i <= Carnet::MAX_NOTE; $i++) :
			$retour .= '<option value="'.$i.'">note n&deg;'.$i.'</option>';
		 endfor;
		$retou = '</select>';
		$retour .= '<input type="button" class="button1" id="'.$id.'btnEnr" value="Enregistrer" onclick="braltipEnr(this, \''.$id.'\')">';				
		$retour .= '<span id="'.$id.'msg">&nbsp</span>';
		$retour .= '</span>';
		
		
		$retour .= "<hr />";
		if ($titre != null) {
			$retour .= $titre;
			$retour .= "<hr />";
		}
		
		$retour .= '<span id="'.$id.'texte">';
		$retour .= $texte;
		$retour .= '</span>';
		
		$retour .= '</div>';
		return $retour;
	}
}
