<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Lieux_Lieu {

	protected $reloadInterface = false;

	function __construct($nomSystemeLieu, $request, $view, $action) {
		Zend_Loader::loadClass("Lieu");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeLieu;

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($view->user->x_braldun, $view->user->y_braldun, $view->user->z_braldun);
		$this->view->estLieuCourant = false;

		if (count($lieuRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {

			$estBanque = false;
			if (strlen($nomSystemeLieu) == 13 && substr($nomSystemeLieu, 0, 6) == "banque") { // banque pour banquedeposer / banqueretirer
				$estBanque = true;
			}

			$lieu = $lieuRowset[0];
			if ($nomSystemeLieu == $lieu["nom_systeme_type_lieu"] || ($estBanque && $lieu["nom_systeme_type_lieu"] == "banque")) {
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
				if (array_key_exists("nom_ville", $lieu)) {
					$this->view->nomVille = $lieu["nom_ville"];
				}
			} else {
				throw new Zend_Exception(get_class($this)."::type de lieu invalide ! s:".$nomSystemeLieu. " id:".$view->user->id_braldun. " x:".$view->user->x_braldun. " y:".$view->user->y_braldun);
			}
		} else {
			Zend_Loader::loadClass("Echoppe");
			$echoppesTable = new Echoppe();
			$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			if (count($echoppeRowset) > 1) {
				throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
			} elseif (count($echoppeRowset) == 1) {
				$echoppe = $echoppeRowset[0];
				$this->view->estLieuCourant = true;
			} else {
				throw new Zend_Exception(get_class($this)."::nombre de lieux invalide = 0 !");
			}
		}

		$this->view->estQueteEvenement = false;

		if ($view->user->pa_braldun - $this->view->paUtilisationLieu >= 0) {
			$this->view->utilisationPaPossible = true;
		} else {
			$this->view->utilisationPaPossible = false;
		}

		$this->prepareCommun();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				if ($this->view->utilisationPaPossible) {
					$this->prepareResultat();
				} else {
					throw new Zend_Exception(get_class($this)."::pas assez de PA braldun:".$view->user->pa_braldun. " lieu:".$this->view->paUtilisationLieu);
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
		$details = "[b".$this->view->user->id_braldun."] a utilisé un service";
		Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $id_type, $details, $detailsBot, $this->view->user->niveau_braldun);
	}

	function getNomInterne() {
		return "box_action";
	}

	function getIdEchoppeCourante() {
		return false;
	}
	
	public function getIdChampCourant() {
		return false;
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
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	public function majBraldun() {
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$this->view->user->pa_braldun = $this->view->user->pa_braldun - $this->view->paUtilisationLieu;
		$this->view->user->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_braldun, $this->view->user->castars_braldun);

		if ($this->view->user->balance_faim_braldun < 0) {
			$this->view->user->balance_faim_braldun = 0;
		}

		$data = array(
			'pa_braldun' => $this->view->user->pa_braldun,
			'px_perso_braldun' => $this->view->user->px_perso_braldun,
			'duree_prochain_tour_braldun' =>  $this->view->user->duree_prochain_tour_braldun,
			'castars_braldun' => $this->view->user->castars_braldun,
			'pi_braldun' => $this->view->user->pi_braldun,
			'force_base_braldun' => $this->view->user->force_base_braldun,
			'agilite_base_braldun' => $this->view->user->agilite_base_braldun,
			'vigueur_base_braldun' => $this->view->user->vigueur_base_braldun,
			'sagesse_base_braldun' => $this->view->user->sagesse_base_braldun,
			'balance_faim_braldun' => $this->view->user->balance_faim_braldun,
			'poids_transportable_braldun' => $this->view->user->poids_transportable_braldun,
			'poids_transporte_braldun' => $this->view->user->poids_transporte_braldun,
			'force_bbdf_braldun' => $this->view->user->force_bbdf_braldun, 
			'agilite_bbdf_braldun' => $this->view->user->agilite_bbdf_braldun,
			'vigueur_bbdf_braldun' => $this->view->user->vigueur_bbdf_braldun, 
			'sagesse_bbdf_braldun' => $this->view->user->sagesse_bbdf_braldun,
			'x_braldun' => $this->view->user->x_braldun,
			'y_braldun' => $this->view->user->y_braldun,
			'z_braldun' => $this->view->user->z_braldun,
			'pv_restant_braldun' => $this->view->user->pv_restant_braldun,
			'armure_naturelle_braldun' => $this->view->user->armure_naturelle_braldun,
			'regeneration_braldun' => $this->view->user->regeneration_braldun,
			'est_quete_braldun' => $this->view->user->est_quete_braldun,
			'pi_academie_braldun' => $this->view->user->pi_academie_braldun,
			'points_gredin_braldun' => $this->view->user->points_gredin_braldun,
			'points_redresseur_braldun' => $this->view->user->points_redresseur_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}

	protected function constructListBoxRefresh($tab = null) {
		$tab[] = "box_profil";
		$tab[] = "box_evenements";
		if ($this->view->estQueteEvenement) {
			$tab[] = "box_quetes";
		}
		if ($this->view->user->pa_braldun < 1) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh0PA($tab);
		}
		return $tab;
	}
}
