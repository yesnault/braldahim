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
class Bral_Util_Niveau {
	
	private function __construct() {
	}
	
	/**
	 * Le niveau suivant est calculé à partir d'un certain nombre de px perso
	 * qui doit être >= à :
	 * NiveauSuivantPX = NiveauSuivant x 5
	 */
	public static function calculNiveau(&$hobbit, &$changeNiveau = null) {

		if ($changeNiveau == null) {
			$changeNiveau = false;
		}
		
		$niveauSuivantPx = ($hobbit->niveau_hobbit + 1) * 5;
		if ($hobbit->px_perso_hobbit >= $niveauSuivantPx) {
			$hobbit->px_perso_hobbit = $hobbit->px_perso_hobbit - $niveauSuivantPx;
			$hobbit->niveau_hobbit = $hobbit->niveau_hobbit + 1;
			$hobbit->pi_cumul_hobbit = $hobbit->pi_cumul_hobbit + $niveauSuivantPx;
			$hobbit->pi_hobbit = $hobbit->pi_hobbit + $niveauSuivantPx;
			$changeNiveau = true;
			
			if (($hobbit->niveau_hobbit % 10) == 0) {
				self::calculTitre(&$hobbit);
			}
		}

		$niveauSuivantPx = ($hobbit->niveau_hobbit + 1) * 5;
		if ($hobbit->px_perso_hobbit >= $niveauSuivantPx) {
			self::calculNiveau(&$hobbit, &$changeNiveau);
		}
		return $changeNiveau;
	}
	
	private static function calculTitre(&$hobbit) {
		Zend_Loader::loadClass("TypeTitre");
		Zend_Loader::loadClass("HobbitsTitres");
		Zend_Loader::loadClass("Bral_Util_Titre");
		
		$idTitre = Bral_Util_De::get_1d4();
		
		$data = array(
			'id_fk_hobbit_htitre' => $hobbit->id_hobbit,
			'id_fk_type_htitre' => $idTitre,
			'niveau_acquis_htitre' => $hobbit->niveau_hobbit,
			'date_acquis_htitre' => date("Y-m-d"),
		);
		
		$hobbitsTitres = new HobbitsTitres();
		$hobbitsTitres->insert($data);
		
		$typeTitre = new TypeTitre();
		$typeTitreRowset = $typeTitre->findById($idTitre);
		
		if ($hobbit->sexe_hobbit == "feminin") {
			$hobbit->titre_courant_hobbit = $typeTitreRowset->nom_feminin_type_titre;
		} else {
			$hobbit->titre_courant_hobbit = $typeTitreRowset->nom_masculin_type_titre;
		}
		
		Bral_Util_Titre::calculNouveauTitre(&$hobbit, $typeTitreRowset);
	}
}