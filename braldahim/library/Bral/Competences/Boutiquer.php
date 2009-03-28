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
abstract class Bral_Competences_Boutiquer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("PetitEquipement");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->boutiquerEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->boutiquerEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
		
		$metier = substr($this->nom_systeme, 9, strlen($this->nom_systeme) - 9);
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit && 
				$e["nom_systeme_metier"] == $metier && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->boutiquerEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}
		
		if ($this->view->boutiquerEchoppeOk == false) {
			return;
		}
		$this->idEchoppe = $idEchoppe;
		$this->view->nomCompetenceBoutiquer = $this->nom_systeme;
		$this->boutiquerMetier = $metier;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification chasse
		if ($this->view->boutiquerEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Boutiquer interdit ");
		}
		
		$details = "[h".$this->view->user->id_hobbit."] a boutiqué";
		$id_type = $this->view->config->game->evenements->type->competence;
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBoutiquer();
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculBoutiquer() {
		
		$this->view->ameliorationCompetence = false;
		$this->view->tabCompetencesAmeliorees = null;
		
		$this->view->boutiquerMetierCourant = false;
		
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		foreach($hobbitsMetierRowset as $m) {
			if ($this->competence["id_fk_metier_competence"] == $m["id_metier"]) {
				if ($m["est_actif_hmetier"] == "oui") {
					$this->view->boutiquerMetierCourant  = true;
					break;
				}
			}
		}
		
		$de10 = Bral_Util_De::get_1d10();
		$deCompetence = 0;
		
		if ($de10 >= 9 && $this->view->boutiquerMetierCourant) {
			$this->view->ameliorationCompetence = true;
			$deCompetence = Bral_Util_De::get_1d2();
			if ($de10 == 9) {
				$this->updateCompetenceDb(2, $deCompetence);
			} else {
				$this->updateCompetenceDb(4, $deCompetence);
			}
		}
		
		$this->view->deCompetence = $deCompetence;
		
		$petitEquipementTable = new PetitEquipement();
		
		$equipements = $petitEquipementTable->findByIdMetier($this->competence["id_fk_metier_competence"]);
		if (count($equipements) < 6) {
			throw new Zend_Exception(get_class($this)." Equipements invalides : idMetier:".$this->competence["id_fk_metier_competence"]);
		}
		$de6 = Bral_Util_De::get_1d6();
		$petitEquipement = $equipements[$de6-1];
		
		$this->view->petitEquipement = $petitEquipement;
		
		$message = "";
		$nbCastars = 0;
		switch($de6) {
			case 1:
				$message = "Je vous le prend, mais c'est vraiment pour vous débarrasser.";
				$nbCastars = Bral_Util_De::get_1d10();
				break;
			case 2:
				$message = "Mmmh, je ne vois pas trop ce que je vais pouvoir faire de ça ...";
				$nbCastars = Bral_Util_De::get_1d10() + 5;
				break;
			case 3:
				$message = "Bon, je devrais pouvoir le revendre. Ça aurait pu être pire !";
				$nbCastars = Bral_Util_De::get_2d10();
				break;
			case 4:
				$message = "Je vous le prends tout de suite !";
				$nbCastars = Bral_Util_De::get_2d10() + 5;
				break;
			case 5:
				$message = "Génial, je connais quelqu'un qui en veut !";
				$nbCastars = Bral_Util_De::get_3d10();
				break;
			case 6:
				$message = "C'est exactement ce que je cherchais !!! Merci !";
				$nbCastars = Bral_Util_De::get_3d10() + 5;
				break;
			default:
				throw new Zend_Exception(get_class($this)." Erreur switch :".$de6);
				break;
		}
		$this->view->nbCastarsGagnes = $nbCastars;
		$this->view->message = $message;
		
		$echoppeTable = new Echoppe();
		$data = array(
			'id_echoppe' => $this->idEchoppe,
			'quantite_castar_caisse_echoppe' => $nbCastars,
		);
		$echoppeTable->insertOrUpdate($data);
	}
	
	private function updateCompetenceDb($nbPa, $deCompetence) {
		$hobbitsCompetencesTable = new HobbitsCompetences();
		
		$competences = $hobbitsCompetencesTable->findByIdHobbitAndNbPaAndNomSystemeMetier($this->view->user->id_hobbit, $nbPa, $this->boutiquerMetier);
		
		if ($competences == null || count($competences) == 0) {
			throw new Zend_Exception(get_class($this)." Competences invalides :".$this->view->user->id_hobbit.",". $nbPa.",".$this->boutiquerMetier);
		}
		
		$tabCompetencesAmeliorees = null;
		
		foreach ($competences as $c) {
			$pourcentage = $c["pourcentage_hcomp"] + $deCompetence;
			if ($pourcentage > $c["pourcentage_max_competence"]) { // % comp maximum
				$pourcentage = $c["pourcentage_max_competence"];
			}
			$data = array('pourcentage_hcomp' => $pourcentage);
			$where = array("id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
			$hobbitsCompetencesTable->update($data, $where);
			$tabCompetencesAmeliorees[] = $c;
		}
		$this->view->tabCompetencesAmeliorees = $tabCompetencesAmeliorees;
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_echoppes"));
	}
}
