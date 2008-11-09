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
class Bral_Util_Nom {

	function __construct() {
	}
	
	function calculNom($prenom) {
		$idNom = $this->calculIdNom($prenom);
		
		$nomTable = new Nom();
		$nomRowset = $nomTable->findById($idNom);
		
		$dataNom["nom"] = $nomRowset->nom;
		$dataNom["id_nom"] = $idNom;
		return $dataNom;
	}
	
	function estValidPrenom($prenom) {
		$idNom = $this->calculIdNom($prenom);
		
		if ($idNom == -1) {
			return false;
		} else {
			return true;
		}
	}
	
	private function calculIdNom($prenom) {
		$nomTable = new Nom();
		$nomRowset = $nomTable->fetchAllId();
		
		foreach ($nomRowset as $n) {
			$idNoms[] = $n["id_nom"];
		}
		$idNomsBis = $idNoms;
		
		srand((float)microtime()*1000000);
		shuffle($idNoms);
		
		$idNom = -1;
		
		$hobbitTable = new Hobbit();
		$nomOk = false;
		while ($nomOk != true) {
			$idNom = array_pop($idNoms);
			$r = $hobbitTable->findByIdNomInitialPrenom($idNom, $prenom);
			if (count($r) > 0) {
				// association nom / prenom deja presente
				// on regarde s'il reste des noms dispo dans la liste
				if (count($idNoms) == 0) {
					// on n'a pas trouve de nom, on re-initialise la liste de nom
					$idNoms = $idNomsBis;
					// et on rajoute Bis au prenom
					if (mb_strlen($prenom) <= 23) { // "123456789012345 Junior" et l'on rajoute un " Bis"
						$prenom = $prenom. " Junior";
					} else {
						// ici, on ne peut vraiment pas accepter le prenom
						$nomOk = true;
						$idNom = -1;
					}
				}
			} else {
				$nomOk = true;
			}
		}
		return $idNom;
	}
}