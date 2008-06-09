<?php

abstract class Bral_Lieux_Lieu {
	
	protected $reloadInterface = false;

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
			Zend_Loader::loadClass("Echoppe");
			$echoppesTable = new Echoppe();
			$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
			if (count($echoppeRowset) > 1) {
				throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
			} elseif (count($echoppeRowset) == 1) {
				$echoppe = $echoppeRowset[0];
				$this->view->estLieuCourant = true;
			} else {
				throw new Zend_Exception(get_class($this)."::nombre de lieux invalide = 0 !");
			}
		}
		
		$this->view->utilisationPaPossible = (($view->user->pa_hobbit - $this->view->paUtilisationLieu) >= 0);
		
		$this->prepareCommun();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				if ($this->view->utilisationPaPossible) {
					$this->prepareResultat();
				} else {
					throw new Zend_Exception(get_class($this)."::pas assez de PA");
				}
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	
	private function majEvenements($detailsBot) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		$id_type = $this->view->config->game->evenements->type->service;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a utilisé un service";
		Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $id_type, $details, $detailsBot);
	}
	
	function getNomInterne() {
		return "box_action";
	}

	function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("lieux/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("lieux/".$this->nom_systeme."_resultat.phtml");
				
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				$this->majEvenements(Bral_Helper_Affiche::copie($this->view->texte));
				return $this->view->render("lieux/commun_resultat.phtml");
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
		$this->view->user->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_hobbit, $this->view->user->castars_hobbit);
		
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
			'balance_faim_hobbit' => $this->view->user->balance_faim_hobbit,
			'poids_transportable_hobbit' => $this->view->user->poids_transportable_hobbit,
			'poids_transporte_hobbit' => $this->view->user->poids_transporte_hobbit,
			'force_bbdf_hobbit' => $this->view->user->force_bbdf_hobbit, 
			'agilite_bbdf_hobbit' => $this->view->user->agilite_bbdf_hobbit,
			'vigueur_bbdf_hobbit' => $this->view->user->vigueur_bbdf_hobbit, 
			'sagesse_bbdf_hobbit' => $this->view->user->sagesse_bbdf_hobbit,
		
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
}
