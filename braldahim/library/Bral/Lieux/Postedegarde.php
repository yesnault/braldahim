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
class Bral_Lieux_Postedegarde extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		$this->prepareDonjon();
		$this->prepareEquipe();
		$this->prepareInscription();
	}

	private function prepareDonjon() {
		Zend_Loader::loadClass('Donjon');

		$donjonTable = new Donjon();
		$donjonCourant = $donjonTable->findByIdLieu($this->view->idLieu);

		if ($donjonCourant == null || count($donjonCourant) != 1) {
			throw new Zend_Exception('Donjon invalide. idLieu='.$this->view->idLieu);
		}
		$this->donjonCourant = $donjonCourant[0];
		$this->view->idTypeDistinctionCourante = $this->donjonCourant["id_fk_distinction_quete_region"];
	}

	private function prepareEquipe() {
		Zend_Loader::loadClass('DonjonEquipe');

		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($this->donjonCourant["id_donjon"]);

		$this->view->nouvelleEquipePossible = true;
		if ($donjonEquipe != null) { // si on a une equipe en cours
			$this->view->nouvelleEquipePossible = false;
		}

		if (count($donjonEquipe) > 1) {
			throw new Exception('Equipe de donjon en cours > 1 idDonjon:'.$this->donjonCourant["id_donjon"]);
		}

		if (count($donjonEquipe) == 1) {
			$this->equipeCourante = $donjonEquipe[0];
		} else {
			$this->equipeCourante = null;
		}
	}

	private function prepareInscription() {


		if ($this->equipeCourante == null) {

			$this->view->inscriptionParHobbitNouvelleEquipePossible = false;

			Zend_Loader::loadClass("HobbitsDistinction");
			$hobbitsDistinctionTable = new HobbitsDistinction();
			$distinction = $hobbitsDistinctionTable->findDistinctionsByHobbitIdAndIdTypeDistinction($this->view->user->id_hobbit, $this->donjonCourant["id_fk_distinction_quete_region"]);
			if (count($distinction) == 1) {
				$this->view->inscriptionParHobbitNouvelleEquipePossible = true;
			} else if (count($distinction) > 1) {
				throw new Zend_Exception("Nb. Distinctions invalides nb=".count($distinction));
			}
			return;
		}

		// on regarde si le joueur est demandé dans l'équipe courante

		Zend_Loader::loadClass('DonjonHobbit');
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdHobbitAndIdEquipe($this->view->user->id_hobbit, $this->equipeCourante["id_donjon_equipe"]);

		$this->view->inscriptionDemandee = false;
		$this->view->inscriptionRealisee = false;
		if ($donjonHobbit != null) {
			if ($donjonHobbit["date_entree_donjon_hobbit"] == null) {
				$this->view->inscriptionDemandee = true;
				$this->view->dateLimiteInscription = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e\; H:i:s ',$donjonHobbit["date_limite_inscription_donjon_equipe"]);
			} else {
				$this->view->inscriptionRealisee = true;
			}
		}

	}


	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : pa:".$this->view->user->pa_hobbit." cout:".$this->$this->view->paUtilisationLieu);
		}

		if ($this->view->nouvelleEquipePossible == false && $this->view->inscriptionDemandee == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 1 idh:".$this->view->user->pa_hobbit);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParHobbitNouvelleEquipePossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 2 idh:".$this->view->user->pa_hobbit);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParHobbitNouvelleEquipePossible == true) {
			$this->inscriptionNouvelleEquipe();
		} elseif ($this->view->inscriptionDemandee == true) {
			$this->inscriptionHobbit();
		}

	}

	private function inscriptionNouvelleEquipe() {
		//TODO
		$hobbits = $this->recupereHobbitFromValeur1();
	}

	private function recupereHobbitFromValeur1() {
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		$hobbitsList = $filter->filter(trim($this->request->get('valeur_1')));

		$idHobbitsTab = split(',', $hobbitsList);

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByIdList($idHobbitsTab);

		if ($hobbits == null) {
			throw new Zend_Exception(get_class($this)." Liste invalide:".$hobbitsList);
		}

		return $hobbits;
	}

	private function inscriptionHobbit() {
		//TODO
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

	private function calculCoutCastars() {
		return 0;
	}

}