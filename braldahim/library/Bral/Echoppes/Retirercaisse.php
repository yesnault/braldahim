<?php

class Bral_Echoppes_Retirercaisse extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass("Echoppe");
		
		$id_echoppe = $this->request->get("valeur_1");
		
		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}
		
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				$tabEchoppe = array(
				'id_echoppe' => $e["id_echoppe"],
				'quantite_rondin_caisse_echoppe' => $e["quantite_rondin_caisse_echoppe"],
				'quantite_peau_caisse_echoppe' => $e["quantite_peau_caisse_echoppe"],
				'quantite_castar_caisse_echoppe' => $e["quantite_castar_caisse_echoppe"],
				);
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}
		
		$this->view->echoppe = $tabEchoppe;
		
		$this->view->retirerCaisseOk = false;
		if ($this->view->echoppe["quantite_rondin_caisse_echoppe"] > 0 ||
			$this->view->echoppe["quantite_peau_caisse_echoppe"] > 0 ||
			$this->view->echoppe["quantite_castar_caisse_echoppe"] > 0) {
			$this->view->retirerCaisseOk = true;
		}
		$this->prepareCommunRessources($tabEchoppe["id_echoppe"]);
		
		$this->view->charetteOk = false;
		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->charetteOk = true;
		}
		
		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retirerCaisseOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$nb_castars = $this->request->get("valeur_2");
		$nb_rondins = $this->request->get("valeur_3");
		$nb_peau = $this->request->get("valeur_4");
		
		if ((int) $nb_castars."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." NB Castars invalide=".$nb_castars);
		} else {
			$nb_castars = (int)$nb_castars;
		}
		if ((int) $nb_rondins."" != $this->request->get("valeur_3")."") {
			throw new Zend_Exception(get_class($this)." NB Rondins invalide=".$nb_rondins);
		} else {
			$nb_rondins = (int)$nb_rondins;
		}
		if ((int) $nb_peau."" != $this->request->get("valeur_4")."") {
			throw new Zend_Exception(get_class($this)." NB Peau invalide=".$nb_peau);
		} else {
			$nb_peau = (int)$nb_peau;
		}
		
		$this->calculEchoppe($nb_rondins, $nb_peau, $nb_castars);
	}
	
	
	private function calculEchoppe($nb_rondins, $nb_peau, $nb_castars) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Laban");
		
		$echoppeTable = new Echoppe();
		
		// on place dans le laban
		$labanTable = new Laban();
		$data = array(
			'id_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_peau_laban' => $nb_peau,
		);
		$labanTable->insertOrUpdate($data);
		
		// on place dans la charette
		if ($this->view->charetteOk === true) {
			$charretteTable = new Charrette();
			$data = array(
				'quantite_rondin_charrette' => $nb_rondins,
				'id_hobbit_charrette' => $this->view->user->id_hobbit,
			);
			$charretteTable->updateCharrette($data);
			
			$nb_rondins = $this->view->echoppe["quantite_rondin_caisse_echoppe"] - $nb_rondins;
		} else {
			$nb_rondins = $this->view->echoppe["quantite_rondin_caisse_echoppe"];
		}
		
		$nb_peau = $this->view->echoppe["quantite_peau_caisse_echoppe"] - $nb_peau;
		$nb_castars = $this->view->echoppe["quantite_castar_caisse_echoppe"] - $nb_castars;
		if ($nb_peau < 0) $nb_peau = 0;
		if ($nb_castars < 0) $nb_castars = 0;
		
		$data = array(
				'quantite_rondin_caisse_echoppe' => $nb_rondins,
				'quantite_peau_caisse_echoppe' => $nb_peau,
				'quantite_castar_caisse_echoppe' => $nb_castars,
		);
		$where = "id_echoppe=".$this->view->echoppe["id_echoppe"];
		$echoppeTable->update($data, $where);
		
		$hobbitTable = new Hobbit();
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + $nb_castars;
		$data = array(
		'castars_hobbit'  => $this->view->user->castars_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
	}
	
	private function prepareCommunRessources($idEchoppe) {
		Zend_Loader::loadClass("EchoppePartiePlante");
		Zend_Loader::loadClass("EchoppeMinerai");

		$tabPartiePlantes = null;
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);
		
		$this->view->nb_valeurs = 4;
		$this->view->nb_caissePartiePlantes = 0;
		$this->view->nb_arrierePartiePlantes = 0;
		$this->view->nb_prepareePartiePlantes = 0;

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
				$tabPartiePlantes[] = array(
				"nom_type" => $p["nom_type_partieplante"],
				"nom_plante" => $p["nom_type_plante"],
				"quantite_caisse" => $p["quantite_caisse_echoppe_partieplante"],
				"indice_valeur" => $this->view->nb_valeurs,
				);
				if ($p["quantite_caisse_echoppe_partieplante"] > 0) {
					$this->view->retirerCaisseOk = true;
				}
				$this->view->nb_caissePartiePlantes = $this->view->nb_caissePartiePlantes + $p["quantite_caisse_echoppe_partieplante"];
			}
		}

		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caisseMinerai = 0;
		$this->view->nb_arriereMinerai = 0;
		$this->view->nb_lingotsMinerai = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
				$tabMinerais[] = array(
				"type" => $m["nom_type_minerai"],
				"quantite_caisse" => $m["quantite_caisse_echoppe_minerai"],
				"indice_valeur" => $this->view->nb_valeurs,
				);
				if ($m["quantite_caisse_echoppe_minerai"] > 0) {
					$this->view->retirerCaisseOk = true;
				}
				$this->view->nb_caisseMinerai = $this->view->nb_caisseMinerai + $m["quantite_caisse_echoppe_minerai"];
			}
		}

		$this->view->partieplantes = $tabPartiePlantes;
		$this->view->minerais = $tabMinerais;
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
	}
}