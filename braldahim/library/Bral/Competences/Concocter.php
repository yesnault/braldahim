<?php

class Bral_Competences_Concocter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->concocterEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->concocterEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit && 
				$e["nom_systeme_metier"] == "apothicaire" && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->concocterEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}
		
		if ($this->view->concocterEchoppeOk == false) {
			return;
		}
		
		Zend_Loader::loadClass("EchoppePartiePlante");
		$tabPartiePlantes = null;
		$echoppePlanteTable = new EchoppePartiePlante();
		$partiesPlantes = $echoppePlanteTable->findByIdEchoppe($idEchoppe);
		
		$this->view->nb_arrierePlante = 0;
		$this->view->concocterPlanteOk = false;
		
		$i = 0;
		if ($partiesPlantes != null) {
			$i++;
			foreach ($partiesPlantes as $m) {
				if ($m["quantite_arriere_echoppe_partieplante"] > 1) {
					$tabPartiePlantes[] = array(
					"indicateur" => $i,
					"id_type" => $m["id_fk_type_echoppe_partieplante"],
					"id_type_plante" => $m["id_fk_type_plante_echoppe_partieplante"],
					"nom_type_partieplante" => $m["nom_type_partieplante"],
					"nom_type" => $m["nom_type_plante"],
					"quantite_arriere" => $m["quantite_arriere_echoppe_partieplante"],
					"quantite_preparees" => $m["quantite_preparees_echoppe_partieplante"],
					);
					$this->view->concocterPlanteOk = true;
				}
			}
		}
		$this->view->partiesPlantes = $tabPartiePlantes;
		$this->idEchoppe = $idEchoppe;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass('Hobbit');

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification chasse
		if ($this->view->concocterEchoppeOk == false || $this->view->concocterPlanteOk == false) {
			throw new Zend_Exception(get_class($this)." Concocter interdit ");
		}
		
		$indicateur = $this->request->get("valeur_1");
		if ($indicateur == null ) {
			throw new Zend_Exception(get_class($this)." Plante inconnue ");
		}
		$planteOk = false;;
		foreach($this->view->partiesPlantes as $t) {
			if ($t["indicateur"] == $indicateur) {
				$planteOk = true;
				$this->view->planteNomType = $t["nom_type"];
				$this->view->planteNomTypePartiePlante = $t["nom_type_partieplante"];
				$idTypePartiePlante = $t["id_type"];
				$idTypePlante = $t["id_type_plante"];
				break;
			}
		}
		if ($planteOk == false) {
			throw new Zend_Exception(get_class($this)." Plante invalide");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculConcocter($idTypePartiePlante, $idTypePlante);
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculConcocter($idTypePartiePlante, $idTypePlante) {
		//2 unités de plante donne 1D2 plante(s) preparee(s)
		$this->view->nbPartiesPlantesPreparees = Bral_Util_De::get_1d2();
		
		$echoppePlanteTable = new EchoppePartiePlante();
		$data = array(
			'id_fk_type_echoppe_partieplante' => $idTypePartiePlante,
			'id_fk_type_plante_echoppe_partieplante' => $idTypePlante,
			'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe,
			'quantite_preparees_echoppe_partieplante' => $this->view->nbPartiesPlantesPreparees,
			'quantite_arriere_echoppe_partieplante' => -2,
		);
		$echoppePlanteTable->insertOrUpdate($data);
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
