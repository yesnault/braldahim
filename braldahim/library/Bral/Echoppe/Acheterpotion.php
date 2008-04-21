<?php

class Bral_Echoppe_Acheterpotion extends Bral_Echoppe_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("EchoppePotionMinerai");
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartiePlante");
		
		$idPotion = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}
		
		$this->preparePotion($idPotion, $echoppeRowset[0]["id_echoppe"]);
		$this->preparePrix();
	}

	private function preparePotion($idPotion, $idEchoppe) {
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);
		
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$trouve = false;
		foreach ($potions as $p) {
			if ($p["id_echoppe_potion"] == $idPotion && $p["type_vente_echoppe_potion"] == "publique") {
				$trouve = true;
				$potion = $p;
			}
			$idPotions[] = $p["id_echoppe_potion"];
		}
		
		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::potion invalide");
		}
		
		$echoppPotionMineraiTable = new EchoppePotionMinerai();
		$echoppePotionMinerai = $echoppPotionMineraiTable->findByIdsPotion($idPotions);
				
		$minerai = null;
		if (count($echoppePotionMinerai) > 0) {
			foreach($echoppePotionMinerai as $r) {
				if ($r["id_fk_echoppe_potion_minerai"] == $potion["id_echoppe_potion"]) {
					$possible = false;
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_type_minerai"] 
						    && $r["prix_echoppe_potion_minerai"] <= $m["quantite_brut_laban_minerai"]) {
						    $possible = true;
						    break;
						}
					}
					
					$minerai[] = array(
						"prix_echoppe_potion_minerai" => $r["prix_echoppe_potion_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"possible" => $possible,
					);
				}
			}
		}
		
		$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
		$echoppePotionPartiePlante = $echoppePotionPartiePlanteTable->findByIdsPotion($idPotions);
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$partiesPlantes = null;
		if (count($echoppePotionPartiePlante) > 0) {
			foreach($echoppePotionPartiePlante as $a) {
				if ($a["id_fk_echoppe_potion_partieplante"] == $potion["id_echoppe_potion"]) {
					$possible = false;
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"] 
							&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"] 
							&& $a["prix_echoppe_potion_partieplante"] <= $p["quantite_laban_partieplante"] ) {
						 	$possible = true;
						    break;
						}
					}
				
					$partiesPlantes[] = array(
						"prix_echoppe_potion_partieplante" => $a["prix_echoppe_potion_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"possible" => $possible,
					);
				}
			}
		}
		
		$tabPotion = array(
			"id_potion" => $potion["id_echoppe_potion"],
			"nom" => $potion["nom_type_potion"],
			"qualite" => $potion["nom_type_qualite"],
			"niveau" => $potion["niveau_echoppe_potion"],
			"caracteristique" => $potion["caract_type_potion"],
			"bm_type" => $potion["bm_type_potion"],
			"prix_1_vente_echoppe_potion" => $potion["prix_1_vente_echoppe_potion"],
			"prix_2_vente_echoppe_potion" => $potion["prix_2_vente_echoppe_potion"],
			"prix_3_vente_echoppe_potion" => $potion["prix_3_vente_echoppe_potion"],
			"unite_1_vente_echoppe_potion" => $potion["unite_1_vente_echoppe_potion"],
			"unite_2_vente_echoppe_potion" => $potion["unite_2_vente_echoppe_potion"],
			"unite_3_vente_echoppe_potion" => $potion["unite_3_vente_echoppe_potion"],
			"commentaire_vente_echoppe_potion" => $potion["commentaire_vente_echoppe_potion"],
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
		);
		
		$this->view->potion = $tabPotion;
	}

	private function preparePrix() {
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($laban) != 1) {
			throw new Zend_Exception(get_class($this)."::laban invalide =! 1");
		} else {
			$laban = $laban[0];
		}
		
		$e = $this->view->potion;
		$tabPrix = null;
		
		$possible = false;
		$acheterOk = false;
		
	   	if ($e["prix_1_vente_echoppe_potion"] > 0) {
	    	$prix = $e["prix_1_vente_echoppe_potion"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_potion"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_potion"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible);
    	}
    	
    	if ($e["prix_2_vente_echoppe_potion"] > 0) {
	    	$prix = $e["prix_2_vente_echoppe_potion"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible);
    	}	
	    
    	if ($e["prix_3_vente_echoppe_potion"] > 0) {
	    	$prix = $e["prix_3_vente_echoppe_potion"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible);
    	}
    	
    	if (count($e["prix_minerais"]) > 0) {
	    	foreach($e["prix_minerais"] as $m) {
	     		if ($m["possible"] === true) {
	     			$acheterOk = true;
	     		}
		    	$prix = $m["prix_echoppe_potion_minerai"];
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
		    	$prix = $p["prix_echoppe_potion_partieplante"]. " ";
		    	$s = "";
		    	if ($p["prix_echoppe_potion_partieplante"] > 1) {
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
		
	}

	function prepareResultat() {
	//TODO
	}
	
	function getListBoxRefresh() {
	}
}