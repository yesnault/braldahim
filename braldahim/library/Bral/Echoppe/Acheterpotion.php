<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Echoppe_Acheterpotion extends Bral_Echoppe_Echoppe {
	
	private $potion = null;
	private $idEchoppe = null;

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter une potion";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("EchoppePotionMinerai");
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanPotion");
		
		$this->idPotion = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}
		
		$this->idEchoppe = $echoppeRowset[0]["id_echoppe"];
		
		$this->preparePotion($this->idPotion);
		$this->preparePrix();
	}

	private function preparePotion($idPotion) {
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($this->idEchoppe);
		
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$trouve = false;
		foreach ($potions as $p) {
			if ($p["id_echoppe_potion"] == $idPotion && $p["type_vente_echoppe_potion"] == "publique") {
				$trouve = true;
				$this->potion = $p;
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
				if ($r["id_fk_echoppe_potion_minerai"] == $this->potion["id_echoppe_potion"]) {
					$possible = false;
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"] 
						    && $r["prix_echoppe_potion_minerai"] <= $m["quantite_brut_laban_minerai"]) {
						    $possible = true;
						    break;
						}
					}
					
					$minerai[] = array(
						"prix_echoppe_potion_minerai" => $r["prix_echoppe_potion_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"id_fk_type_minerai" => $r["id_fk_type_echoppe_potion_minerai"],
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
				if ($a["id_fk_echoppe_potion_partieplante"] == $this->potion["id_echoppe_potion"]) {
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
						"id_fk_type_plante" => $a["id_fk_type_plante_echoppe_potion_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_echoppe_potion_partieplante"],
						"possible" => $possible,
					);
				}
			}
		}
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		
		if ($poidsRestant < Bral_Util_Poids::POIDS_POTION) {
			$placeDispo = false;
		} else {
			$placeDispo = true;
		}
		
		$tabPotion = array(
			"id_potion" => $this->potion["id_echoppe_potion"],
			"nom" => $this->potion["nom_type_potion"],
			"qualite" => $this->potion["nom_type_qualite"],
			"niveau" => $this->potion["niveau_echoppe_potion"],
			"caracteristique" => $this->potion["caract_type_potion"],
			"bm_type" => $this->potion["bm_type_potion"],
			"prix_1_vente_echoppe_potion" => $this->potion["prix_1_vente_echoppe_potion"],
			"prix_2_vente_echoppe_potion" => $this->potion["prix_2_vente_echoppe_potion"],
			"prix_3_vente_echoppe_potion" => $this->potion["prix_3_vente_echoppe_potion"],
			"unite_1_vente_echoppe_potion" => $this->potion["unite_1_vente_echoppe_potion"],
			"unite_2_vente_echoppe_potion" => $this->potion["unite_2_vente_echoppe_potion"],
			"unite_3_vente_echoppe_potion" => $this->potion["unite_3_vente_echoppe_potion"],
			"commentaire_vente_echoppe_potion" => $this->potion["commentaire_vente_echoppe_potion"],
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
			"place_dispo" => $placeDispo,
		);
		
		$this->view->potion = $tabPotion;
	}

	private function preparePrix() {
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($laban) != 1) {
			$laban = null;
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
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_1_vente_echoppe_potion"]);
    	}
    	
    	if ($e["prix_2_vente_echoppe_potion"] > 0) {
	    	$prix = $e["prix_2_vente_echoppe_potion"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_2_vente_echoppe_potion"]);
    	}	
	    
    	if ($e["prix_3_vente_echoppe_potion"] > 0) {
	    	$prix = $e["prix_3_vente_echoppe_potion"];
	    	$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"]);
	    	$type = "echoppe";
	    	$possible = $this->calculPrixUnitaire($laban, $prix, Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"], true));
	    	$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_3_vente_echoppe_potion"]);
    	}
    	
    	if (count($e["prix_minerais"]) > 0) {
	    	foreach($e["prix_minerais"] as $m) {
	     		if ($m["possible"] === true) {
	     			$acheterOk = true;
	     		}
		    	$prix = $m["prix_echoppe_potion_minerai"];
	    		$nom = htmlspecialchars($m["nom_type_minerai"]);
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
	    		$nom = htmlspecialchars($p["nom_type_partieplante"]). "$s ";
	    		$nom .= htmlspecialchars($p["prefix_type_plante"]);
	    		$nom .= htmlspecialchars($p["nom_type_plante"]);
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
		} elseif ($nomSysteme == "peau" && $laban != null) {
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
		$labanPotionTable = new LabanPotion();
		$data = array(
			'id_laban_potion' => $this->potion["id_echoppe_potion"],
			'id_fk_type_laban_potion' => $this->potion["id_fk_type_potion_echoppe_potion"],
			'id_fk_hobbit_laban_potion' => $this->view->user->id_hobbit,
			'id_fk_type_qualite_laban_potion' => $this->potion["id_fk_type_qualite_echoppe_potion"],
			'niveau_laban_potion' => $this->potion["niveau_echoppe_potion"],
		);
		$labanPotionTable->insert($data);
		
		$echoppePotionTable = new EchoppePotion();
		$where = "id_echoppe_potion=".$this->potion["id_echoppe_potion"];
		$echoppePotionTable->delete($where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_equipement", "box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}