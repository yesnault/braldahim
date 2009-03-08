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
abstract class Bral_Soule_Soule {
	
	function __construct($nomSystemeAction, $request, $view, $action, $idTerrainDefaut = null) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->idTerrainDefaut = $idTerrainDefaut;
		
		$this->prepareCommun();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	abstract function getNomInterne();
	abstract function getTitreAction();
	abstract function calculNbPa();
	
	protected function constructListBoxRefresh($tab = null) {
		if ($this->view->user->niveau_hobbit > 0 && ($this->view->user->niveau_hobbit % 10) == 0) {
			$tab[] = "box_titres";
		}
		$tab[] = "box_profil";
		$tab[] = "box_evenements";
		return $tab;
	}
	
	protected function setDetailsEvenement($details, $idType) {
		$this->detailEvenement = $details;
		$this->idTypeEvenement = $idType;
	}
	
	/*
	 * Mise à jour des événements du hobbit : type : compétence.
	 */
	private function majEvenementsSoule($detailsBot) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_hobbit);
	}
	
	public function getIdEchoppeCourante() {
		return false;
	}
	
	function render() {
		$this->view->nomAction = $this->getTitreAction();
		$this->view->nomSysteme = $this->nom_systeme;
		switch($this->action) {
			case "ask":
				$texte = $this->view->render("soule/".$this->nom_systeme."_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				
				return $this->view->render("soule/commun_formulaire.phtml");
				break;
			case "do":
				$texte = $this->view->render("soule/".$this->nom_systeme."_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				
				$this->majEvenementsSoule(Bral_Helper_Affiche::copie($this->view->texte));
				return $this->view->render("soule/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	protected function majHobbit() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
		
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;
		$this->view->user->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_hobbit, $this->view->user->castars_hobbit);
		
		if ($this->view->user->balance_faim_hobbit < 0) {
			$this->view->user->balance_faim_hobbit = 0; 
		}
		
		$data = array(
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'castars_hobbit' => $this->view->user->castars_hobbit,
			'poids_transporte_hobbit' => $this->view->user->poids_transporte_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
	
}