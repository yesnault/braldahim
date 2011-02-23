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

		$retour = null;
		$retour .= $this->calculCommunautes();

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCommunautes() {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculCommunautes - enter -");
		$retour = "";

		$communauteTable = new Communaute();
		$communautes = $communauteTable->fetchAll();
		foreach($communautes as $communaute) {
			$retour .= $this->calculEntretien($communaute);
		}

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculCommunautes - exit -");
		return $retour;
	}

	private function calculEntretien($communaute) {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretien - enter - communaute id:".$communaute['id_communaute']);
		$retour = "";

		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByIdCommunaute($communaute['id_communaute']);

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
		
		//TODO Message
		
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
		
		if ($lieu['niveau_lieu'] <= 1) { 
			// Suppression du bâtiment et des dépendances	
			$lieuTable->delete($where);
			
			$retour = "suppression du lieu ".$lieu['id_lieu'];
			Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - ".$retour);
			
			//TODO Message
		} else { // descente d'un niveau
			
			$data = array(
				'niveau_lieu' => $lieu['niveau_lieu'] - 1,
				'niveau_prochain_lieu' => $lieu['niveau_prochain_lieu'] - 1,
			);
			$lieuTable->update($data, $where);
			$retour = "descente d'un niveau du lieu ".$lieu['id_lieu'];
			Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - ".$retour);
			//TODO Message
		}
		
		
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Communaute - calculEntretienKo - exit -");
		return $retour;
	}

}