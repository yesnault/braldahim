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
class Bral_Echoppe_Acheteraliment extends Bral_Echoppe_Echoppe {

	private $aliment = null;

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter un aliment";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeAliment");
		Zend_Loader::loadClass("EchoppeAlimentMinerai");
		Zend_Loader::loadClass("EchoppeAlimentPartiePlante");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("CharretteMinerai");
		Zend_Loader::loadClass("CharrettePartieplante");

		$this->idAliment = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));

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

		$this->prepareAliment($this->idAliment);
		$this->preparePrix();

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = true;
	}

	private function prepareAliment($idAliment) {
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($this->idEchoppe);

		$trouve = false;
		foreach ($aliments as $p) {
			if ($p["id_echoppe_aliment"] == $idAliment && $p["type_vente_echoppe_aliment"] == "publique") {
				$trouve = true;
				$this->aliment = $p;
			}
			$idAliments[] = $p["id_echoppe_aliment"];
		}

		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::aliment invalide:".$idAliment);
		}

		$echoppeAlimentMineraiTable = new EchoppeAlimentMinerai();
		$echoppeAlimentMinerai = $echoppeAlimentMineraiTable->findByIdsAliment($idAliments);

		$minerai = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareAlimentMinerai($d, $echoppeAlimentMinerai, $minerai);
		}

		$echoppeAlimentPartiePlanteTable = new EchoppeAlimentPartiePlante();
		$echoppeAlimentPartiePlante = $echoppeAlimentPartiePlanteTable->findByIdsAliment($idAliments);

		$partiesPlantes = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareAlimentPartiePlante($d, $echoppeAlimentPartiePlante, $partiesPlantes);
		}

		$estCharrette = false;

		$tabCharrette["possible"] = true;
		$tabCharrette["detail"] = "";

		if (substr($this->aliment["nom_systeme_type_aliment"], 0, 9) == "charrette") {
			$estCharrette = true;

			Zend_Loader::loadClass("Bral_Util_Metier");
			$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_braldun, $this->view->user->sexe_braldun);
			$estMenuisierOuBucheron = false;
			if ($tab["tabMetierCourant"]["nom_systeme"] == "bucheron" || $tab["tabMetierCourant"]["nom_systeme"] == "menuisier") {
				$estMenuisierOuBucheron = true;
			}
			Zend_Loader::loadClass("Bral_Util_Charrette");
			$tab = Bral_Util_Charrette::calculAttraperPossible($this->aliment, $this->view->user, $estMenuisierOuBucheron);

			$charretteTable = new Charrette();
			$nombre = $charretteTable->countByIdBraldun($this->view->user->id_braldun);

			if ($nombre > 0) {
				$tabCharrette["possible"] = false;
				$tabCharrette["detail"] = "Vous possédez déjà une charrette";
			}

		}

		Zend_Loader::loadClass("Bral_Util_Aliment");
		$tabAliment = array(
			"id_aliment" => $this->aliment["id_echoppe_aliment"],
			'id_type_aliment' => $this->aliment["id_type_aliment"],
			'nom_systeme_type_aliment' => $this->aliment["nom_systeme_type_aliment"],
			'nom' =>$this->aliment["nom_type_aliment"],
			'poids' => $this->aliment["poids_unitaire_type_aliment"],
			"qualite" => $this->aliment["nom_aliment_type_qualite"],
			"bbdf" => $this->aliment["bbdf_aliment"],
			"recette" => Bral_Util_Aliment::getNomType($this->aliment["type_bbdf_type_aliment"]),
			"prix_1_vente_echoppe_aliment" => $this->aliment["prix_1_vente_echoppe_aliment"],
			"prix_2_vente_echoppe_aliment" => $this->aliment["prix_2_vente_echoppe_aliment"],
			"prix_3_vente_echoppe_aliment" => $this->aliment["prix_3_vente_echoppe_aliment"],
			"unite_1_vente_echoppe_aliment" => $this->aliment["unite_1_vente_echoppe_aliment"],
			"unite_2_vente_echoppe_aliment" => $this->aliment["unite_2_vente_echoppe_aliment"],
			"unite_3_vente_echoppe_aliment" => $this->aliment["unite_3_vente_echoppe_aliment"],
			"commentaire_vente_echoppe_aliment" => $this->aliment["commentaire_vente_echoppe_aliment"],
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
			"est_charrette" => $estCharrette,
			"charrette_possible" => $tabCharrette["possible"],
			"charrette_detail" => $tabCharrette["detail"],
		);

		$this->view->aliment = $tabAliment;
	}

	private function prepareAlimentMinerai($destination, $echoppeAlimentMinerai, &$minerai) {

		if ($destination["id_destination"] == "laban") {
			$table = new LabanMinerai();
			$minerais = $table->findByIdBraldun($this->view->user->id_braldun);
			$i = 0;
		} else {
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$i = 1;
		}

		if (count($echoppeAlimentMinerai) > 0) {
			foreach($echoppeAlimentMinerai as $r) {
				if ($r["id_fk_echoppe_aliment_minerai"] == $this->aliment["id_echoppe_aliment"]) {
					$possible = false;
					if ($r["prix_echoppe_aliment_minerai"] == 0) {
						$possible = true;
					}
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"]
						&& $r["prix_echoppe_aliment_minerai"] <= $m["quantite_brut_".$destination["id_destination"]."_minerai"]) {
							$possible = true;
							break;
						}
					}

					$placeDispoForce = false;
					if ($destination["possible"] == false && $destination["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] - $r["prix_echoppe_aliment_minerai"] * Bral_Util_Poids::POIDS_MINERAI) {
						$placeDispoForce = true;
						$this->view->destinationTransfert[$i]["possible_force"] = true;
					}

					$minerai[] = array(
						"prix_echoppe_aliment_minerai" => $r["prix_echoppe_aliment_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"id_fk_type_minerai" => $r["id_fk_type_echoppe_aliment_minerai"],
						"possible" => $possible,
						"id_destination" => $destination["id_destination"],
						"place_dispo_force" => $placeDispoForce,
					);
				}
			}
		}
	}

	private function prepareAlimentPartiePlante($destination, $echoppeAlimentPartiePlante, &$partiesPlantes) {

		if ($destination["id_destination"] == "laban") {
			$labanPartiePlanteTable = new LabanPartieplante();
			$partiePlantes = $labanPartiePlanteTable->findByIdBraldun($this->view->user->id_braldun);
			$i = 0;
		} else {
			$table = new CharrettePartieplante();
			$partiePlantes = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$i = 1;
		}

		if (count($echoppeAlimentPartiePlante) > 0) {
			foreach($echoppeAlimentPartiePlante as $a) {
				if ($a["id_fk_echoppe_aliment_partieplante"] == $this->aliment["id_echoppe_aliment"]) {
					$possible = false;
					if ($a["prix_echoppe_aliment_partieplante"] == 0) {
						$possible = true;
					}
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"]
						&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"]
						&& $a["prix_echoppe_aliment_partieplante"] <= $p["quantite_".$destination["id_destination"]."_partieplante"] ) {
							$possible = true;
							break;
						}
					}

					$placeDispoForce = false;
					if ($destination["possible"] == false && $destination["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] - $a["prix_echoppe_aliment_partieplante"] * Bral_Util_Poids::POIDS_MINERAI) {
						$placeDispoForce = true;
						$this->view->destinationTransfert[$i]["possible_force"] = true;
					}

					$partiesPlantes[] = array(
						"prix_echoppe_aliment_partieplante" => $a["prix_echoppe_aliment_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"id_fk_type_plante" => $a["id_fk_type_plante_echoppe_aliment_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_echoppe_aliment_partieplante"],
						"possible" => $possible,
						"id_destination" => $destination["id_destination"],
						"place_dispo_force" => $placeDispoForce,
					);
				}
			}
		}
	}

	private function preparePrix() {
		$e = $this->view->aliment;
		$tabPrix = null;

		$possible = false;
		$acheterOk = false;

		$placeDispo = false;

		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] || (substr($this->aliment["nom_systeme_type_aliment"], 0, 9) == "charrette")) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i++;
		}

		if ($e["prix_1_vente_echoppe_aliment"] >= 0 && $e["unite_1_vente_echoppe_aliment"] > 0) {
			$prix = $e["prix_1_vente_echoppe_aliment"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_aliment"], false, $e["prix_1_vente_echoppe_aliment"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_aliment"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_1_vente_echoppe_aliment"], "id_destination" => $d["id_destination"]);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] - $poidsPrix) {
					$placeDispo = true;
					$this->view->destinationTransfert[$i]["possible_force"] = true;
				}
				$i++;
			}
		}
			
		if ($e["prix_2_vente_echoppe_aliment"] >= 0 && $e["unite_2_vente_echoppe_aliment"] > 0) {
			$prix = $e["prix_2_vente_echoppe_aliment"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_aliment"], false, $e["prix_2_vente_echoppe_aliment"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_aliment"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_2_vente_echoppe_aliment"], "id_destination" => $d["id_destination"]);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] - $poidsPrix) {
					$placeDispo = true;
					$this->view->destinationTransfert[$i]["possible_force"] = true;
				}
				$i++;
			}
		}
			
		if ($e["prix_3_vente_echoppe_aliment"] >= 0 && $e["unite_3_vente_echoppe_aliment"] > 0) {
			$prix = $e["prix_3_vente_echoppe_aliment"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_aliment"], false, $e["prix_3_vente_echoppe_aliment"]);
			$nomSystemeUnite = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_aliment"], true);
			$poidsPrix = $prix * Bral_Util_Poids::getPoidsUnite($nomSystemeUnite);
			$type = "echoppe";
			$i = 0;
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, $nomSystemeUnite);
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_3_vente_echoppe_aliment"], "id_destination" => $d["id_destination"]);
				if ($this->view->destinationTransfert[$i]["possible"] == false && $d["poids_restant"] >= $this->aliment["poids_unitaire_type_aliment"] - $poidsPrix) {
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
				$prix = $m["prix_echoppe_aliment_minerai"];
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
				$prix = $p["prix_echoppe_aliment_partieplante"]. " ";
				$s = "";
				if ($p["prix_echoppe_aliment_partieplante"] > 1) {
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
		$this->view->aliment["place_dispo"] = $placeDispo;
	}

	private function calculPrixUnitaire($destination, $prix, $nomSysteme) {
		$retour = false;

		if ($destination["id_destination"] == "laban") {
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

		// on verifie que le braldun a assez de ressources.
		if ($this->view->prix[$idPrix]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)."::prix invalide");
		}

		if ($this->view->aliment["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		$idDestination = $this->request->get("valeur_3");

		if ($this->view->charrette == null && $this->request->get("valeur_3") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));

		if (intval($this->idAliment) != intval($this->request->getPost("valeur_1"))) {
			throw new Zend_Exception("Aliment invalide : ".$this->idAliment. " - ".$this->request->getPost("valeur_1"));
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

//		$details = "[b".$this->view->user->id_braldun."] a acheté le matériel n°".$this->idAliment. " dans l'échoppe";
//		Zend_Loader::loadClass("Bral_Util_Aliment");
//		Bral_Util_Aliment::insertHistorique(Bral_Util_Aliment::HISTORIQUE_ACHETER_ID, $this->idAliment, $details);
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

		if ($this->view->aliment["est_charrette"] == true) {
			$dataUpdate = array(
				"id_fk_braldun_charrette" => $this->view->user->id_braldun,
				"x_charrette" => null,
				"y_charrette" => null,
				"id_charrette" => $this->view->aliment["id_aliment"],
				"durabilite_max_charrette" => $this->view->aliment["durabilite"],
				"durabilite_actuelle_charrette" => $this->view->aliment["durabilite"],
				"poids_transportable_charrette" => $this->view->aliment["capacite"],
				"poids_transporte_charrette" => 0,
			);
			$where = "id_charrette = ".$this->view->aliment["id_aliment"];
			$charretteTable = new Charrette();
			$charretteTable->insert($dataUpdate);
		} else {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteAliment");
				$table = new CharretteAliment();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanAliment");
				$table = new LabanAliment();
				$suffixe = "laban";
			}

			$data = array(
				"id_".$suffixe."_aliment" => $this->view->aliment["id_aliment"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_aliment"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_braldun_laban_aliment"] = $this->view->user->id_braldun;
			}

			$table->insert($data);
		}

		$echoppeAlimentTable = new EchoppeAliment();
		$where = "id_echoppe_aliment=".$this->view->aliment["id_aliment"];
		$echoppeAlimentTable->delete($where);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		}
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}