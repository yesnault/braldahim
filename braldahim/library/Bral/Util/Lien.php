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
class Bral_Util_Lien {
	
	public static function remplaceBaliseParNomEtJs($texteOriginal, $avecJs = true) {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("Materiel");
		
		// Monstre
		$texte = preg_replace_callback("/\[m(.*?)]/si", 
		create_function('$matches', self::getFunctionMonstre($avecJs)) , $texteOriginal);
		
		// Braldun
		$texte = preg_replace_callback("/\[b(.*?)]/si", 
		create_function('$matches', self::getFunctionBraldun($avecJs)), $texte);
		
		// Materiel
		$texte = preg_replace_callback("/\[t(.*?)]/si", 
		create_function('$matches', self::getFunctionMateriel($avecJs)), $texte);
		
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
	
	private static function getFunctionMonstre($avecJs = true) {
		$retour = '$m = new Monstre();';
		$retour .= '$nom = "";';
		if ($avecJs) $retour .= '$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'/voir/monstre/?monstre=".$matches[1]."\');\">";';
		$retour .= '$nom .= $m->findNomById($matches[1]);';
		if ($avecJs) $retour .= '$nom .= "</label>";';
		$retour .= 'return $nom;';
		return $retour;
	}
	
	private static function getFunctionBraldun($avecJs = true) {
		$retour = '$h = new Braldun();';
		$retour .= '$retour = "";';
		$retour .= '$equipe = "";';
		//$retour .= '$nom = $h->findNomById($matches[1]);';
		$retour .= '$braldun = $h->findById($matches[1]);';
		$retour .= '$nom = $braldun["prenom_braldun"]. " ".$braldun["nom_braldun"]. " (".$braldun["id_braldun"].")";';
		$retour .= ' if ($braldun["est_soule_braldun"] == "oui") {';
		$retour .= '$equipe = " equipe".$braldun["soule_camp_braldun"];';
		$retour .= ' }';
		
		if ($avecJs) $retour .= '$retour = "<label class=\'alabel$equipe\' onclick=\"javascript:ouvrirWin(\'/voir/braldun/?braldun=".$matches[1]."\');\">";';
		
		if ($avecJs) $retour .= '$retour .= "$nom</label>";';
		$retour .= 'return $retour;';
		return $retour;
	}
	
	private static function getFunctionMateriel($avecJs = true) {
		$retour = '$m = new Materiel();';
		$retour .= '$nom = "";';
		if ($avecJs) $retour .= '$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'/voir/materiel/?materiel=".$matches[1]."\');\">";';
		$retour .= '$nom .= $m->findNomById($matches[1]);';
		if ($avecJs) $retour .= '$nom .= "</label>";';
		$retour .= 'return $nom;';
		return $retour;
	}
	
	public static function getJsBraldun($id, $texte, $afficheMessage = false) {
		$msg = "";
		if ($afficheMessage) $msg = " <img src='/public/images/uddeim/env.gif' title='Envoyer un message' alt='Ecrire' border='0' onClick=ecrireMessage('$id'); style='cursor:pointer'/> ";
		$lien = "<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/braldun/?braldun=".$id."');\">".$texte."</label>".$msg;
		return $lien;
	}
}
