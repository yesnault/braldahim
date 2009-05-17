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
class Bral_Echoppe_Achetermateriel extends Bral_Echoppe_Echoppe {

	private $materiel = null;

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter un materiel";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeMateriel");
		Zend_Loader::loadClass("EchoppeMaterielMinerai");
		Zend_Loader::loadClass("EchoppeMaterielPartiePlante");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanMateriel");

		$this->idMateriel = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));

		$this->prepareMateriel($this->idMateriel);
		$this->preparePrix();

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = true;
	}

	private function prepareMateriel($idMateriel) {
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($this->idEchoppe);

		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		$trouve = false;
		foreach ($materiels as $p) {
			if ($p["id_echoppe_materiel"] == $idMateriel && $p["type_vente_echoppe_materiel"] == "publique") {
				$trouve = true;
				$this->materiel = $p;
			}
			$idMateriels[] = $p["id_echoppe_materiel"];
		}

		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)."::materiel invalide:".$idMateriel);
		}

		$echoppMaterielMineraiTable = new EchoppeMaterielMinerai();
		$echoppeMaterielMinerai = $echoppMaterielMineraiTable->findByIdsMateriel($idMateriels);

		$minerai = null;
		if (count($echoppeMaterielMinerai) > 0) {
			foreach($echoppeMaterielMinerai as $r) {
				if ($r["id_fk_echoppe_materiel_minerai"] == $this->materiel["id_echoppe_materiel"]) {
					$possible = false;
					if ($r["prix_echoppe_materiel_minerai"] == 0) {
						$possible = true;
					}
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"]
						&& $r["prix_echoppe_materiel_minerai"] <= $m["quantite_brut_laban_minerai"]) {
							$possible = true;
							break;
						}
					}

					$minerai[] = array(
						"prix_echoppe_materiel_minerai" => $r["prix_echoppe_materiel_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"id_fk_type_minerai" => $r["id_fk_type_echoppe_materiel_minerai"],
						"possible" => $possible,
					);
				}
			}
		}

		$echoppeMaterielPartiePlanteTable = new EchoppeMaterielPartiePlante();
		$echoppeMaterielPartiePlante = $echoppeMaterielPartiePlanteTable->findByIdsMateriel($idMateriels);

		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);

		$partiesPlantes = null;
		if (count($echoppeMaterielPartiePlante) > 0) {
			foreach($echoppeMaterielPartiePlante as $a) {
				if ($a["id_fk_echoppe_materiel_partieplante"] == $this->materiel["id_echoppe_materiel"]) {
					$possible = false;
					if ($a["prix_echoppe_materiel_partieplante"] == 0) {
						$possible = true;
					}
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"]
						&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"]
						&& $a["prix_echoppe_materiel_partieplante"] <= $p["quantite_laban_partieplante"] ) {
							$possible = true;
							break;
						}
					}

					$partiesPlantes[] = array(
						"prix_echoppe_materiel_partieplante" => $a["prix_echoppe_materiel_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"id_fk_type_plante" => $a["id_fk_type_plante_echoppe_materiel_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_echoppe_materiel_partieplante"],
						"possible" => $possible,
					);
				}
			}
		}

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;

		$estCharrette = false;

		if (substr($this->materiel["nom_systeme_type_materiel"], 0, 9) == "charrette") {
			$estCharrette = true;
		}

		if ($poidsRestant < $this->materiel["poids_type_materiel"] && $estCharrette == false) {
			$placeDispo = false;
		} else {
			$placeDispo = true;
		}

		$tabMateriel = array(
			"id_materiel" => $this->materiel["id_echoppe_materiel"],
			"nom" => $this->materiel["nom_type_materiel"],
			"id_type_materiel" => $this->materiel["id_fk_type_echoppe_materiel"],
			'nom_systeme_type_materiel' => $this->materiel["nom_systeme_type_materiel"],
			'capacite' => $this->materiel["capacite_type_materiel"], 
			'durabilite' => $this->materiel["durabilite_type_materiel"], 
			'usure' => $this->materiel["usure_type_materiel"], 
			'poids' => $this->materiel["poids_type_materiel"], 
			"prix_1_vente_echoppe_materiel" => $this->materiel["prix_1_vente_echoppe_materiel"],
			"prix_2_vente_echoppe_materiel" => $this->materiel["prix_2_vente_echoppe_materiel"],
			"prix_3_vente_echoppe_materiel" => $this->materiel["prix_3_vente_echoppe_materiel"],
			"unite_1_vente_echoppe_materiel" => $this->materiel["unite_1_vente_echoppe_materiel"],
			"unite_2_vente_echoppe_materiel" => $this->materiel["unite_2_vente_echoppe_materiel"],
			"unite_3_vente_echoppe_materiel" => $this->materiel["unite_3_vente_echoppe_materiel"],
			"commentaire_vente_echoppe_materiel" => $this->materiel["commentaire_vente_echoppe_materiel"],
			"poids" => $this->materiel["poids_type_materiel"],
			"place_dispo" => $placeDispo,
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
			"est_charrette" => $estCharrette,
		);

		$this->view->materiel = $tabMateriel;
	}

	private function preparePrix() {
		$e = $this->view->materiel;
		$tabPrix = null;

		$possible = false;
		$acheterOk = false;

		if ($e["prix_1_vente_echoppe_materiel"] >= 0 && $e["unite_1_vente_echoppe_materiel"] > 0) {
			$prix = $e["prix_1_vente_echoppe_materiel"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_materiel"], false, $e["prix_1_vente_echoppe_materiel"]);
			$type = "echoppe";
			$possible = $this->calculPrixUnitaire($prix, Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_materiel"], true));
			$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_1_vente_echoppe_materiel"]);
		}
			
		if ($e["prix_2_vente_echoppe_materiel"] >= 0 && $e["unite_2_vente_echoppe_materiel"] > 0) {
			$prix = $e["prix_2_vente_echoppe_materiel"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_materiel"], false, $e["prix_2_vente_echoppe_materiel"]);
			$type = "echoppe";
			$possible = $this->calculPrixUnitaire($prix, Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_materiel"], true));
			$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_2_vente_echoppe_materiel"]);
		}
			
		if ($e["prix_3_vente_echoppe_materiel"] >= 0 && $e["unite_3_vente_echoppe_materiel"] > 0) {
			$prix = $e["prix_3_vente_echoppe_materiel"];
			$nom = Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_materiel"], false, $e["prix_3_vente_echoppe_materiel"]);
			$type = "echoppe";
			$possible = $this->calculPrixUnitaire($prix, Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_materiel"], true));
			$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $e["unite_3_vente_echoppe_materiel"]);
		}
			
		if (count($e["prix_minerais"]) > 0) {
			foreach($e["prix_minerais"] as $m) {
				if ($m["possible"] === true) {
					$acheterOk = true;
				}
				$prix = $m["prix_echoppe_materiel_minerai"];
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
				$prix = $p["prix_echoppe_materiel_partieplante"]. " ";
				$s = "";
				if ($p["prix_echoppe_materiel_partieplante"] > 1) {
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

	private function calculPrixUnitaire($prix, $nomSysteme) {
		$retour = false;

		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);

		if (count($laban) != 1) {
			$possedeLaban = false;
		} else {
			$possedeLaban = true;
			$laban = $laban[0];
		}

		if ($nomSysteme == "rondin") {
			$charretteTable = new Charrette();
			$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
			if (count($charrette) == 1) {
				$charrette = $charrette[0];
				if ($charrette["quantite_rondin_charrette"] >= $prix) {
					$retour = true;
				}
			}
		} elseif ($nomSysteme == "peau" && $possedeLaban == true) {
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

		if ($this->view->materiel["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		$this->view->detailPrix = "";

		if ($this->view->prix[$idPrix]["type"] == "echoppe") {
			$this->calculAchatEchoppe($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "minerais") {
			$this->calculAchatMinerais($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "parties_plantes") {
			$this->calculAchatPartiesPlantes($this->view->prix[$idPrix]);
		}

		$this->calculTransfert();

		if ($this->view->detailPrix != "") {
			$this->view->detailPrix = mb_substr($this->view->detailPrix, 0, -2);
		}
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

			if ($prix["prix"] > 0) {
				$data = array(
					'id_echoppe' => $this->idEchoppe,
					'quantite_rondin_caisse_echoppe' => $prix["prix"],
				);
				$echoppeTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";

		} elseif (Bral_Util_Registre::getNomUnite($prix["unite"], true)  == "peau") {
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_peau_laban' => -$prix["prix"],
			);
			$labanTable->insertOrUpdate($data);

			if ($prix["prix"] > 0) {
				$data = array(
					'id_echoppe' => $this->idEchoppe,
					'quantite_peau_caisse_echoppe' => $prix["prix"],
				);
				$echoppeTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";

		} elseif (Bral_Util_Registre::getNomUnite($prix["unite"], true)  == "castar") {
			$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $prix["prix"];

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
		$labanMineraiTable = new LabanMinerai();
		$data = array(
			'id_fk_type_laban_minerai' => $prix["minerais"]["id_fk_type_minerai"],
			'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
			'quantite_brut_laban_minerai' => - $prix["prix"],
		);
		$labanMineraiTable->insertOrUpdate($data);

		$echoppeMineraiTable = new EchoppeMinerai();
		if ($prix["prix"] > 0) {
			$data = array(
				'id_fk_type_echoppe_minerai' => $prix["minerais"]["id_fk_type_minerai"],
				'id_fk_echoppe_echoppe_minerai' => $this->idEchoppe,
				'quantite_caisse_echoppe_minerai' => $prix["prix"],
			);
			$echoppeMineraiTable->insertOrUpdate($data);
		}

		$this->view->detailPrix .= $prix["prix"]. " ".$prix["nom"].", ";
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

	private function calculTransfert() {

		if ($this->view->materiel["est_charrette"] == true) {
			$dataUpdate = array(
			"id_fk_hobbit_charrette" => null,
			"x_charrette" => $this->view->user->x_hobbit,
			"y_charrette" => $this->view->user->y_hobbit,
			"id_charrette" => $this->view->materiel["id_materiel"],
			"id_fk_type_materiel_charrette" => $this->view->materiel["id_type_materiel"],
			);
			$where = "id_charrette = ".$this->view->materiel["id_materiel"];
			$charretteTable = new Charrette();
			$charretteTable->insert($dataUpdate, $where);
		} else {
			$labanMaterielTable = new LabanMateriel();
			$data = array(
				'id_laban_materiel' => $this->view->materiel["id_materiel"],
				'id_fk_type_laban_materiel' => $this->view->materiel["id_type_materiel"],
				'id_fk_hobbit_laban_materiel' => $this->view->user->id_hobbit,
			);
			$labanMaterielTable->insert($data);
		}

		$echoppeMaterielTable = new EchoppeMateriel();
		$where = "id_echoppe_materiel=".$this->view->materiel["id_materiel"];
		$echoppeMaterielTable->delete($where);
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_echoppe", "box_echoppes", "box_laban", "box_charrette", "box_evenements");
	}
}