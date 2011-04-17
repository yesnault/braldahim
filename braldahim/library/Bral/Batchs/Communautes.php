<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Communautes extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculBatchImpl - enter -");

		Zend_Loader::loadClass('Communaute');
		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Coffre');
		Zend_Loader::loadClass('Bral_Util_Lune');
		Zend_Loader::loadClass("TypeEvenementCommunaute");
		Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

		$retour = null;

		$annee = date('Y');
		$mois = date('m');
		$jour = date('d');
		$heure = date('H');
		$mine = date('i');
		$seconde = date('s');

		list($moonPhase, $moonAge, $moonDist, $moonAng, $sunDist, $sunAng, $mpfrac) = Bral_Util_Lune::calculPhase($annee, $mois, $jour, $heure, $mine, $seconde);
		// coef lune : $mpfrac
		$coefLune = floor($mpfrac * 100);

		$retour .= $this->calculCommunautes($moonAge);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCommunautes($moonAge) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculCommunautes - enter -");
		$retour = "";

		$communauteTable = new Communaute();
		$communautes = $communauteTable->fetchAll();
		foreach($communautes as $communaute) {
			// Les deux premiers jours de la lune, on vérifie l'entretien, sinon non
			// Si l'age est < 2j, on revérifie la date d'entretien des bâtiments pour la réentrance
			//if ($moonAge > 2) { // Lune > 2 jours
			Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretien - exit, ageLune:".$moonAge);
			//	return $retour;
			//}
			$retour .= $this->calculEntretien($communaute);
			$retour .= $this->calculPointsInfluence($communaute);
		}

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculCommunautes - exit -");
		return $retour;
	}

	private function calculPointsInfluence($communaute) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculPointsInfluence - enter - communaute id:".$communaute['id_communaute']);
		$retour = "";

		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByIdCommunaute($communaute['id_communaute']);

		if (count($lieux) == 0) {
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Communaute - calculPointsInfluence - aucun bâtiment pour la communaute ".$communaute['id_communaute']);
		}

		$points = 0;
		foreach($lieux as $lieu) {
			$points = $points + $lieu['niveau_lieu'];
		}

		$communauteTable = new Communaute();
		$data['points_communaute'] = $points;
		$where = "id_communaute = ".$communaute['id_communaute'];
		$communauteTable->update($data, $where);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculPointsInfluence - exit -");
		return $retour;
	}

	private function calculEntretien($communaute) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretien - enter - communaute id:".$communaute['id_communaute']);
		$retour = "";

		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByIdCommunaute($communaute['id_communaute'], null, null, null, true);

		$coffreTable = new Coffre();
		$coffre = $coffreTable->findByIdCommunaute($communaute['id_communaute']);

		if (count($coffre) != 1) {
			throw new Zend_Exception('Erreur coffre. IdCommunaute:'.$communaute['id_communaute']);
		}
		$coffre = $coffre[0];
		$nbCastarsDansCoffre = $coffre['quantite_castar_coffre'];

		if (count($lieux) == 0) {
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Communaute - calculEntretien - aucun bâtiment pour la communaute ".$communaute['id_communaute']);
		}
		
		foreach($lieux as $lieu) {
			$coutsEntretien = Bral_Util_Communaute::getCoutsEntretienBatiment($lieu['niveau_lieu']);
			$coutsCastars = $coutsEntretien['cout_castar'];

			Bral_Util_Log::batchs()->trace("Bral_Batchs_Communaute - calculEntretien - nbCastarsDansCoffre(".$nbCastarsDansCoffre.") coutsCastars(".$coutsCastars.")");
			if ($nbCastarsDansCoffre >= $coutsCastars) { // entretien Ok
				$nbCastarsDansCoffre = $nbCastarsDansCoffre - $coutsCastars;
				$this->calculEntretienOk($lieu, $communaute, $coutsCastars, $nbCastarsDansCoffre);
			} else { // entretien Ko
				$this->calculEntretienKo($lieu, $communaute, $coutsCastars);
			}
		}

		// mise à jour du coffre
		if ($nbCastarsDansCoffre < 0) {
			$nbCastarsDansCoffre = 0;
		}
		$data['quantite_castar_coffre'] = $nbCastarsDansCoffre;
		$where = 'id_coffre = '.$coffre['id_coffre'];
		$coffreTable->update($data, $where);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretien - exit -");
		return $retour;
	}

	private function calculEntretienOk($lieu, $communaute, $coutsCastars, $nbCastarsDansCoffre) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienOk - enter -");
		$retour = "";

		$details = $lieu['nom_lieu']." (ok)";
		$detailsBot = "Le bâtiment -".$lieu['nom_lieu']."- a été correctement entretenu. ".PHP_EOL;
		$detailsBot .= "Coût en castars: ".$coutsCastars.".".PHP_EOL;

		$s = '';
		if ($nbCastarsDansCoffre > 1) {
			$s = 's';
		}

		$detailsBot .= "Il reste ".$nbCastarsDansCoffre." castar".$s." dans le coffre de la Communauté.".PHP_EOL;
		$detailsBot .= PHP_EOL.PHP_EOL."Action réalisée automatiquement.";
		Bral_Util_EvenementCommunaute::ajoutEvenements($communaute['id_communaute'], TypeEvenementCommunaute::ID_TYPE_ENTRETIEN, $details, $detailsBot, $this->view);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienOk - exit -");
		return $retour;
	}

	private function calculEntretienKo($lieu, $communaute, $coutsCastars) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - enter -");
		$retour = "";

		/*
		 * - Entretien pour chaque bâtiment
		 * -> si entretien KO, -1 sur le niveau du bâtiment.
		 *  Si niveau == 0, suppression du bâtiment et des dépendances.
		 */

		$lieuTable = new Lieu();
		$where = 'id_lieu = '.$lieu['id_lieu'];

		$detailsBot = "Coût en castars de l'entretien: ".$coutsCastars.".".PHP_EOL;
		$detailsBot .= "Il n'y a pas assez de castars dans le coffre de la Communauté.".PHP_EOL.PHP_EOL;

		$lieu['niveau_lieu'] = $lieu['niveau_lieu'] - 1;
		
		if ($lieu['niveau_lieu'] < 1) {
			// Suppression du bâtiment et des dépendances
			$lieuTable->delete($where);

			$retour = "suppression du lieu ".$lieu['id_lieu'];
			Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - ".$retour);

			$details = $lieu['nom_lieu']." (détruit)";
			$detailsBot .= "Le bâtiment -".$lieu['nom_lieu']."- a été n'a pas été entretenu. Étant de niveau ".($lieu['niveau_lieu'] + 1).", il a été détruit. ".PHP_EOL;
			$detailsBot .= self::calculSuppressionDependances($lieu);
		} else { // descente d'un niveau, suppression des dépendances

			$data = array(
				'niveau_lieu' => $lieu['niveau_lieu'],
				'niveau_prochain_lieu' => $lieu['niveau_prochain_lieu'] - 1,
				'date_entretien_lieu' => date("Y-m-d H:i:s"),
			);
			$lieuTable->update($data, $where);
			$retour = "descente d'un niveau du lieu ".$lieu['id_lieu'];
			Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - ".$retour);

			$details = $lieu['nom_lieu']." (ko)";
			$detailsBot .= "Le bâtiment -".$lieu['nom_lieu']."- a été n'a pas été entretenu et a perdu un niveau.".PHP_EOL;
			$detailsBot .= "Nouveau niveau obtenu :".$lieu['niveau_lieu'].PHP_EOL.PHP_EOL;
			$detailsBot .= "S'il n'est pas entretenu au niveau 0 ou 1, il sera automatiquement détruit.".PHP_EOL;
			$detailsBot .= self::calculSuppressionDependances($lieu);
		}

		$detailsBot .= PHP_EOL.PHP_EOL."Action réalisée automatiquement.";
		Bral_Util_EvenementCommunaute::ajoutEvenements($communaute['id_communaute'], TypeEvenementCommunaute::ID_TYPE_ENTRETIEN, $details, $detailsBot, $this->view);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - exit -");
		return $retour;
	}

	private function calculSuppressionDependances($lieu) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculSuppressionDependances - enter -");
		$retour = "";

		$lieuTable = new Lieu();
		$dependances = $lieuTable->findDependanceByIdTypeAndIdCommunaute($lieu["id_fk_type_lieu"], $lieu["id_fk_communaute_lieu"]);
		
		if ($dependances == null || count($dependances) <= 0) {
			return null;
		}

		foreach($dependances as $dependance) {
			// si le niveau du lieu est strictement inferieur à la dépendance
			if ($dependance["niveau_type_dependance"] > $lieu["niveau_lieu"]) {
				$retour .= "La dépendance -".$dependance['nom_lieu']."- est détruite.".PHP_EOL;
				$where = "id_lieu=".$dependance["id_lieu"];
				$lieuTable->delete($where);
			}
		}
		
		if ($retour != "") {
			$retour = PHP_EOL.$retour;
		}
		
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculSuppressionDependances - exit -");
		return $retour;
	}

}