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
class Bral_Echoppe_Acheterequipement extends Bral_Echoppe_Echoppe {

	private $equipement = null;

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter une equipement";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("CharretteMinerai");
		Zend_Loader::loadClass("CharrettePartieplante");

		$this->idEquipement = Bral_Util_Controle::getValeurIntVerif($this->request->get("idEquipement"));

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "poids_restant" => $poidsRestant, "possible" => false, "possible_force" => false);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "poids_restant" => $poidsRestant, "possible" => false, "possible_force" => false);
		}
		$this->view->destinationTransfert = $tabDestinationTransfert;

		$this->view->charrette = $charrette;

		$this->prepareEquipement($this->idEquipement);
		$this->preparePrix();
	}

	private function prepareEquipement($idEquipement) {
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($this->idEchoppe);

		$trouve = false;
		foreach ($equipements as $p) {
			if ($p["id_echoppe_equipement"] == $idEquipement && $p["type_vente_echoppe_equipement"] == "publique") {
				$trouve = true;
				$this->equipement = $p;
			}
			$idEquipements[] = $p["id_echoppe_equipement"];
		}

		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::equipement invalide:".$idEquipement);
		}

		$runes = $this->prepareEquipementRune($idEquipements);
		$bonus = $this->prepareEquipementBonus($idEquipements);

		$echoppEquipementMineraiTable = new EchoppeEquipementMinerai();
		$echoppeEquipementMinerai = $echoppEquipementMineraiTable->findByIdsEquipement($idEquipements);

		$minerai = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareEquipementMinerai($d, $echoppeEquipementMinerai, $minerai);
		}

		$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
		$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);

		$partiesPlantes = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareEquipementPartiePlante($d, $echoppeEquipementPartiePlante, $partiesPlantes);
		}

		$tabEquipement = array(
			"id_equipement" => $this->equipement["id_echoppe_equipement"],
			"nom" => Bral_Util_Equipement::getNomByIdRegion($this->equipement, $this->equipement["id_fk_region_equipement"]),
			"nom_standard" => $this->equipement["nom_type_equipement"],
			"qualite" => $this->equipement["nom_type_qualite"],
			"niveau" => $this->equipement["niveau_recette_equipement"],
			"id_type_emplacement" => $this->equipement["id_type_emplacement"],
			"id_type_equipement" => $this->equipement["id_type_equipement"],
			"emplacement" => $this->equipement["nom_type_emplacement"],
			"nom_systeme_type_emplacement" => $this->equipement["nom_systeme_type_emplacement"],
			"nb_runes" => $this->equipement["nb_runes_equipement"],
			"id_fk_recette_equipement" => $this->equipement["id_fk_recette_equipement"],
			"armure" => $this->equipement["armure_recette_equipement"],
			"force" => $this->equipement["force_recette_equipement"],
			"agilite" => $this->equipement["agilite_recette_equipement"],
			"vigueur" => $this->equipement["vigueur_recette_equipement"],
			"sagesse" => $this->equipement["sagesse_recette_equipement"],
			"vue" => $this->equipement["vue_recette_equipement"],
			"bm_attaque" => $this->equipement["bm_attaque_recette_equipement"],
			"bm_degat" => $this->equipement["bm_degat_recette_equipement"],
			"bm_defense" => $this->equipement["bm_defense_recette_equipement"],
			"suffixe" => $this->equipement["suffixe_mot_runique"],
			"id_fk_mot_runique" => $this->equipement["id_fk_mot_runique_equipement"],
			"id_fk_region" => $this->equipement["id_fk_region_equipement"],
			"nom_systeme_mot_runique" => $this->equipement["nom_systeme_mot_runique"],
			"etat_courant" => $this->equipement["etat_courant_equipement"],
			"etat_initial" => $this->equipement["etat_initial_equipement"],
			"prix_1_vente_echoppe_equipement" => $this->equipement["prix_1_vente_echoppe_equipement"],
			"prix_2_vente_echoppe_equipement" => $this->equipement["prix_2_vente_echoppe_equipement"],
			"prix_3_vente_echoppe_equipement" => $this->equipement["prix_3_vente_echoppe_equipement"],
			"unite_1_vente_echoppe_equipement" => $this->equipement["unite_1_vente_echoppe_equipement"],
			"unite_2_vente_echoppe_equipement" => $this->equipement["unite_2_vente_echoppe_equipement"],
			"unite_3_vente_echoppe_equipement" => $this->equipement["unite_3_vente_echoppe_equipement"],
			"commentaire_vente_echoppe_equipement" => $this->equipement["commentaire_vente_echoppe_equipement"],
			"poids" => $this->equipement["poids_recette_equipement"],
			"runes" => $runes,
			"bonus" => $bonus,
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
		);

		$this->view->equipement = $tabEquipement;
		
		$equipements = null;
		$equipements[$tabEquipement["id_type_emplacement"]]["equipements"][] = $tabEquipement;
		$equipements[$tabEquipement["id_type_emplacement"]]["nom_type_emplacement"] = $tabEquipement["emplacement"];
		$this->view->equipements = $equipements;
	}

	private function prepareEquipementRune($idEquipements) {
		$equipementRuneTable = new EquipementRune();
		$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

		$runes = null;
		if (count($equipementRunes) > 0) {
			foreach($equipementRunes as $r) {
				if ($r["id_equipement_rune"] == $this->equipement["id_echoppe_equipement"]) {
					$runes[] = array(
						"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
						"id_fk_type_rune_equipement_rune" => $r["id_fk_type_rune_equipement_rune"],
						"nom_type_rune" => $r["nom_type_rune"],
						"image_type_rune" => $r["image_type_rune"],
						"effet_type_rune" => $r["effet_type_rune"],
					);
				}
			}
		}
		return $runes;
	}

	private function prepareEquipementBonus($idEquipements) {
		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);

		$bonus = null;
		if (count($equipementBonus) > 0) {
			foreach($equipementBonus as $b) {
				if ($b["id_equipement_bonus"] == $this->equipement["id_echoppe_equipement"]) {
					$bonus = $b;
					break;
				}
			}
		}
		return $bonus;
	}

	private function prepareEquipementMinerai($destination, $echoppeEquipementMinerai, &$minerai) {
		if ($destination["id_destination"] == "laban") {
			$table = new LabanMinerai();
			$minerais = $table->findByIdHobbit($this->view->user->id_hobbit);
			$i = 0;
		} else {
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$i = 1;
		}

		if (count($echoppeEquipementMinerai) > 0) {
			foreach($echoppeEquipementMinerai as $r) {
				if ($r["id_fk_echoppe_equipement_minerai"] == $this->equipement["id_echoppe_equipement"]) {
					$possible = false;
					if ($r["prix_echoppe_equipement_minerai"] == 0) {
						$possible = true;
					}

					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"]
						&& $r["prix_echoppe_equipement_minerai"] <= $m["quantite_brut_".$destination["id_destination"]."_minerai"]) {
							$possible = true;
							break;
						}
					}

					$placeDispoForce = false;
					if ($destination["possible"] == false && $destination["poids_restant"] >= $this->equipement["poids_recette_equipement"] - $r["prix_echoppe_equipement_minerai"] * Bral_Util_Poids::POIDS_MINERAI) {
						$placeDispoForce = true;
						$this->view->destinationTransfert[$i]["possible_force"] = true;
					}

					$minerai[] = array(
							"prix_echoppe_equipement_minerai" => $r["prix_echoppe_equipement_minerai"],
							"nom_type_minerai" => $r["nom_type_minerai"],
							"id_fk_type_minerai" => $r["id_fk_type_echoppe_equipement_minerai"],
							"possible" => $possible,
							"id_destination" => $destination["id_destination"],
							"place_dispo_force" => $placeDispoForce,
					);
				}
			}
		}
	}

	private function prepareEquipementPartiePlante($destination, $echoppeEquipementPartiePlante, &$partiesPlantes) {
		if ($destination["id_destination"] == "laban") {
			$labanPartiePlanteTable = new LabanPartieplante();
			$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
			$i = 0;
		} else {
			$table = new CharrettePartieplante();
			$partiePlantes = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$i = 1;
		}

		if (count($echoppeEquipementPartiePlante) > 0) {
			foreach($echoppeEquipementPartiePlante as $a) {
				if ($a["id_fk_echoppe_equipement_partieplante"] == $this->equipement["id_echoppe_equipement"]) {
					$possible = false;
					if ($a["prix_echoppe_equipement_partieplante"] == 0) {
						$possible = true;
					}
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"]
						&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"]
						&& $a["prix_echoppe_equipement_partieplante"] <= $p["quantite_".$destination["id_destination"]."_partieplante"] ) {
							$possible = true;
							break;
						}
					}

					$placeDispoForce = false;
					if ($destination["possible"] == false && $destination["poids_restant"] >= $this->equipement["poids_recette_equipement"] - $r["prix_echoppe_equipement_minerai"] * Bral_Util_Poids::POIDS_MINERAI) {
						$placeDispoForce = true;
						$this->view->destinationTransfert[$i]["possible_force"] = true;
					}

					$partiesPlantes[] = array(
						"prix_echoppe_equipement_partieplante" => $a["prix_echoppe_equipement_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"id_fk_type_plante" => $a["id_fk_type_plante_echoppe_equipement_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_echoppe_equipement_partieplante"],
						"possible" => $possible,
						"id_destination" => $destination["id_destination"],
						"place_dispo_force" => $placeDispoForce,
					);
				}
			}
		}
	}

	private function preparePrix() {
		$e = $this->view->equipement;
		$tabPrix = null;

		$possible = false;
		$acheterOk = false;

		$placeDispo = false;

		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $this->equipement["poids_recette_equipement"]) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i++;
		}

		if ($e["prix_1_vente_echoppe_equipement"] >= 0 && $e["unite_1_vente_echoppe_equipement"] > 0) {
			$prix = $e["prix_1_vente_echoppe_equipement"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_equipement"], false, $e["prix_1_vente_echoppe_equipement"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_equipement"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->equipement["poids_recette_equipement"] - $poidsPrix) {
					$placeDispo = true;
					$this->view->destinationTransfert[$i]["possible_force"] = true;
				}
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_1_vente_echoppe_equipement"], "id_destination" => $d["id_destination"]);
				$i++;
			}
		}

		if ($e["prix_2_vente_echoppe_equipement"] >= 0 && $e["unite_2_vente_echoppe_equipement"] > 0) {
			$prix = $e["prix_2_vente_echoppe_equipement"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_equipement"], false, $e["prix_2_vente_echoppe_equipement"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_equipement"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_2_vente_echoppe_equipement"], "id_destination" => $d["id_destination"]);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->equipement["poids_recette_equipement"] - $poidsPrix) {
					$placeDispo = true;
					$this->view->destinationTransfert[$i]["possible_force"] = true;
				}
				$i++;
			}
		}

		if ($e["prix_3_vente_echoppe_equipement"] >= 0 && $e["unite_3_vente_echoppe_equipement"] > 0) {
			$prix = $e["prix_3_vente_echoppe_equipement"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_equipement"], false, $e["prix_3_vente_echoppe_equipement"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_equipement"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_3_vente_echoppe_equipement"], "id_destination" => $d["id_destination"]);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->equipement["poids_recette_equipement"] - $poidsPrix) {
					$placeDispo = true;
					$this->view->destinationTransfert[$i]["possible_force"] = true;
				}
				$i++;
			}
		}

		if (count($e["prix_minerais"]) > 0) {
			foreach($e["prix_minerais"] as $m) {
				if ($m["possible"] === true) {
					$acheterOk = true;
				}
				$prix = $m["prix_echoppe_equipement_minerai"];
				$nom = htmlspecialchars($m["nom_type_minerai"]);
				$type = "minerais";
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "minerais" => $m, "possible" => $m["possible"], "id_destination" => $m["id_destination"]);
				if ($m["place_dispo_force"] == true) {
					$placeDispo = true;
				}
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
				$nom = htmlspecialchars($p["nom_type_partieplante"]). "$s ";
				$nom .= htmlspecialchars($p["prefix_type_plante"]);
				$nom .= htmlspecialchars($p["nom_type_plante"]);
				$type = "parties_plantes";
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "parties_plantes" => $p, "possible" => $p["possible"], "id_destination" => $p["id_destination"]);
				if ($p["place_dispo_force"] == true) {
					$placeDispo = true;
				}
			}
		}

		$this->view->acheterOk = $acheterOk;
		$this->view->prix = $tabPrix;
		$this->view->equipement["place_dispo"] = $placeDispo;
	}

	private function calculPrixUnitaire($destination, $prix, $nomSysteme) {
		$retour = false;

		if ($destination["id_destination"] == "laban") {
			$table = new Laban();
			$conteneur = $table->findByIdHobbit($this->view->user->id_hobbit);
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
				if ($this->view->user->castars_hobbit >= $prix) {
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

		// on verifie que le hobbit a assez de ressources.
		if ($this->view->prix[$idPrix]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)."::prix invalide");
		}

		if ($this->view->equipement["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		$idDestination = $this->request->get("valeur_3");

		if ($this->view->charrette == null && $this->request->get("valeur_3") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));

		if (intval($this->idEquipement) != intval($this->request->getPost("valeur_1"))) {
			throw new Zend_Exception("Equipement invalide : ".$this->idEquipement. " - ".$this->request->getPost("valeur_1"));
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

		if ($destination["possible"] == false && $destination["possible_force"] == false) {
			throw new Zend_Exception(get_class($this)." destination invalide 3");
		}

		$this->view->detailPrix = "";

		if ($this->view->prix[$idPrix]["type"] == "echoppe") {
			$this->calculAchatEchoppe($this->view->prix[$idPrix]);
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
	}

	private function calculAchatEchoppe($prix) {

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
				"id_fk_hobbit_".$suffixe => $this->view->user->id_hobbit,
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
					"id_fk_hobbit_".$suffixe => $this->view->user->id_hobbit,
					"quantite_".$nomSysteme."_".$suffixe => -$prix["prix"],
				);
				$table->insertOrUpdate($data);
			} else {
				$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $prix["prix"];
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
			$data["id_fk_hobbit_laban_minerai"] = $this->view->user->id_hobbit;
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
			$data["id_fk_hobbit_laban_partieplante"] = $this->view->user->id_hobbit;
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

		if ($this->equipement["nom_systeme_type_emplacement"] == 'laban') {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteMunition");
				$table = new CharretteMunition();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanMunition");
				$table = new LabanMunition();
				$suffixe = "laban";
			}

			$data = array(
				"id_fk_type_".$suffixe."_munition" => $this->equipement["id_fk_type_munition_type_equipement"],
				"quantite_".$suffixe."_munition" => $this->equipement["nb_munition_type_equipement"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_munition"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_hobbit_laban_munition"] = $this->view->user->id_hobbit;
			}
			$table->insertOrUpdate($data);
		} else {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteEquipement");
				$table = new CharretteEquipement();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanEquipement");
				$table = new LabanEquipement();
				$suffixe = "laban";
			}

			$data = array(
				"id_".$suffixe."_equipement" => $this->equipement["id_echoppe_equipement"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_equipement"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_hobbit_laban_equipement"] = $this->view->user->id_hobbit;
			}
			$table->insert($data);
		}

		$echoppeEquipementTable = new EchoppeEquipement();
		$where = "id_echoppe_equipement=".$this->equipement["id_echoppe_equipement"];
		$echoppeEquipementTable->delete($where);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_equipement", "box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}