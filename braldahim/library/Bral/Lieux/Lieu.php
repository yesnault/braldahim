<?php

abstract class Bral_Lieux_Lieu {

	function __construct($nomSystemeLieu, $request, $view, $action) {
		Zend_Loader::loadClass("Lieu");
		
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeLieu;

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($view->user->x_hobbit, $view->user->y_hobbit);
		$this->view->estLieuCourant = false;

		if (count($lieuRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			$this->view->estLieuCourant = true;
			$this->view->idLieu = $lieu["id_lieu"];
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->view->nomTypeLieu = $lieu["nom_type_lieu"];
			$this->view->nomSystemeLieu = $lieu["nom_systeme_type_lieu"];
			$this->view->descriptionLieu = $lieu["description_lieu"];
			$this->view->descriptionTypeLieu = $lieu["description_type_lieu"];
			$this->view->estFranchissableLieu = ($lieu["est_franchissable_type_lieu"] == "oui");
			$this->view->estAlterableLieu = ($lieu["est_alterable_type_lieu"] == "oui");
			$this->view->paUtilisationLieu = $lieu["pa_utilisation_type_lieu"];
			$this->view->niveauMinLieu = $lieu["niveau_min_type_lieu"];
		} else {
			throw new Zend_Exception(get_class($this)."::nombre de lieux invalide = 0 !");
		}
		
		$this->view->utilisationPaPossible = (($this->view->paUtilisationLieu - $view->user->pa_hobbit) < 0);
		
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

	function getNomInterne() {
		return "box_action";
	}

	function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("lieux/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				return $this->view->render("lieux/".$this->nom_systeme."_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	public function majHobbit() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->paUtilisationLieu;
		
		if ($this->view->user->balance_faim_hobbit < 0) {
			$this->view->user->balance_faim_hobbit = 0; 
		}
		
		$data = array(
		'pa_hobbit' => $this->view->user->pa_hobbit,
		'castars_hobbit' => $this->view->user->castars_hobbit,
		'pi_hobbit' => $this->view->user->pi_hobbit,
		'force_base_hobbit' => $this->view->user->force_base_hobbit,
		'agilite_base_hobbit' => $this->view->user->agilite_base_hobbit,
		'vigueur_base_hobbit' => $this->view->user->vigueur_base_hobbit,
		'sagesse_base_hobbit' => $this->view->user->sagesse_base_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
}