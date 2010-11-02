<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Auberge extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("ElementAliment");

		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_braldun -  $this->_coutCastars) >= 0);

		$this->view->poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$this->view->nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_ALIMENT);

		$castarsRestants = $this->view->user->castars_braldun -  $this->_coutCastars;
		$nbPossibleAvecCastars = floor($this->view->user->castars_braldun / $this->_coutCastars);

		$this->view->nbDeduction = 0;
		if ($this->view->nbPossible >= $nbPossibleAvecCastars) {
			$this->view->nbPossible = $nbPossibleAvecCastars;
			$this->view->nbDeduction = 1;
		}

		$achatAliment = true;
		if ($this->view->nbPossible < 1) {
			$this->view->nbPossible = 0;
			$achatAliment = false;
		}

		$achatAlimentEtResto = true;
		if ( floor($castarsRestants / $this->_coutCastars) < 1 || $achatAliment == false){
			$achatAlimentEtResto = false;
		}

		$tabChoix[1]["nom"] = "Se restaurer uniquement";
		$tabChoix[1]["valid"] = $this->_utilisationPossible;
		$tabChoix[1]["bouton"] = "Se Restaurer";
		$tabChoix[2]["nom"] = "Acheter des ragoûts uniquement";
		$tabChoix[2]["valid"] = $achatAliment;
		$tabChoix[2]["bouton"] = "Acheter";
		$tabChoix[3]["nom"] = "Se restaurer et acheter des ragoûts";
		$tabChoix[3]["valid"] = $achatAlimentEtResto;
		$tabChoix[3]["bouton"] = "Se Restaurer et Acheter";

		$this->view->tabChoix = $tabChoix;
	}

	function prepareFormulaire() {
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {

		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_braldun." cout:".$this->_coutCastars);
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idChoix = (int)$this->request->get("valeur_1");
		}

		if ($this->view->idChoix == 2 || $this->view->idChoix == 3) {
			if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
				throw new Zend_Exception("Bral_Lieux_Auberge :: Nombre invalide : ".$this->request->get("valeur_2"));
			} else {
				$this->view->nbAcheter = (int)$this->request->get("valeur_2");
			}
		}

		if ($this->view->idChoix < 1 || $this->view->idChoix > 3) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide 2 : ".$this->request->get("valeur_1"));
		}

		if ($this->view->tabChoix[$this->view->idChoix]["valid"] == false) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide 3 : ".$this->view->tabChoix[$this->view->idChoix]["valid"]);
		}

		if ($this->view->nbAcheter > $this->view->nbPossible) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Nombre Rations invalide : ".$this->view->nbAcheter. " possible=".$this->view->nbPossible);
		}

		if ($this->view->idChoix == 1 || $this->view->idChoix == 3) {
				
			Zend_Loader::loadClass("TypeAliment");
			$typeAlimentTable = new TypeAliment();
			$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);
				
			Zend_Loader::loadClass("Bral_Util_Faim");
			Bral_Util_Faim::calculBalanceFaim($this->view->user, $aliment->bbdf_base_type_aliment);
			Zend_Loader::loadClass("Bral_Util_Quete");
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeManger($this->view->user, true);
		} else {
			$this->_coutCastars = 0;
		}

		if ($this->view->idChoix == 2 || $this->view->idChoix == 3) {
			if ($this->view->nbAcheter > 0) {
				$this->calculAchat();
				$this->_coutCastars = $this->_coutCastars + ($this->calculCoutCastars() * $this->view->nbAcheter);
			}
		}

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->_coutCastars;
		$this->majBraldun();

		$this->view->coutCastars = $this->_coutCastars;
	}

	private function calculAchat() {
		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);

		$this->view->qualiteAliment = 2; // qualite correcte
		
		$this->view->bbdfAliment = Bral_Util_De::get_de_specifique(20, 25);
		$this->view->aliment= $aliment;

		$elementAlimentTable = new ElementAliment();
		$labanAlimentTable = new LabanAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAlimentTable = new IdsAliment();
		
		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();
		
		for ($i = 1; $i <= $this->view->nbAcheter; $i++) {
			
			$id_aliment = $idsAlimentTable->prepareNext();
			
			$data = array(
				'id_aliment' => $id_aliment,
				'id_fk_type_aliment' => TypeAliment::ID_TYPE_RAGOUT,
				'id_fk_type_qualite_aliment' => $this->view->qualiteAliment,
				'bbdf_aliment' => $this->view->bbdfAliment,
			);
			$alimentTable->insert($data);
			
			$data = array(
				'id_laban_aliment' => $id_aliment,
				'id_fk_braldun_laban_aliment' => $this->view->user->id_braldun,
			);
			$labanAlimentTable->insert($data);
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban"));
	}

	private function calculCoutCastars() {
		return 9;
	}
}