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
class Bral_Util_Lien {
	
	public static function remplaceBaliseParNomEtJs($texteOriginal, $avecJs = true) {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Lieu");
		
		// Monstre
		$texte = preg_replace_callback("/\[m(.*?)]/si", 
		create_function(
			'$matches', '
			$m = new Monstre();
			$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'/voir/monstre/?monstre=".$matches[1]."\');\">";
			$nom .= $m->findNomById($matches[1]);
			$nom .= "</label>";
			return $nom;'
		)
		, $texteOriginal);
		
		// Hobbit
		$texte = preg_replace_callback("/\[h(.*?)]/si", 
		create_function(
			'$matches', '
			$h = new Hobbit();
			$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'/voir/hobbit/?hobbit=".$matches[1]."\');\">";
			$nom .= $h->findNomById($matches[1]);
			$nom .= "</label>";
			return $nom;'
		)
		, $texte);
		
		// Lieu
		$texte = preg_replace_callback("/\[l(.*?)]/si", 
		create_function(
			'$matches', '
			$l = new Lieu();
			$nom = $l->findNomById($matches[1]);
			return $nom;'
		)
		, $texte);
		
		return $texte;
	}
	
	public static function getJsHobbit($id, $texte) {
		return "<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/hobbit/?hobbit=".$id."');\">".$texte."</label>";
	}
}
