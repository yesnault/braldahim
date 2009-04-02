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
class Bral_Lieux_Quete extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Quete");
		Zend_Loader::loadClass("Bral_Util_Quete");
		$queteTable = new Quete();

		$quete = $queteTable->findByIdHobbitAndIdLieu($this->view->user->id_hobbit, $this->view->idLieu);
		if ($quete != null || count($quete) > 0) {
			$this->view->queteObtenue = true;
		} else {
			$this->view->queteObtenue = false;
		}

		if ($this->view->user->est_quete_hobbit == "non") {
			$this->view->queteEnCours = false;
		} else {
			$this->view->queteEnCours = true;
		}

		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_hobbit -  $this->_coutCastars) >= 0);
	}

	function prepareFormulaire() {
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {

		// verification qu'il y a assez de castars
		if ($this->view->queteObtenue === true) {
			throw new Zend_Exception(get_class($this)." Quete impossible : id:".$this->view->user->id_hobbit." l:".$this->view->idLieu);
		}

		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}

		if ($this->view->queteEnCours == true) {
			throw new Zend_Exception(get_class($this)." Quete en cours id:".$this->view->user->id_hobbit);
		}

		$this->calculQuete();
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;
		$this->view->user->est_quete_hobbit = "oui";
		$this->majHobbit();

		$this->view->coutCastars = $this->_coutCastars;
	}

	private function calculQuete() {
		$queteTable = new Quete();

		$data = array(
			"id_fk_lieu_quete" => $this->view->idLieu,
			"id_fk_hobbit_quete" => $this->view->user->id_hobbit,
			"date_creation_quete" => date("Y-m-d H:i:s"),		
		);
		$idQuete = $queteTable->insert($data);

		$this->calculEtapes($idQuete);
	}

	private function calculEtapes($idQuete) {
		Zend_Loader::loadClass("Etape");
		Zend_Loader::loadClass("TypeEtape");
		Zend_Loader::loadClass("HobbitsMetiers");

		$typeEtapes = $this->getTypesEtapesPossibles();

		$etapeTable = new Etape();
		$nbEtapesAFaire = Bral_Util_De::get_2d3();

		for ($i = 1; $i<= $nbEtapesAFaire; $i++) {
			$n = Bral_Util_De::get_de_specifique(0, count($typeEtapes) - 1);
			//			$dataEtape = $this->prepareEtape($i, $idQuete, $typeEtapes[$n]);

			$dataEtape = $this->prepareEtape($i, $idQuete, $typeEtapes[3]);
			$etapes[] = $dataEtape;
			$etapeTable->insert($dataEtape);
		}

		$this->view->nbEtapesAFaire = $nbEtapesAFaire;
		$this->view->etapes = $etapes;
	}

	private function getTypesEtapesPossibles() {
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);

		$idMetiers = array();
		foreach($hobbitsMetierRowset as $m) {
			$idMetiers[] = $m["id_metier"];
		}

		$typeEtapeTable = new TypeEtape();
		$typeEtapes = $typeEtapeTable->fetchAllSansMetier();
		$typeEtapesMetier = $typeEtapeTable->fetchAllAvecIdsMetier($idMetiers);

		if ($typeEtapesMetier != null) {
			foreach($typeEtapesMetier as $e) {
				array_push($typeEtapes, $e);
			}
		}
		return $typeEtapes;
	}

	private function prepareEtape($ordre, $idQuete, $typeEtape) {

		$dataTypeEtape = $this->pepareParamTypeEtape($typeEtape);

		if ($ordre == 1) {
			$dateDebutEtape = date("Y-m-d H:i:s");
		} else {
			$dateDebutEtape = null;
		}

		$data = array(
			"id_fk_quete_etape" => $idQuete,
			"id_fk_type_etape" => $typeEtape["id_type_etape"],
			"id_fk_hobbit_etape" => $this->view->user->id_hobbit, // denormalisation
			"libelle_etape" => $dataTypeEtape["libelle_etape"],
			"date_debut_etape" => $dateDebutEtape,
			"param_1_etape" => $dataTypeEtape["param1"],
			"param_2_etape" => $dataTypeEtape["param2"],
			"param_3_etape" => $dataTypeEtape["param3"],
			"param_4_etape" => $dataTypeEtape["param4"],
			"param_5_etape" => $dataTypeEtape["param5"],
			"ordre_etape" => $ordre,
		);

		return $data;
	}

	private function pepareParamTypeEtape($typeEtape) {
		switch($typeEtape["nom_systeme_type_etape"]) {
			case "tuer":
				return $this->pepareParamTypeEtapeTuer();
				break;
			case "manger":
				return $this->pepareParamTypeEtapeManger();
				break;
			case "fumer":
				return $this->pepareParamTypeEtapeFumer();
				break;
			case "marcher":
				return $this->pepareParamTypeEtapeMarcher();
				break;
			case "posseder":
				return $this->pepareParamTypeEtapePosseder();
				break;
			case "equiper":
				return $this->pepareParamTypeEtapeEquiper();
				break;
			case "construire":
				return $this->pepareParamTypeEtapeConstruire();
				break;
			case "fabriquer":
				return $this->pepareParamTypeEtapeFabriquer();
				break;
			case "collecter":
				return $this->pepareParamTypeEtapeCollecter();
				break;
			default:
				throw new Zend_Exception(get_class($this)." nom_systeme_type_etape invalide:".$typeEtape["nom_systeme_type_etape"]);
				break;
		}
	}

	private function initDataTypeEtape() {
		$dataTypeEtape = array (
			"param1" => null,
			"param2" => null,
			"param3" => null,
			"param4" => null,
			"param5" => null,
			"libelle_etape" => "",
			"libelle_etape_fin" => "",
		);
		return $dataTypeEtape;
	}
	private function pepareParamTypeEtapeTuer() {
		$dataTypeEtape = $this->initDataTypeEtape();

		$this->pepareParamTypeEtapeTuerParam1et2($dataTypeEtape);
		$this->pepareParamTypeEtapeTuerParam3et4($dataTypeEtape);

		$dataTypeEtape["libelle_etape"] = $dataTypeEtape["libelle_etape"].$dataTypeEtape["libelle_etape_fin"].".";
		return $dataTypeEtape;
	}

	private function pepareParamTypeEtapeTuerParam1et2(&$dataTypeEtape) {
		$dataTypeEtape["param1"] = Bral_Util_De::get_1d3();

		if (Bral_Util_Quete::ETAPE_TUER_PARAM1_NOMBRE == $dataTypeEtape["param1"]) {
			$dataTypeEtape["param2"] = Bral_Util_De::get_1d10() + 2;
			$dataTypeEtape["libelle_etape"] = "Vous devez tuer ".$dataTypeEtape["param2"]. " monstres";
		} else if (Bral_Util_Quete::ETAPE_TUER_PARAM1_JOUR == $dataTypeEtape["param1"]) {
			$dataTypeEtape["param2"] = Bral_Util_De::get_1D7();
			$dataTypeEtape["libelle_etape"] = "Vous devez tuer 1 monstre";
			$dataTypeEtape["libelle_etape_fin"] = ", un ".Bral_Helper_Calendrier::getJourSemaine($dataTypeEtape["param2"]);
		} else if (Bral_Util_Quete::ETAPE_TUER_PARAM1_ETAT == $dataTypeEtape["param1"]) {
			$dataTypeEtape["param2"] = Bral_Util_De::get_1D2();
			if (Bral_Util_Quete::ETAPE_TUER_PARAM2_ETAT_AFFAME == $dataTypeEtape["param2"]) {
				$dataTypeEtape["libelle_etape"] = "En étant affamé, vous devez tuer 1 monstre";
			} else {
				$dataTypeEtape["libelle_etape"] = "En étant repu, vous devez tuer 1 monstre";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeTuerParam1et2 invalide:".$dataTypeEtape["param1"]);
		}
	}

	private function pepareParamTypeEtapeTuerParam3et4(&$dataTypeEtape) {
		$dataTypeEtape["param3"] = Bral_Util_De::get_1d3();

		if (Bral_Util_Quete::ETAPE_TUER_PARAM3_TAILLE == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("TailleMonstre");
			$tailleMonstreTable = new TailleMonstre();
			$tailles = $tailleMonstreTable->fetchAll();
			$deTaille = Bral_Util_De::get_de_specifique(0, count($tailles) - 1);
			$dataTypeEtape["param4"] = $tailles[$deTaille]["id_taille_monstre"];
			$dataTypeEtape["libelle_etape"] .= " de taille ".$tailles[$deTaille]["nom_taille_f_monstre"];
		} else if (Bral_Util_Quete::ETAPE_TUER_PARAM3_TYPE == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("TypeMonstre");
			$typeMonstreTable = new TypeMonstre();
			$types = $typeMonstreTable->fetchAll();
			$deType = Bral_Util_De::get_de_specifique(0, count($types) - 1);
			$dataTypeEtape["param4"] = $types[$deType]["id_type_monstre"];
			$dataTypeEtape["libelle_etape"] .= " de type ".$types[$deType]["nom_type_monstre"];
		} else if (Bral_Util_Quete::ETAPE_TUER_PARAM3_NIVEAU == $dataTypeEtape["param3"]) {
			$dataTypeEtape["param4"] = $this->view->user->niveau_hobbit + Bral_Util_De::get_1d6();
			$dataTypeEtape["libelle_etape"] .= " de niveau ".$dataTypeEtape["param4"];
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeTuer param1 invalide:".$param1);
		}
	}

	private function pepareParamTypeEtapeManger() {
		$dataTypeEtape = $this->initDataTypeEtape();

		$dataTypeEtape["param1"] = Bral_Util_De::get_de_specifique(5, 10);
		$dataTypeEtape["libelle_etape"] = "Vous devez manger ".$dataTypeEtape["param1"]." repas";

		$this->pepareParamTypeEtapeMangerParam2et3($dataTypeEtape);

		$dataTypeEtape["param4"] = Bral_Util_De::get_1D7();
		$dataTypeEtape["libelle_etape"] .= ", un ".Bral_Helper_Calendrier::getJourSemaine($dataTypeEtape["param4"]);

		$dataTypeEtape["libelle_etape"] .= ".";
		return $dataTypeEtape;
	}

	private function pepareParamTypeEtapeMangerParam2et3(&$dataTypeEtape) {

		$dataTypeEtape["param2"] = Bral_Util_De::get_1d3();

		if (Bral_Util_Quete::ETAPE_MANGER_PARAM2_AUBERGE == $dataTypeEtape["param2"]) {
			$lieuTable = new Lieu();
			$auberges = $lieuTable->findByType($this->view->config->game->lieu->type->auberge);
			$deAuberge = Bral_Util_De::get_de_specifique(0, count($auberges) -1);
			$auberge = $auberges[$deAuberge];
			$dataTypeEtape["param3"] = $auberge["id_lieu"];
			$dataTypeEtape["libelle_etape"] .= " dans l'auberge de ".$auberge["nom_ville"]." en x:".$auberge["x_lieu"]." et y:".$auberge["y_lieu"];
		} else if (Bral_Util_Quete::ETAPE_MANGER_PARAM2_TERRAIN == $dataTypeEtape["param2"]) {
			Zend_Loader::loadClass("Environnement");
			$environnementTable = new Environnement();
			$environnements = $environnementTable->fetchAll();
			$deEnvironnement = Bral_Util_De::get_de_specifique(0, count($environnements) -1);
			$environnement = $environnements[$deEnvironnement];
			$dataTypeEtape["param3"] = $environnement["id_environnement"];
			$dataTypeEtape["libelle_etape"] .= " sur un terrain de type ".$environnement["nom_environnement"];
		} else if (Bral_Util_Quete::ETAPE_MANGER_PARAM2_ETAT == $dataTypeEtape["param2"]) {
			$dataTypeEtape["param3"] = Bral_Util_De::get_1D2();
			if (Bral_Util_Quete::ETAPE_MANGER_PARAM3_ETAT_AFFAME == $dataTypeEtape["param3"]) {
				$dataTypeEtape["libelle_etape"] .= " en étant affamé";
			} else {
				$dataTypeEtape["libelle_etape"] .= " en étant repu";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeMangerParam2et3 param2 invalide:".$dataTypeEtape["param2"]);
		}
	}

	private function pepareParamTypeEtapeFumer() {
		$dataTypeEtape = $this->initDataTypeEtape();

		$dataTypeEtape["libelle_etape"] = "Vous devez fumer";
		$this->pepareParamTypeEtapeFumerParam1($dataTypeEtape);
		$this->pepareParamTypeEtapeFumerParam2et3($dataTypeEtape);
		$this->pepareParamTypeEtapeFumerParam4et5($dataTypeEtape);

		$dataTypeEtape["libelle_etape"] = $dataTypeEtape["libelle_etape"].".";
		return $dataTypeEtape;
	}

	private function pepareParamTypeEtapeFumerParam1(&$dataTypeEtape) {
		Zend_Loader::loadClass("TypeTabac");
		$typeTabacTable = new TypeTabac();
		$typeTabacs = $typeTabacTable->fetchAll();
		$deTypeTabac = Bral_Util_De::get_de_specifique(0, count($typeTabacs) -1);
		$typeTabac = $typeTabacs[$deTypeTabac];
		$dataTypeEtape["param1"] = $typeTabac["id_type_tabac"];
		$dataTypeEtape["libelle_etape"] .= " du tabac de type ".$typeTabac["nom_type_tabac"].", ";
	}

	private function pepareParamTypeEtapeFumerParam2et3(&$dataTypeEtape) {
		$dataTypeEtape["param2"] = Bral_Util_De::get_1d2();

		if (Bral_Util_Quete::ETAPE_FUMER_PARAM2_JOUR == $dataTypeEtape["param2"]) {
			$dataTypeEtape["param3"] = Bral_Util_De::get_1D7();
			$dataTypeEtape["libelle_etape"] .= "un ".Bral_Helper_Calendrier::getJourSemaine($dataTypeEtape["param3"]);
		} else if (Bral_Util_Quete::ETAPE_FUMER_PARAM2_ETAT == $dataTypeEtape["param2"]) {
			$dataTypeEtape["param3"] = Bral_Util_De::get_1D2();
			if (Bral_Util_Quete::ETAPE_FUMER_PARAM3_ETAT_AFFAME == $dataTypeEtape["param3"]) {
				$dataTypeEtape["libelle_etape"] .= "en étant affamé";
			} else {
				$dataTypeEtape["libelle_etape"] .= "en étant repu";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeFumerParam2et3 param2 invalide:".$dataTypeEtape["param2"]);
		}
	}

	private function pepareParamTypeEtapeFumerParam4et5(&$dataTypeEtape) {
		$dataTypeEtape["param4"] = Bral_Util_De::get_1d2();

		if (Bral_Util_Quete::ETAPE_FUMER_PARAM4_TERRAIN == $dataTypeEtape["param4"]) {
			Zend_Loader::loadClass("Environnement");
			$environnementTable = new Environnement();
			$environnements = $environnementTable->fetchAll();
			$deEnvironnement = Bral_Util_De::get_de_specifique(0, count($environnements) -1);
			$environnement = $environnements[$deEnvironnement];
			$dataTypeEtape["param5"] = $environnement["id_environnement"];
			$dataTypeEtape["libelle_etape"] .= ", sur un terrain de type ".$environnement["nom_environnement"];
		} else if (Bral_Util_Quete::ETAPE_FUMER_PARAM4_VILLE == $dataTypeEtape["param4"]) {
			Zend_Loader::loadClass("Ville");
			$villeTable = new Ville();
			$villes = $villeTable->fetchAll();
			$deVille = Bral_Util_De::get_de_specifique(0, count($villes) -1);
			$ville = $villes[$deVille];
			$dataTypeEtape["param5"] = $ville["id_ville"];
			$dataTypeEtape["libelle_etape"] .= ", dans la ville de ".$ville["nom_ville"];
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeMangerParam2et3 param2 invalide:".$dataTypeEtape["param2"]);
		}
	}

	private function pepareParamTypeEtapeMarcher() {
		$dataTypeEtape = $this->initDataTypeEtape();

		$this->pepareParamTypeEtapeMarcherParam1et2($dataTypeEtape);
		$this->pepareParamTypeEtapeMarcherParam3et4et5($dataTypeEtape);

		$dataTypeEtape["libelle_etape"] = $dataTypeEtape["libelle_etape"].".";
		return $dataTypeEtape;
	}

	private function pepareParamTypeEtapeMarcherParam1et2(&$dataTypeEtape) {
		$dataTypeEtape["param1"] = Bral_Util_De::get_1d2();

		if (Bral_Util_Quete::ETAPE_MARCHER_PARAM1_JOUR == $dataTypeEtape["param1"]) {
			$dataTypeEtape["param2"] = Bral_Util_De::get_1D7();
			$dataTypeEtape["libelle_etape"] = "Un ".Bral_Helper_Calendrier::getJourSemaine($dataTypeEtape["param2"]).", ";
		} else if (Bral_Util_Quete::ETAPE_MARCHER_PARAM1_ETAT == $dataTypeEtape["param1"]) {
			$dataTypeEtape["param2"] = Bral_Util_De::get_1D2();
			if (Bral_Util_Quete::ETAPE_MARCHER_PARAM2_ETAT_AFFAME == $dataTypeEtape["param2"]) {
				$dataTypeEtape["libelle_etape"] = "En étant affamé, ";
			} else {
				$dataTypeEtape["libelle_etape"] = "En étant repu, ";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeTuerParam1et2 invalide:".$dataTypeEtape["param1"]);
		}
		$dataTypeEtape["libelle_etape"] .= " vous devez marcher ";
	}

	private function pepareParamTypeEtapeMarcherParam3et4et5(&$dataTypeEtape) {
		$dataTypeEtape["param3"] = Bral_Util_De::get_1d3();

		if (Bral_Util_Quete::ETAPE_MARCHER_PARAM3_TERRAIN == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("Environnement");
			$environnementTable = new Environnement();
			$environnements = $environnementTable->fetchAll();
			$deEnvironnement = Bral_Util_De::get_de_specifique(0, count($environnements) -1);
			$environnement = $environnements[$deEnvironnement];
			$dataTypeEtape["param4"] = $environnement["id_environnement"];
			$dataTypeEtape["libelle_etape"] .= "sur un terrain de type ".$environnement["nom_environnement"];
		} else if (Bral_Util_Quete::ETAPE_MARCHER_PARAM3_LIEU == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("Lieu");
			$lieuTable = new Lieu();
			$lieux = $lieuTable->fetchAll("est_soule_lieu = 'non'");
			$deLieu = Bral_Util_De::get_de_specifique(0, count($lieux) -1);
			$lieu = $lieux[$deLieu];
			$dataTypeEtape["param4"] = $lieu["id_lieu"];
			$dataTypeEtape["libelle_etape"] .= "sur le lieu suivant : ".$lieu["nom_lieu"]. ", en x:".$lieu["x_lieu"]." et y:".$lieu["y_lieu"];
		} else if (Bral_Util_Quete::ETAPE_MARCHER_PARAM3_POSITION == $dataTypeEtape["param3"]) {
			$x = Bral_Util_De::get_de_specifique(0, $this->view->config->game->x_max * 2) - $this->view->config->game->x_max;
			$y = Bral_Util_De::get_de_specifique(0, $this->view->config->game->y_max * 2) - $this->view->config->game->y_max;

			Zend_Loader::loadClass("Palissade");
			$palissadeTable = new Palissade();
			$palissades = $palissadeTable->findByCase($x, $y);
			if ($palissades != null && count($palissades) == 1 && $palissades[0]["est_destructible_palissade"] == "non") {
				$x = 0;
				$y = 0;
			} else if (count($palissades) > 1) {
				throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeMarcherParam3et4et5 nb Palissade invalides ! x:".$x. " y:".$y);
			}

			$dataTypeEtape["param4"] = $x;
			$dataTypeEtape["param5"] = $y;
			$dataTypeEtape["libelle_etape"] .= " en x:".$x." et y:".$y;
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapeMarcherParam3et4et5 param3 invalide:".$dataTypeEtape["param3"]);
		}
	}

	private function pepareParamTypeEtapePosseder() {
		$dataTypeEtape = $this->initDataTypeEtape();

		$this->pepareParamTypeEtapePossederParam1et2($dataTypeEtape);
		$this->pepareParamTypeEtapePossederParam3et4et5($dataTypeEtape);

		$dataTypeEtape["libelle_etape"] = $dataTypeEtape["libelle_etape"].$dataTypeEtape["libelle_etape_fin"];
		return $dataTypeEtape;
	}

	private function pepareParamTypeEtapePossederParam1et2(&$dataTypeEtape) {
		
		if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_CASTAR == $dataTypeEtape["param3"]) {
			$dataTypeEtape["param1"] = Bral_Util_De::get_de_specifique($this->view->user->niveau_hobbit * 10, $this->view->user->niveau_hobbit * 20);
		} else { // Pour les materiaux : 1D20 + 5
			$dataTypeEtape["param1"] = Bral_Util_De::get_1d20() + 5;
		}
		
		$dataTypeEtape["libelle_etape"] = "Vous devez posséder ";
		$dataTypeEtape["param2"] = Bral_Util_De::get_1d2();
		if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM2_COFFRE == $dataTypeEtape["param2"]) {
			$dataTypeEtape["libelle_etape"] .= "dans votre coffre ".$dataTypeEtape["param1"];
			$dataTypeEtape["libelle_etape_fin"] = ". Le calcul est fait sur l'action de dépot de votre part dans le coffre, les éléments seront détruits à la fin de l'étape.";
		} else if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM2_LABAN == $dataTypeEtape["param2"]) {
			$dataTypeEtape["libelle_etape"] .= "dans votre laban ".$dataTypeEtape["param1"];
			$dataTypeEtape["libelle_etape_fin"] = ". Le calcul est fait sur la compétence Ramasser, les éléments seront détruits à la fin de l'étape.";
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapePossederParam1et2 param3 invalide:".$dataTypeEtape["param2"]);
		}
	}

	private function pepareParamTypeEtapePossederParam3et4et5(&$dataTypeEtape) {
		$dataTypeEtape["param3"] = Bral_Util_De::get_1d5();

		if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_MINERAI == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("TypeMinerai");
			$typeMineraiTable = new TypeMinerai();
			$typeMinerais = $typeMineraiTable->fetchAll();
			$deTypeMinerai = Bral_Util_De::get_de_specifique(0, count($typeMinerais) -1);
			$typeMinerai = $typeMinerais[$deTypeMinerai];
			$dataTypeEtape["param4"] = $typeMinerai["id_type_minerai"];
			$dataTypeEtape["libelle_etape"] .= " minerais bruts de type ".$typeMinerai["nom_type_minerai"];
		} else if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_PLANTE == $dataTypeEtape["param3"]) {
			Zend_Loader::loadClass("Bral_Util_Plantes");
			$plantes = Bral_Util_Plantes::getTabPlantes();
			$dePlante = Bral_Util_De::get_de_specifique(0, count($plantes)-1);
			$plante = $plantes[$dePlante];
				
			if ($plante["nom_type_plante"]{0} == "A" || $plante["nom_type_plante"]{0} == "Y" || $plante["nom_type_plante"]{0} == "E") {
				$d = "d'";
			} else {
				$d = "de ";
			}
			$dataTypeEtape["libelle_etape"] .= " ".$plante["nom_type_partieplante"]."s ".$d."".$plante["nom_type_plante"];

			$dataTypeEtape["param4"] = $plante["id_type_plante"];
			$dataTypeEtape["param5"] = $plante["id_type_partieplante"];
		} else if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_PEAU == $dataTypeEtape["param3"]) {
			$dataTypeEtape["libelle_etape"] .= " peaux";
		} else if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_FOURRURE == $dataTypeEtape["param3"]) {
			$dataTypeEtape["libelle_etape"] .= " fourrures";
		} else if (Bral_Util_Quete::ETAPE_POSSEDER_PARAM3_CASTAR == $dataTypeEtape["param3"]) {
			$dataTypeEtape["libelle_etape"] .= " castars";
		} else {
			throw new Zend_Exception(get_class($this)."::pepareParamTypeEtapePossederParam1et2 param3 invalide:".$dataTypeEtape["param3"]);
		}
	}

	private function pepareParamTypeEtapeEquiper() {
		//TODO
	}

	private function pepareParamTypeEtapeConstruire() {
		//TODO
	}

	private function pepareParamTypeEtapeFabriquer() {
		//TODO
	}

	private function pepareParamTypeEtapeCollecter() {
		//TODO
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_quetes"));
	}

	private function calculCoutCastars() {
		return 5;
	}
}