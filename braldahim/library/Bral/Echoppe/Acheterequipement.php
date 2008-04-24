<?php

class Bral_Echoppe_Acheterequipement extends Bral_Echoppe_Echoppe {
	
	private $equipement = null;
	private $idEchoppe = null;

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter une equipement";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppePartiePlante");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartiePlante");
		Zend_Loader::loadClass("LabanEquipement");
		
		$this->idEquipement = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}
		
		$this->idEchoppe = $echoppeRowset[0]["id_echoppe"];
		
		$this->prepareEquipement($this->idEquipement);
		$this->preparePrix();
	}

	private function prepareEquipement($idEquipement) {
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($this->idEchoppe);
		
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$trouve = false;
		foreach ($equipements as $p) {
			if ($p["id_echoppe_equipement"] == $idEquipement && $p["type_vente_echoppe_equipement"] == "publique") {
				$trouve = true;
				$this->equipement = $p;
			}
			$idEquipements[] = $p["id_echoppe_equipement"];
		}
		
		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::equipement invalide");
		}
		
		$echoppEquipementMineraiTable = new EchoppeEquipementMinerai();
		$echoppeEquipementMinerai = $echoppEquipementMineraiTable->findByIdsEquipement($idEquipements);
				
		$minerai = null;
		if (count($echoppeEquipementMinerai) > 0) {
			foreach($echoppeEquipementMinerai as $r) {
				if ($r["id_fk_echoppe_equipement_minerai"] == $this->equipement["id_echoppe_equipement"]) {
					$possible = false;
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"] 
						    && $r["prix_echoppe_equipement_minerai"] <= $m["quantite_brut_laban_minerai"]) {
						    $possible = true;
						    break;
						}
					}
					
					$minerai[] = array(
						"prix_echoppe_equipement_minerai" => $r["prix_echoppe_equipement_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"id_fk_type_minerai" => $r["id_fk_type_echoppe_equipement_minerai"],
						"possible" => $possible,
					);
				}
			}
		}
		
		$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
		$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$partiesPlantes = null;
		if (count($echoppeEquipementPartiePlante) > 0) {
			foreach($echoppeEquipementPartiePlante as $a) {
				if ($a["id_fk_echoppe_equipement_partieplante"] == $this->equipement["id_echoppe_equipement"]) {
					$possible = false;
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"] 
							&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"] 
							&& $a["prix_echoppe_equipement_partieplante"] <= $p["quantite_laban_partieplante"] ) {
						 	$possible = true;
						    break;
						}
					}
				
					$partiesPlantes[] = array(
						"prix_echoppe_equipement_partieplante" => $a["prix_echoppe_equipement_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"id_fk_type_plante" => $a["id_fk_type_plante_echoppe_equipement_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_echoppe_equipement_partieplante"],
						"possible" => $possible,
					);
				}
			}
		}
		
		$tabEquipement = array(
			"id_equipement" => $this->equipement["id_echoppe_equipement"],
			"nom" => $this->equipement["nom_type_equipement"],
			"prix_1_vente_echoppe_equipement" => $this->equipement["prix_1_vente_echoppe_equipement"],
			"prix_2_vente_echoppe_equipement" => $this->equipement["prix_2_vente_echoppe_equipement"],
			"prix_3_vente_echoppe_equipement" => $this->equipement["prix_3_vente_echoppe_equipement"],
			"unite_1_vente_echoppe_equipement" => $this->equipement["unite_1_vente_echoppe_equipement"],
			"unite_2_vente_echoppe_equipement" => $this->equipement["unite_2_vente_echoppe_equipement"],
			"unite_3_vente_echoppe_equipement" => $this->equipement["unite_3_vente_echoppe_equipement"],
			"commentaire_vente_echoppe_equipement" => $this->equipement["commentaire_vente_echoppe_equipement"],
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
		);
		
		$this->view->equipement = $tabEquipement;
	}

	private function preparePrix() {
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($laban) != 1) {
			throw new Zend_Exception(get_class($this)."::laban invalide =! 1");
		} else {
			$laban = $laban[0];
		}
		
		$e = $this->view->equipement;
		$tabPrix = null;
		
		$possible = false;
		$acheterOk = false;
		
	   	if ($e["prix_1_vente_echoppe_equipement"] > 0) {
	    	$prix = $e["prix_1_vente_echoppe_equipement"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_equipement"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_equipement"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_1_vente_echoppe_equipement"]);
    	}
    	
    	if ($e["prix_2_vente_echoppe_equipement"] > 0) {
	    	$prix = $e["prix_2_vente_echoppe_equipement"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_equipement"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_equipement"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_2_vente_echoppe_equipement"]);
    	}	
	    
    	if ($e["prix_3_vente_echoppe_equipement"] > 0) {
	    	$prix = $e["prix_3_vente_echoppe_equipement"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_equipement"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_equipement"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_3_vente_echoppe_equipement"]);
    	}
    	
    	if (count($e["prix_minerais"]) > 0) {
	    	foreach($e["prix_minerais"] as $m) {
	     		if ($m["possible"] === true) {
	     			$acheterOk = true;
	     		}
		    	$prix = $m["prix_echoppe_equipement_minerai"];
	    		$nom = htmlentities($m["nom_type_minerai"]);
	    		$type = "minerais";
	    		$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "minerais" => $m, "possible" => $m["possible"]);
	    	}
    	}
    	
    	if (count($e["prix_parties_plantes"]) > 0) {
    	 	foreach($e["prix_parties_plantes"] as $p) {
	     		if ($p["possible"] === true) {
	     			$acheterOk = true;
	     		}
		    	$prix = $p["prix_echoppe_equipement_partieplante"]. " ";
		    	$s = "";
		    	if ($p["prix_echoppe_equipement_partieplante"] > 1) {
		    		$s = "s";
		    	}
	    		$nom = htmlentities($p["nom_type_partieplante"]). "$s ";
	    		$nom .= htmlentities($p["prefix_type_plante"]);
	    		$nom .= htmlentities($p["nom_type_plante"]);
	    		$type = "parties_plantes";
	    		$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "parties_plantes" => $p, "possible" => $p["possible"]);
    	 	}
    	}
    	
    	$this->view->acheterOk = $acheterOk;
    	$this->view->prix = $tabPrix;
	}
	
	private function calculPrixUnitaire($laban, $prix, $nomSysteme) {
		$retour = false;
		if ($nomSysteme == "rondin") {
			$charretteTable = new Charrette();
			$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
			if (count($charrette) == 1) {
				$charrette = $charrette[0];
				if ($charrette["quantite_rondin_charrette"] >= $prix) {
					$retour = true;
				}
			}
		} elseif ($nomSysteme == "peau") {
			if ($laban["quantite_peau_laban"] >= $prix) {
				$retour = true;
			}
		} elseif ($nomSysteme == "castar") {
			if ($this->view->user->castars_hobbit >= $prix) {
				$retour = true;
			}
		}
		return $retour;
	}
	
	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		
		$idPrix = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		
		// on verifie que idPrix est dans la liste des prix Ok.
		if (!array_key_exists($idPrix, $this->view->prix)) {
			throw new Zend_Exception(get_class($this)."::prix invalide. non connu");
		}
		
		// on verifie que le hobbit a assez de ressources.
		if ($this->view->prix[$idPrix]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)."::prix invalide");
		}
		
		if ($this->view->prix[$idPrix]["type"] == "echoppe") {
			$this->calculAchatEchoppe($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "minerais") {
			$this->calculAchatMinerais($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "parties_plantes") {
			$this->calculAchatPartiesPlantes($this->view->prix[$idPrix]);
		}	

		$this->calculTransfert();
	}
	
	private function calculAchatEchoppe($prix) {
		$echoppeTable = new Echoppe();
		
		if (Bral_Util_Registre::getNomUnite($prix["unite"], true) == "rondin") {
			$charretteTable = new Charrette();
			$data = array(
				'quantite_rondin_charrette' => -$prix["prix"],
				'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
			);
			$charretteTable->updateCharrette($data);
			
			$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_rondin_caisse_echoppe' => $prix["prix"],
			);
			$echoppeTable->insertOrUpdate($data);
			
		} elseif (Bral_Util_Registre::getNomUnite($prix["unite"], true)  == "peau") {
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_peau_laban' => -$prix["prix"],
			);
			$labanTable->insertOrUpdate($data);
			
			$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_peau_caisse_echoppe' => $prix["prix"],
			);
			$echoppeTable->insertOrUpdate($data);
		} elseif (Bral_Util_Registre::getNomUnite($prix["unite"], true)  == "castar") {
			$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $prix["prix"];
			
			$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_castar_caisse_echoppe' => $prix["prix"],
			);
			$echoppeTable->insertOrUpdate($data);
		}
	}
	
	private function calculAchatMinerais($prix) {
		$labanMineraiTable = new LabanMinerai();
		$data = array(
			'id_fk_type_laban_minerai' => $prix["minerais"]["id_fk_type_minerai"],
			'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
			'quantite_brut_laban_minerai' => - $prix["prix"],
		);
		$labanMineraiTable->insertOrUpdate($data);
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$data = array(
			'id_fk_type_echoppe_minerai' => $prix["minerais"]["id_fk_type_minerai"],
			'id_fk_echoppe_echoppe_minerai' => $this->idEchoppe,
			'quantite_caisse_echoppe_minerai' => $prix["prix"],
		);
		$echoppeMineraiTable->insertOrUpdate($data);
	}
	
	private function calculAchatPartiesPlantes($prix) {
		$labanPartiePlanteTable = new LabanPartieplante();
		$data = array(
			'id_fk_type_laban_partieplante' => $prix["parties_plantes"]["id_fk_type_partieplante"],
			'id_fk_type_plante_laban_partieplante' => $prix["parties_plantes"]["id_fk_type_plante"],
			'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
			'quantite_laban_partieplante' => - $prix["prix"],
		);
		$labanPartiePlanteTable->insertOrUpdate($data);
		
		$echoppePartiePlanteTable = new EchoppePartieplante();
		
		$data = array('quantite_caisse_echoppe_partieplante' => $prix["prix"],
					  'id_fk_type_echoppe_partieplante' => $prix["parties_plantes"]["id_fk_type_partieplante"],
					  'id_fk_type_plante_echoppe_partieplante' => $prix["parties_plantes"]["id_fk_type_plante"],
					  'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe,
					 );
		$echoppePartiePlanteTable->insertOrUpdate($data);
	}
	
	private function calculTransfert() {
		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $this->equipement["id_echoppe_equipement"],
			'id_fk_type_laban_equipement' => $this->equipement["id_fk_type_equipement_echoppe_equipement"],
			'id_fk_hobbit_laban_equipement' => $this->view->user->id_hobbit,
			'id_fk_type_qualite_laban_equipement' => $this->equipement["id_fk_type_qualite_echoppe_equipement"],
			'niveau_laban_equipement' => $this->equipement["niveau_echoppe_equipement"],
		);
		$labanEquipementTable->insert($data);
		
		$echoppeEquipementTable = new EchoppeEquipement();
		$where = "id_echoppe_equipement=".$this->equipement["id_echoppe_equipement"];
		$echoppeEquipementTable->delete($where);
	}
	
	function getListBoxRefresh() {
		return array("box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}