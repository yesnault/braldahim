<?php

class Bral_Echoppes_Deposerressources extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass('Laban');
		
		$id_echoppe = $this->request->get("valeur_1");
		
		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}
		
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$tabEchoppe = array(
				'id_echoppe' => $e["id_echoppe"],
				'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
				'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
				'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
				'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
				'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
				);
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}
		
		$this->view->echoppe = $tabEchoppe;
		
		$tabLaban["nb_peau"] = 0;
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		foreach ($laban as $p) {
			$tabLaban = array(
			"nb_peau" => $p["quantite_peau_laban"],
			"nb_cuir" => $p["quantite_cuir_laban"],
			"nb_fourrure" => $p["quantite_fourrure_laban"],
			"nb_planche" => $p["quantite_planche_laban"],
			);
		}
		
		
		$tabCharrette["nb_rondin"] = 0;
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		if ($charrette != null && count($charrette) > 0) {
			foreach ($charrette as $c) {
				$tabCharrette = array(
				"nb_rondin" => $c["quantite_rondin_charrette"],
				);
			}
		}
		
		$this->view->deposerRessourcesOk = false;
		if ($tabLaban["nb_peau"] > 0 && $tabCharrette["nb_rondin"] > 0) {
			$this->view->deposerRessourcesOk = true;
		}
		$this->prepareCommunRessources();
		
		$this->view->charretteOk = false;
		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->charretteOk = true;
		}
		
		$this->view->laban = $tabLaban;
		$this->view->charrette = $tabCharrette;
		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->deposerRessourcesOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$nb_rondins = $this->request->get("valeur_2");
		$nb_peau = $this->request->get("valeur_3");
		$nb_cuir = $this->request->get("valeur_4");
		$nb_fourrure = $this->request->get("valeur_5");
		$nb_planche = $this->request->get("valeur_6");
		
		if ((int) $nb_rondins."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." NB Rondins invalide=".$nb_rondins);
		} else {
			$nb_rondins = (int)$nb_rondins;
		}
		if ((int) $nb_peau."" != $this->request->get("valeur_3")."") {
			throw new Zend_Exception(get_class($this)." NB Peau invalide=".$nb_peau);
		} else {
			$nb_peau = (int)$nb_peau;
		}
		if ((int) $nb_cuir."" != $this->request->get("valeur_4")."") {
			throw new Zend_Exception(get_class($this)." NB Cuir invalide=".$nb_cuir);
		} else {
			$nb_cuir = (int)$nb_cuir;
		}
		if ((int) $nb_fourrure."" != $this->request->get("valeur_5")."") {
			throw new Zend_Exception(get_class($this)." NB Fourrure invalide=".$nb_fourrure);
		} else {
			$nb_fourrure = (int)$nb_fourrure;
		}
		if ((int) $nb_planche."" != $this->request->get("valeur_6")."") {
			throw new Zend_Exception(get_class($this)." NB Planche invalide=".$nb_planche);
		} else {
			$nb_planche = (int)$nb_planche;
		}
		
		
		if ($nb_rondins > $this->view->charrette["nb_rondin"]) {
			throw new Zend_Exception(get_class($this)." NB Rondin interdit=".$nb_rondins);
		}	
		if ($nb_peau > $this->view->laban["nb_peau"]) {
			throw new Zend_Exception(get_class($this)." NB Peau interdit=".$nb_peau);
		}
		if ($nb_cuir > $this->view->laban["nb_cuir"]) {
			throw new Zend_Exception(get_class($this)." NB Cuir interdit=".$nb_cuir);
		}
		if ($nb_fourrure > $this->view->laban["nb_fourrure"]) {
			throw new Zend_Exception(get_class($this)." NB Fourrure interdit=".$nb_fourrure);
		}
			if ($nb_planche > $this->view->laban["nb_planche"]) {
			throw new Zend_Exception(get_class($this)." NB Planche interdit=".$nb_planche);
		}
		
		$this->view->elementsRetires = "";
		$this->calculEchoppe($nb_rondins, $nb_peau, $nb_cuir, $nb_fourrure, $nb_planche);
		$this->calculPartiesPlantes();
		$this->calculMinerais();
		if ($this->view->elementsRetires != "") {
			$this->view->elementsRetires = substr($this->view->elementsRetires, 0, -2);
		}
	}
	
	private function calculEchoppe($nb_rondins, $nb_peau, $nb_cuir, $nb_fourrure, $nb_planche) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Laban");
		
		$echoppeTable = new Echoppe();
		
		if ($nb_peau > 0) {
			// on retire du laban
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_peau_laban' => -$nb_peau,
			);
			$labanTable->insertOrUpdate($data);
			$this->view->elementsRetires .= $nb_peau. " peau";
			if ($nb_peau > 1) $this->view->elementsRetires .= "x";
			$this->view->elementsRetires .= ", ";
		}
		if ($nb_cuir > 0) {
			// on retire du laban
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_cuir_laban' => -$nb_cuir,
			);
			$labanTable->insertOrUpdate($data);
			$this->view->elementsRetires .= $nb_cuir. " cuir";
			if ($nb_cuir > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}
		if ($nb_fourrure > 0) {
			// on retire du laban
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_fourrure_laban' => -$nb_fourrure,
			);
			$labanTable->insertOrUpdate($data);
			$this->view->elementsRetires .= $nb_fourrure. " fourrure";
			if ($nb_fourrure > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}		
		if ($nb_planche > 0) {
			// on retire du laban
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_planche_laban' => -$nb_planche,
			);
			$labanTable->insertOrUpdate($data);
			$this->view->elementsRetires .= $nb_planche. " planche";
			if ($nb_planche > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}	
		
		if ($nb_rondins > 0) {
			// on retire de la charette
			if ($this->view->charretteOk === true) {
				$charretteTable = new Charrette();
				$data = array(
					'quantite_rondin_charrette' => -$nb_rondins,
					'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
				);
				$charretteTable->updateCharrette($data);
				
				$this->view->elementsRetires .= $nb_rondins. " rondin";
				if ($nb_rondins > 1) $this->view->elementsRetires .= "s";
				$this->view->elementsRetires .= ", ";
				
				$nb_rondins = $this->view->echoppe["quantite_rondin_arriere_echoppe"] + $nb_rondins;
			} else {
				$nb_rondins = $this->view->echoppe["quantite_rondin_arriere_echoppe"];
			}
		}
		
		if ($nb_peau < 0) $nb_peau = 0;
		
		$data = array(
				'quantite_rondin_arriere_echoppe' => $nb_rondins,
				'quantite_peau_arriere_echoppe' => $this->view->echoppe["quantite_peau_arriere_echoppe"] + $nb_peau,
				'quantite_cuir_arriere_echoppe' => $this->view->echoppe["quantite_cuir_arriere_echoppe"] + $nb_cuir,
				'quantite_fourrure_arriere_echoppe' => $this->view->echoppe["quantite_fourrure_arriere_echoppe"] + $nb_fourrure,
				'quantite_planche_arriere_echoppe' => $this->view->echoppe["quantite_planche_arriere_echoppe"] + $nb_planche,
		);
		$where = "id_echoppe=".$this->view->echoppe["id_echoppe"];
		$echoppeTable->update($data, $where);
		
	}
	
	private function calculPartiesPlantes() {
		Zend_Loader::loadClass("EchoppePartiePlante");
		Zend_Loader::loadClass('LabanPartieplante');
		
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$labanPartiePlanteTable = new LabanPartieplante();
		
		for($i=7; $i<=$this->view->valeur_fin_partieplantes; $i++) {
			$indice = "valeur_".$i;
			$nb = $this->request->get($indice);
			if ((int) $nb."" != $this->request->get("valeur_".$i)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante invalide=".$nb);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->partieplantes[$indice]["quantite_laban_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante interdit=".$nb);
			}
			if ($nb > 0) {
				$data = array('quantite_arriere_echoppe_partieplante' => $nb,
							  'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
							  'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
							  'id_fk_echoppe_echoppe_partieplante' => $this->view->idEchoppe);
				$echoppePartiePlanteTable->insertOrUpdate($data);
				
				$data = array(
						'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
						'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
						'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
						'quantite_laban_partieplante' => -$nb,
				);
				$labanPartiePlanteTable->insertOrUpdate($data);
				$this->view->elementsRetires .= $this->view->partieplantes[$indice]["nom_plante"]. " : ".$nb. " ".$this->view->partieplantes[$indice]["nom_type"];
				if ($nb > 1) $this->view->elementsRetires .= "s";
				$this->view->elementsRetires .= ", ";
			}
		}
	}

	private function calculMinerais() {
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass('LabanMinerai');
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$labanMineraiTable = new LabanMinerai();
		
		for($i=$this->view->valeur_fin_partieplantes + 1; $i<=$this->view->nb_valeurs; $i = $i + 2) {
			$indiceBrut = "valeur_".($i-1);
			$indiceLingot = "valeur_".$i;
			$nbBrut = $this->request->get($indiceBrut);
			$nbLingot = $this->request->get($indiceLingot);
			
			if ((int) $nbBrut."" != $this->request->get($indiceBrut)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai brut invalide=".$nbBrut. " indice=".$indiceBrut);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indiceBrut]["quantite_brut_laban_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}
			
			if ((int) $nbLingot."" != $this->request->get($indiceLingot)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot invalide=".$nbLingot. " indice=".$indiceLingot);
			} else {
				$nbLingot = (int)$nbLingot;
			}
			if ($nbLingot > $this->view->minerais[$indiceLingot]["quantite_lingots_laban_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot interdit=".$nbLingot);
			}
			
			if ($nbBrut > 0 || $nbLingot > 0) {
				$data = array('quantite_arriere_echoppe_minerai' => $nb,
							  'id_fk_type_echoppe_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
							  'id_fk_echoppe_echoppe_minerai' => $this->view->idEchoppe);
				$echoppeMineraiTable->insertOrUpdate($data);
				
				$data = array(
				'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
				'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
				'quantite_brut_laban_minerai' => -$nbBrut,
				'quantite_lingots_laban_minerai' => -$nbLingot,
				);
		
				$labanMineraiTable->insertOrUpdate($data);
				$this->view->elementsRetires .= $this->view->minerais[$indice]["type"]. " : ".$nb;
				$this->view->elementsRetires .= ", ";
			}
		}
	}
	
	private function prepareCommunRessources() {
		Zend_Loader::loadClass("LabanPartiePlante");
		Zend_Loader::loadClass("LabanMinerai");

		$tabPartiePlantes = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$this->view->nb_valeurs = 6;
		$this->view->nb_partiePlantes = 0;
		
		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_laban_partieplante"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabPartiePlantes["valeur_".$this->view->nb_valeurs] = array(
					"nom_type" => $p["nom_type_partieplante"],
					"nom_plante" => $p["nom_type_plante"],
					"id_fk_type_laban_partieplante" => $p["id_fk_type_laban_partieplante"],
					"id_fk_type_plante_laban_partieplante" => $p["id_fk_type_plante_laban_partieplante"],
					"id_fk_hobbit_laban_partieplante" => $p["id_fk_hobbit_laban_partieplante"],
					"quantite_laban_partieplante" => $p["quantite_laban_partieplante"],
					"indice_valeur" => $this->view->nb_valeurs,
					);
					if ($p["quantite_laban_partieplante"] > 0) {
						$this->view->deposerRessourcesOk = true;
					}
					$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_laban_partieplante"];
				}
			}
		}
		
		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;
		
		$tabMinerais = null;
		$labanMineraiTable = new labanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->nb_minerai_brut = 0;
		$this->view->nb_minerai_lingot = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_laban_minerai"] > 0 || $m["quantite_lingots_laban_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
					"type" => $m["nom_type_minerai"],
					"id_fk_type_laban_minerai" => $m["id_fk_type_laban_minerai"],
					"id_fk_hobbit_laban_minerai" => $m["id_fk_hobbit_laban_minerai"],
					"quantite_brut_laban_minerai" => $m["quantite_brut_laban_minerai"],
					"quantite_lingots_laban_minerai" => $m["quantite_lingots_laban_minerai"],
					"indice_valeur" => $this->view->nb_valeurs,
					);
					if ($m["quantite_brut_laban_minerai"] > 0 || $m["quantite_lingots_laban_minerai"] > 0) {
						$this->view->deposerRessourcesOk = true;
					}
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // lingot
					$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_laban_minerai"];
					$this->view->nb_minerai_lingot = $this->view->nb_minerai_lingot + $m["quantite_lingots_laban_minerai"];
				}
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
		return array("box_laban", "box_charrette", "box_profil");
	}
}