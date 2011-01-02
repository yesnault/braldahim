<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Echoppe_Acheterlot extends Bral_Echoppe_Echoppe {

	private $lot = null;

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter un lot";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_Lot");
		Zend_Loader::loadClass("Lot");

		$this->view->idLot = Bral_Util_Controle::getValeurIntVerif($this->request->get("idLot"));

		$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "poids_restant" => $poidsRestant, "possible" => false, "possible_force" => false);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "poids_restant" => $poidsRestant, "possible" => false, "possible_force" => false);
		}
		$this->view->destinationTransfert = $tabDestinationTransfert;

		$this->view->charrette = $charrette;

		$this->prepareLot();
		$this->preparePrix();
	}

	private function prepareLot() {

		$lotTable = new Lot();
		$lots = $lotTable->findByIdEchoppe($this->idEchoppe, $this->view->idLot);

		$trouve = false;
		foreach ($lots as $p) {
			if ($this->view->idLot = $p["id_lot"]) {
				$trouve = true;
				$this->lot = $p;
				break;
			}
		}

		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::lot invalide:".$this->view->idLot);
		}

		Zend_Loader::loadClass("Bral_Util_Lot");
		$lot = Bral_Util_Lot::getLotByIdLot($this->view->idLot);
		if ($lots == null) {
			throw new Zend_Exception(get_class($this)."::lot invalide 2 :".$this->view->idLot);
		}

		$this->view->lot = $lot;
	}

	private function preparePrix() {
		$tabPrix = null;

		$possible = false;
		$acheterOk = false;

		$placeDispo = false;

		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $this->lot["poids_lot"]) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i++;
		}

		for ($k = 1; $k <= 3; $k++) {
			if ($this->view->lot["prix_".$k."_lot"] >= 0 && $this->view->lot["unite_".$k."_lot"] > 0) {
				$prix = $this->view->lot["prix_".$k."_lot"];
				$nom = Bral_Util_Registre::getNomUnite($this->view->lot["unite_".$k."_lot"], false, $this->view->lot["prix_".$k."_lot"]);
				$nomSystemeUnite = Bral_Util_Registre::getNomUnite($this->view->lot["unite_".$k."_lot"], true);
				$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
				$type = "echoppe";
				$i = 0;
				foreach($this->view->destinationTransfert as $d) {
					$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
					if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->lot["poids_lot"] - $poidsPrix) {
						$placeDispo = true;
						$this->view->destinationTransfert[$i]["possible_force"] = true;
					}
					$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $this->view->lot["unite_".$k."_lot"], "id_destination" => $d["id_destination"]);
					$i++;
				}
			}
		}

		if (count($this->view->lot["prix_minerais"]) > 0) {
			foreach($this->view->lot["prix_minerais"] as $m) {

				foreach($this->view->destinationTransfert as $destination) {
					$possible = false;

					if ($d["id_destination"] == "laban") {
						Zend_Loader::loadClass("LabanMinerai");
						$table = new LabanMinerai();
						$minerais = $table->findByIdBraldun($this->view->user->id_braldun);
					} else {
						Zend_Loader::loadClass("CharretteMinerai");
						$table = new CharretteMinerai();
						$minerais = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
					}

					foreach ($minerais as $m) {

						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"]
						&& $r["prix_lot_prix_minerai"] <= $m["quantite_brut_".$destination["id_destination"]."_minerai"]) {
							$acheterOk = true;
							$possible = true;
							break;
						}
					}

					$prix = $m["prix_lot_prix_minerai"];
					$nom = htmlspecialchars($m["nom_type_minerai"]);
					$type = "minerais";
					$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "minerais" => $m, "possible" => $possible, "id_destination" => $destination["id_destination"]);

				}
					
				/*if ($m["place_dispo_force"] == true) {
					$placeDispo = true;
					}*/
			}
		}

		if (count($this->view->lot["prix_parties_plantes"]) > 0) {
			foreach($this->view->lot["prix_parties_plantes"] as $p) {
				foreach($this->view->destinationTransfert as $destination) {
					$possible = false;
					if ($destination["id_destination"] == "laban") {
						Zend_Loader::loadClass("LabanPartieplante");
						$labanPartiePlanteTable = new LabanPartieplante();
						$partiePlantes = $labanPartiePlanteTable->findByIdBraldun($this->view->user->id_braldun);
						$i = 0;
					} else {
						Zend_Loader::loadClass("CharrettePartieplante");
						$table = new CharrettePartieplante();
						$partiePlantes = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
						$i = 1;
					}

					foreach ($partiePlantes as $a) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"]
						&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"]
						&& $a["prix_lot_prix_partieplante"] <= $p["quantite_".$destination["id_destination"]."_partieplante"] ) {
							$acheterOk = true;
							$possible = true;
							break;
						}
					}

					$prix = $p["prix_lot_prix_partieplante"]. " ";
					$s = "";
					if ($p["prix_lot_prix_partieplante"] > 1) {
						$s = "s";
					}
					$nom = htmlspecialchars($p["nom_type_partieplante"]). "$s ";
					$nom .= htmlspecialchars($p["prefix_type_plante"]);
					$nom .= htmlspecialchars($p["nom_type_plante"]);
					$type = "parties_plantes";
					$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "parties_plantes" => $p, "possible" => $possible, "id_destination" => $destination["id_destination"]);
				}

				/*if ($p["place_dispo_force"] == true) {
					$placeDispo = true;
					}*/
			}
		}

		$this->view->acheterOk = $acheterOk;
		$this->view->prix = $tabPrix;
		$this->view->lot["place_dispo"] = $placeDispo;
	}

	private function calculPrixUnitaire($destination, $prix, $nomSysteme) {
		$retour = false;

		if ($destination["id_destination"] == "laban") {
			Zend_Loader::loadClass("Laban");
			$table = new Laban();
			$conteneur = $table->findByIdBraldun($this->view->user->id_braldun);
			$suffixe = "laban";
			$possedeConteneur = true;
			if ($conteneur == null || count($conteneur) < 1) {
				$conteneur["quantite_peau_laban"] = 0;
				$conteneur["quantite_rondin_laban"] = 0;
			} else {
				$conteneur = $conteneur[0];
			}
		} else {
			$conteneur = $this->view->charrette;
			if ($conteneur == null) {
				$possedeConteneur = false;
			} else {
				$possedeConteneur = true;
			}
			$suffixe = "charrette";
		}

		if (($nomSysteme == "peau" || $nomSysteme == "rondin") && $possedeConteneur == true) {
			if ($conteneur["quantite_".$nomSysteme."_".$suffixe] >= $prix) {
				$retour = true;
			}
		} elseif ($nomSysteme == "castar") {
			if ($destination["id_destination"] == "laban") {
				if ($this->view->user->castars_braldun >= $prix) {
					$retour = true;
				}
			} else {
				if ($conteneur["quantite_castar_charrette"] >= $prix) {
					$retour = true;
				}
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

		// on verifie que le Braldûn a assez de ressources.
		if ($this->view->prix[$idPrix]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)."::prix invalide");
		}

		if ($this->view->lot["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		$idDestination = $this->request->get("valeur_3");

		if ($this->view->charrette == null && $this->request->get("valeur_3") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));

		if (intval($this->view->idLot) != intval($this->request->getPost("valeur_1"))) {
			throw new Zend_Exception("Lot invalide : ".$this->view->idLot. " - ".$this->request->getPost("valeur_1"));
		}

		// on regarde si l'on connait la destination
		$flag = false;
		$destination = null;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["id_destination"] == $idDestination) {
				$destination = $d;
				$flag = true;
				break;
			}
		}

		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." destination inconnue=".$idDestination);
		}

		if ($destination["possible"] == false) {// && $destination["possible_force"] == false) {
			throw new Zend_Exception(get_class($this)." destination invalide 3");
		}

		$this->view->detailPrix = "";

		if ($this->view->prix[$idPrix]["type"] == "echoppe") {
			$this->calculAchat($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "minerais") {
			$this->calculAchatMinerais($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "parties_plantes") {
			$this->calculAchatPartiesPlantes($this->view->prix[$idPrix]);
		}

		$this->calculTransfert($idDestination);
		$this->view->destination = $destination;

		if ($this->view->detailPrix != "") {
			$this->view->detailPrix = mb_substr($this->view->detailPrix, 0, -2);
		}

//TODO		$details = "[b".$this->view->user->id_braldun."] a acheté le lot d'équipement n°".$this->view->lot["id_lot"]. " dans l'échoppe";
//		Bral_Util_Lot::insertHistorique(Bral_Util_Lot::HISTORIQUE_ACHETER_ID, $this->view->lot["id_lot"], $details);
	}

	private function calculAchat($prix) {

		if ($prix["id_destination"] == "charrette") {
			$table = new Charrette();
			$suffixe = "charrette";
		} else {
			$table = new Laban();
			$suffixe = "laban";
		}

		$echoppeTable = new Echoppe();

		$nomSysteme = Bral_Util_Registre::getNomUnite($prix["unite"], true);
		if ($nomSysteme  == "peau" ||$nomSysteme == "rondin") {
			$data = array(
				"id_fk_braldun_".$suffixe => $this->view->user->id_braldun,
				"quantite_".$nomSysteme."_".$suffixe => -$prix["prix"],
			);
			$table->insertOrUpdate($data);

			if ($prix["prix"] > 0) {
				$data = array(
					'id_echoppe' => $this->idEchoppe,
					"quantite_".$nomSysteme."_caisse_echoppe" => $prix["prix"],
				);
				$echoppeTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";

		} elseif ($nomSysteme  == "castar") {
			if ($prix["id_destination"] == "charrette") {
				$data = array(
					"id_fk_braldun_".$suffixe => $this->view->user->id_braldun,
					"quantite_".$nomSysteme."_".$suffixe => -$prix["prix"],
				);
				$table->insertOrUpdate($data);
			} else {
				$this->view->user->castars_braldun = $this->view->user->castars_braldun - $prix["prix"];
			}

			if ($prix["prix"] > 0) {
				$data = array(
					'id_echoppe' => $this->idEchoppe,
					'quantite_castar_caisse_echoppe' => $prix["prix"],
				);
				$echoppeTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";
		}
	}

	private function calculAchatMinerais($prix) {

		if ($prix["id_destination"] == "charrette") {
			$table = new CharretteMinerai();
			$suffixe = "charrette";
		} else {
			$table = new LabanMinerai();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_minerai" => $prix["minerais"]["id_fk_type_minerai"],
			"quantite_brut_".$suffixe."_minerai" => - $prix["prix"],
		);

		if ($prix["id_destination"] == "charrette") {
			$data["id_fk_charrette_minerai"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_braldun_laban_minerai"] = $this->view->user->id_braldun;
		}

		$table->insertOrUpdate($data);

		$echoppeMineraiTable = new EchoppeMinerai();
		if ($prix["prix"] > 0) {
			$data = array(
				'id_fk_type_echoppe_minerai' => $prix["minerais"]["id_fk_type_minerai"],
				'id_fk_echoppe_echoppe_minerai' => $this->idEchoppe,
				'quantite_brut_caisse_echoppe_minerai' => $prix["prix"],
			);
			$echoppeMineraiTable->insertOrUpdate($data);
		}

		$this->view->detailPrix .= $prix["prix"]. " ".$prix["nom"].", ";
	}

	private function calculAchatPartiesPlantes($prix) {
		if ($prix["id_destination"] == "charrette") {
			$table = new CharrettePartieplante();
			$suffixe = "charrette";
		} else {
			$table = new LabanPartieplante();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_partieplante" => $prix["parties_plantes"]["id_fk_type_partieplante"],
			"id_fk_type_plante_".$suffixe."_partieplante" => $prix["parties_plantes"]["id_fk_type_plante"],
			"quantite_".$suffixe."_partieplante" => - $prix["prix"],
		);

		if ($prix["id_destination"] == "charrette") {
			$data["id_fk_charrette_partieplante"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_braldun_laban_partieplante"] = $this->view->user->id_braldun;
		}

		$table->insertOrUpdate($data);

		$echoppePartiePlanteTable = new EchoppePartieplante();

		if ($prix["prix"] > 0) {
			$data = array('quantite_caisse_echoppe_partieplante' => $prix["prix"],
						  'id_fk_type_echoppe_partieplante' => $prix["parties_plantes"]["id_fk_type_partieplante"],
						  'id_fk_type_plante_echoppe_partieplante' => $prix["parties_plantes"]["id_fk_type_plante"],
						  'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe,
			);
			$echoppePartiePlanteTable->insertOrUpdate($data);
		}

		$this->view->detailPrix .= $prix["prix"]. " ".$prix["nom"].", ";
	}

	private function calculTransfert($idDestination) {

		Zend_Loader::loadClass("Bral_Util_Lot");
		if ($this->view->charrette != null) {
			Bral_Util_Lot::transfertLot($this->lot["id_lot"], "charrette", $this->view->charrette["id_charrette"]);
		} else {
			Bral_Util_Lot::transfertLot($this->lot["id_lot"], "laban", $this->view->user->id_braldun);
		}

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		}
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}