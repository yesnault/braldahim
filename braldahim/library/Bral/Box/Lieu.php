<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Lieu extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Lieu";
	}

	function getNomInterne() {
		return "box_lieu";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/lieu.phtml");
	}

	function data() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Lieu");

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		unset($lieuxTable);
		$this->view->estLieuCourant = false;

		if (count($lieuRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			unset($lieuRowset);
			$this->view->estLieuCourant = true;
			$this->view->idLieu = $lieu["id_lieu"];
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->view->nomTypeLieu = $lieu["nom_type_lieu"];
			$this->view->nomSystemeLieu = $lieu["nom_systeme_type_lieu"];
			$this->view->nomImageLieu = "batiments/".$lieu["nom_systeme_type_lieu"];
			$this->view->descriptionLieu = $lieu["description_lieu"];
			$this->view->descriptionTypeLieu = $lieu["description_type_lieu"];
			$this->view->estFranchissableLieu = ($lieu["est_franchissable_type_lieu"] == "oui");
			$this->view->estAlterableLieu = ($lieu["est_alterable_type_lieu"] == "oui");
			$this->view->paUtilisationLieu = $lieu["pa_utilisation_type_lieu"];
			$this->view->niveauMinLieu = $lieu["niveau_min_type_lieu"];
			$this->view->idCommunauteLieu = $lieu["id_fk_communaute_lieu"];
			$this->view->nbPaDepensesLieu = $lieu["nb_pa_depenses_lieu"];
			$this->view->nbCastarsDepensesLieu = $lieu["nb_castars_depenses_lieu"];
			$this->view->niveauLieu = $lieu["niveau_lieu"];
			$this->view->niveauProchainLieu = $lieu["niveau_prochain_lieu"];

			if ($this->view->idCommunauteLieu != null) {
				$this->prepareCommunaute($lieu);
			}

			$this->view->htmlLieu = $this->view->render("interface/lieux/".$lieu["nom_systeme_type_lieu"].".phtml");
		} else {
			$avecEchoppe = $this->prepareEchoppe();
			if ($avecEchoppe == false) {
				$this->prepareChamp();
			}
		}

		$this->view->nom_interne = $this->getNomInterne();
	}

	private function prepareEchoppe() {
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		unset($echoppesTable);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} elseif (count($echoppeRowset) == 1) {
			$echoppe = $echoppeRowset[0];
			unset($echoppeRowset);
			$this->view->estLieuCourant = true;

			$nom = "Échoppe";
			if ($echoppe["nom_masculin_metier"]{0} == "A") {
				$nom .= " d'";
			} else {
				$nom .= " de ";
			}
			if ($echoppe["sexe_braldun"] == "masculin") {
				$nom .= $echoppe["nom_masculin_metier"];
			} else {
				$nom .= $echoppe["nom_feminin_metier"];
			}
			$nom .= " appartenant à ".$echoppe["prenom_braldun"];
			$nom .= " ".$echoppe["nom_braldun"];
			$nom .= " n°".$echoppe["id_braldun"];

			$this->view->nomLieu = $nom;
			$this->view->nomTypeLieu = "échoppe";
			$this->view->nomSystemeLieu = "echoppe";
			$this->view->nomImageLieu = "echoppes/".$echoppe["nom_systeme_metier"];
			$this->view->nomEchoppe = $echoppe["nom_echoppe"];
			$this->view->descriptionLieu = "";
			$this->view->commentaireEchoppe = $echoppe["commentaire_echoppe"];
			$this->view->estFranchissableLieu = true;
			$this->view->estAlterableLieu = false;
			$this->view->paUtilisationLieu = 0;
			$this->view->niveauMinLieu = 0;

			$this->view->htmlLieu = $this->view->render("interface/lieux/echoppe.phtml");

			return true;
		} else {
			return false;
		}
	}

	private function prepareChamp() {
		Zend_Loader::loadClass("Champ");
		$champsTable = new Champ();
		$champRowset = $champsTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		unset($champsTable);
		if (count($champRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre champ invalide > 1 !");
		} elseif (count($champRowset) == 1) {
			$champ = $champRowset[0];
			unset($champRowset);
			$this->view->estLieuCourant = true;

			$nom = " Champ appartenant à ".$champ["prenom_braldun"];
			$nom .= " ".$champ["nom_braldun"];
			$nom .= " n°".$champ["id_braldun"];

			$this->view->nomLieu = $nom;
			$this->view->nomTypeLieu = "champ";
			$this->view->nomSystemeLieu = "champ";
			$this->view->nomImageLieu = "champs/todo.png";
			$this->view->nomChamp = $champ["nom_champ"];
			$this->view->descriptionLieu = "";
			$this->view->commentaireChamp = $champ["commentaire_champ"];
			$this->view->estFranchissableLieu = true;
			$this->view->estAlterableLieu = false;
			$this->view->paUtilisationLieu = 0;
			$this->view->niveauMinLieu = 0;

			$this->view->htmlLieu = $this->view->render("interface/lieux/champ.phtml");

			return true;
		} else {
			return false;
		}
	}

	private function prepareCommunaute($lieu) {
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("Bral_Helper_Communaute");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Bral_Util_Communaute");

		$communauteTable = new Communaute();
		$communautes = $communauteTable->findById($this->view->idCommunauteLieu);

		if ($communautes != null && count($communautes) == 1) {
			$communaute = $communautes[0];
			$this->view->nomCommunauteLieu = $communaute["nom_communaute"];
			$this->view->descriptionCommunauteLieu = $communaute["description_communaute"];

			if ($lieu["id_type_lieu"] != TypeLieu::ID_TYPE_HALL) {
				$this->view->coutsConstruction = Bral_Util_Communaute::getCoutsAmeliorationBatiment($lieu["niveau_prochain_lieu"]);
			}
		}
	}

}
