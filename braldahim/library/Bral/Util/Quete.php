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
class Bral_Util_Quete {

	const QUETE_ETAPE_TUER_ID = 1;
	const QUETE_ETAPE_MANGER_ID = 2;
	const QUETE_ETAPE_FUMER_ID = 3;
	const QUETE_ETAPE_POSSEDER_ID = 4;
	const QUETE_ETAPE_EQUIPER_ID = 5;
	const QUETE_ETAPE_CONSTRUIRE_ID = 6;
	const QUETE_ETAPE_FABRIQUER_ID = 7;
	const QUETE_ETAPE_COLLECTER_ID = 8;

	const ETAPE_TUER_PARAM1_NOMBRE = 1;
	const ETAPE_TUER_PARAM1_JOUR = 2;
	const ETAPE_TUER_PARAM1_ETAT = 3;

	const ETAPE_TUER_PARAM2_ETAT_AFFAME = 1;
	const ETAPE_TUER_PARAM2_ETAT_REPU = 2;

	const ETAPE_TUER_PARAM3_TAILLE = 1;
	const ETAPE_TUER_PARAM3_TYPE = 2;
	const ETAPE_TUER_PARAM3_NIVEAU = 3;

	const ETAPE_MANGER_PARAM2_AUBERGE = 1;
	const ETAPE_MANGER_PARAM2_TERRAIN = 2;
	const ETAPE_MANGER_PARAM2_ETAT = 3;

	const ETAPE_MANGER_PARAM3_ETAT_AFFAME = 1;
	const ETAPE_MANGER_PARAM3_ETAT_REPU = 2;

	private static function estQueteEnCours($hobbit) {
		if ($hobbit->est_quete_hobbit == "oui") {
			return true;
		} else {
			return false;
		}
	}

	private static function getEtapeCourante(&$hobbit, $idTypeEtape) {
		Zend_Loader::loadClass("Etape");
		$etapeTable = new Etape();
		return $etapeTable->findEnCoursByIdHobbitAndIdTypeEtape($hobbit->id_hobbit, $idTypeEtape);
	}

	private static function activeProchaineEtape(&$hobbit) {
		$etapeTable = new Etape();
		$etape = $etapeTable->findProchaineEtape($hobbit->id_hobbit);
		if ($etape) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - activeProchaineEtape - Activation prochaine etape");
			$data = array("date_debut_etape" => date("Y-m-d H:i:s"));
			$where = "id_etape=".$etape["id_etape"];
			$etapeTable->update($data, $where);
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - activeProchaineEtape - Pas de prochaine etape");
			return false; // fin quete
		}
	}

	private static function termineQuete(&$hobbit) {

		Bral_Util_Log::quete()->trace("Bral_Util_Quete - termineQuete - Fin de la quete");

		$hobbit->est_quete_hobbit = 'non';
		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();
		$quete = $queteTable->findEnCoursByIdHobbit($hobbit->id_hobbit);
		if ($quete == null) {
			throw new Zend_Exception("Bral_Util_Quete::termineQuete nbInvalide:".$hobbit->id_hobbit);
		} else {
			$etapeTable = new Etape();
			$nbEtape = $etapeTable->countByIdQuete($quete["id_quete"]);
			$gain = self::calculGain($hobbit, $nbEtape);
			$data = array(
				"date_fin_quete" => date("Y-m-d H:i:s"),
				"gain_quete" => $gain,
			);
			$where = "id_quete=".$quete["id_quete"];
			$queteTable->update($data, $where);
		}
	}

	private static function calculGain(&$hobbit, $nbEtape) {
		Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculGain - enter");
		$nbRecompenses = $nbEtape - Bral_Util_De::get_1d3();
		if ($nbRecompenses < 1) {
			$nbRecompenses = 1;
		}

		$retour = "";

		$liste = array();
		for ($i = 1; $i<=$nbRecompenses; $i++) {
			$n = Bral_Util_De::get_de_specifique_hors_liste(1, 5, $liste);
			$liste[] = $n;

			if ($n == 1) {
				$retour .= self::calculGainRune($hobbit);
			} elseif ($n == 2) {
				$retour .= self::calculGainExperience($hobbit, $nbRecompenses, $nbEtape);
			} elseif ($n == 3) {
				$retour .= self::calculGainCastars($hobbit, $nbRecompenses, $nbEtape);
			} elseif ($n == 4) {
				$retour .= self::calculGainMinerais($hobbit, $nbRecompenses, $nbEtape);
			} elseif ($n == 5) {
				$retour .= self::calculGainPlantes($hobbit, $nbRecompenses, $nbEtape);
			}
		}

		Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculGain - exit:".$retour);
		return $retour;
	}

	private static function calculGainRune(&$hobbit) {
		Zend_Loader::loadClass("ElementRune");
		Zend_Loader::loadClass("CoffreRune");
		Zend_Loader::loadClass("TypeRune");

		if (Bral_Util_De::get_1d2() == 1) {
			$niveauRune = 'a';
		} else {
			$niveauRune = 'b';
		}

		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);

		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			throw new Zend_Exception("Bral_Util_Quete::calculGainRune niveauRune:".$niveauRune);
		}

		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);

		$typeRune = $typeRuneRowset[$numeroRune];

		$dateCreation = date("Y-m-d H:i:s");
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, 10);

		$elementRuneTable = new ElementRune();
		$data = array(
			"x_element_rune"  => $hobbit->x_hobbit,
			"y_element_rune" => $hobbit->y_hobbit,
			"id_fk_type_element_rune" => $typeRune["id_type_rune"],
			"date_depot_element_rune" => $dateCreation,
			"date_fin_element_rune" => $dateFin,
		);

		$idRune = $elementRuneTable->insert($data);

		$where = "id_element_rune=".$idRune;
		$elementRuneTable->delete($where);

		$coffreRuneTable = new CoffreRune();
		$data = array (
			"id_rune_coffre_rune" => $idRune,
			"id_fk_type_coffre_rune" => $typeRune["id_type_rune"],
			"id_fk_hobbit_coffre_rune" => $hobbit->id_hobbit,
			"est_identifiee_rune" => "oui",
		);
		$coffreRuneTable->insert($data);

		$retour = " une rune de type ".$typeRune["nom_type_rune"].PHP_EOL;
		return $retour;
	}

	private static function calculGainExperience(&$hobbit, $nbRecompenses, $nbEtape) {
		$nbPx = floor((($hobbit->niveau_hobbit / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_de_specifique(1, $hobbit->niveau_hobbit));
		if ($nbPx < 1) {
			$nbPx = 1;
		}

		$hobbit->px_perso_hobbit = $hobbit->px_perso_hobbit + $nbPx;
		$retour = " ".$nbPx." PX ".PHP_EOL;
		return $retour;
	}

	private static function calculGainCastars(&$hobbit, $nbRecompenses, $nbEtape) {
		$nbCastars = floor((($hobbit->niveau_hobbit / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_de_specifique(1, $hobbit->niveau_hobbit)) * 10;
		if ($nbCastars < 2) {
			$nbCastars = 2;
		}

		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();
		$data = array(
			"quantite_castar_coffre" => $nbCastars,
			"id_fk_hobbit_coffre" => $hobbit->id_hobbit,
		);
		$coffreTable->insertOrUpdate($data);

		$retour = " ".$nbCastars." castars (dans votre coffre) ".PHP_EOL;
		return $retour;
	}

	private static function calculGainMinerais(&$hobbit, $nbRecompenses, $nbEtape) {
		$nbMinerais = floor((($hobbit->niveau_hobbit / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_1d6());
		if ($nbMinerais < 2) {
			$nbMinerais = 2;
		}

		$typeMinerai = "todo";
		Zend_Loader::loadClass("TypeMinerai");
		$typeMineraiTable = new TypeMinerai();
		$types = $typeMineraiTable->fetchAll();

		$n = Bral_Util_De::get_de_specifique(1, count($types));
		$typeMinerai = $types[$n-1];

		$data = array (
				"id_fk_hobbit_coffre_minerai" => $hobbit->id_hobbit,
				"id_fk_type_coffre_minerai" => $typeMinerai["id_type_minerai"],
				"quantite_brut_coffre_minerai" => $nbMinerais,
		);

		Zend_Loader::loadClass("CoffreMinerai");
		$coffreMineraiTable = new CoffreMinerai();
		$coffreMineraiTable->insertOrUpdate($data);

		$retour = " ".$nbMinerais." minerais bruts de type ".$typeMinerai["nom_type_minerai"]." (dans votre coffre) ".PHP_EOL;
		return $retour;
	}

	private static function calculGainPlantes(&$hobbit, $nbRecompenses, $nbEtape) {
		$retour = "";

		$nbPartiesPlantes = floor((($hobbit->niveau_hobbit / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_1d6());
		if ($nbPartiesPlantes < 2) {
			$nbPartiesPlantes = 2;
		}

		Zend_Loader::loadClass("Bral_Util_Plantes");
		$plantes = Bral_Util_Plantes::getTabPlantes();
		$nbPlantes = count($plantes);

		Zend_Loader::loadClass("CoffrePartieplante");

		$tirage1 = Bral_Util_De::get_de_specifique(0, $nbPlantes - 1);
		$tirage2 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbPlantes - 1, array($tirage1));
		$tirage3 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbPlantes - 1, array($tirage1, $tirage2));

		$nbUnitaireGain = ceil($nbGain / 3);
		$retour .= self::calculGainPlantesDb($hobbit, $plantes, $tirage1, $nbUnitaireGain) ;
		$retour .= self::calculGainPlantesDb($hobbit, $plantes, $tirage2, $nbUnitaireGain) ;
		$retour .= self::calculGainPlantesDb($hobbit, $plantes, $tirage3, $nbUnitaireGain) ;

		return $retour;
	}

	private static function calculGainPlantesDb(&$hobbit, $plantes, $tirage, $nbUnitaireGain) {

		$coffrePartieplanteTable = new CoffrePartieplante();
		$data = array (
				"id_fk_hobbit_coffre_partieplante" => $hobbit->id_hobbit,
				"id_fk_type_coffre_partieplante" => $plantes[$tirage]["id_type_partieplante"],
				"id_fk_type_plante_coffre_partieplante" => $plantes[$tirage]["id_type_plante"],
				"quantite_coffre_partieplante" => $nbUnitaireGain,
		);
		$coffrePartieplanteTable->insertOrUpdate($data);

		$texte = "  ".$nbUnitaireGain ." ".$plantes[$tirage]["nom_type_partieplante"]."$s de ".$plantes[$tirage]["nom_type_plante"].PHP_EOL;

		return $texte;
	}

	public static function etapeTuer(&$hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		if (self::estQueteEnCours($hobbit)) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - etapeTuer - quete en cours -");
			$etape = self::getEtapeCourante($hobbit, self::QUETE_ETAPE_TUER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - etapeTuer - pas d'etape tuer en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - etapeTuer - etape tuer en cours");
				return self::calculEtapeTuer($etape, $hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeTuer($etape, &$hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		if (self::calculEtapeTuerParam1($etape, $hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre)
		&& self::calculEtapeTuerParam3($etape, $hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre)) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - etapeTuer - conditions remplies, calcul fin etape");
			self::calculEtapeTuerFin($etape, $hobbit);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeTuerParam1($etape, &$hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		$retour = false;
		Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - param1:".$etape["param_1_etape"]. " param2:".$etape["param_2_etape"]);
		if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_NOMBRE) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - A");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_JOUR && $etape["param_2_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - B");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_ETAT) {
			if ($etape["param_2_etape"] == self::ETAPE_TUER_PARAM2_ETAT_AFFAME && $hobbit->balance_faim_hobbit < 1) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - C");
				$retour = true;
			} elseif ($etape["param_2_etape"] == self::ETAPE_TUER_PARAM2_ETAT_REPU && $hobbit->balance_faim_hobbit >= 95) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - D");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerParam1 - E");
		}
		return $retour;
	}

	private static function calculEtapeTuerParam3($etape, &$hobbit, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		$retour = false;
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeTuerParam3 - param3:".$etape["param_3_etape"]. " param4:".$etape["param_4_etape"] . " taille:".$tailleMonstre. " type:".$typeMonstre. " niv:".$niveauMonstre);
		if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_TAILLE && $etape["param_4_etape"] == $tailleMonstre) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeTuerParam3 - A");
			return true;
		} else if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_TYPE && $etape["param_4_etape"] == $typeMonstre) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeTuerParam3 - B");
			return true;
		} else if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_NIVEAU && $etape["param_4_etape"] == $niveauMonstre) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeTuerParam3 - C");
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeTuerParam3 - D");
			return false;
		}
		return $retour;
	}

	private static function calculEtapeTuerFin($etape, &$hobbit) {
		$etapeTable = new Etape();
		$estFinEtape = false;
		if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_NOMBRE) {
			$data = array( "objectif_etape" => $etape["objectif_etape"] + 1);
			if ($etape["objectif_etape"] + 1 >= $etape["param_2_etape"]) {
				$data = array( "objectif_etape" => $etape["objectif_etape"] + 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerFin - Fin Ok 1");
				$estFinEtape = true;
			}
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_JOUR ||
		$etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_ETAT) {
			$data = array( "objectif_etape" => 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeTuerFin - Fin Ok 2");
			$estFinEtape = true;
		} else {
			throw new Zend_Exception("::calculEtapeTuerParam1 param1 invalide:".$etape["param_1_etape"]);
		}
		$where = "id_etape = ".$etape["id_etape"];
		$etapeTable->update($data, $where);
		if ($estFinEtape) {
			if (self::activeProchaineEtape($hobbit) == false) { // fin quete
				self::termineQuete($hobbit);
			}
		}
	}

	public static function etapeManger(&$hobbit) {
		if (self::estQueteEnCours($hobbit)) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::etapeManger - quete en cours -");
			$etape = self::getEtapeCourante($hobbit, self::QUETE_ETAPE_MANGER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete::etapeManger - pas d'etape manger en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete::etapeManger - etape manger en cours");
				return self::calculEtapeManger($etape, $hobbit);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeManger($etape, &$hobbit) {
		if (self::calculEtapeMangerParam3($etape, $hobbit)
		&& self::calculEtapeMangerParam4($etape, $hobbit)) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeManger - conditions remplies, calcul fin etape");
			self::calculEtapeMangerFin($etape, $hobbit);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeMangerParam3($etape, &$hobbit) {
		$retour = false;
		Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - param2:".$etape["param_2_etape"]. " param3:".$etape["param_3_etape"]);
		if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_AUBERGE) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - A");
			//TODO
			$retour = true;
		} else if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_TERRAIN && $etape["param_2_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - B");
			//TODO
			$retour = true;
		} else if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_ETAT) {
			if ($etape["param_3_etape"] == self::ETAPE_MANGER_PARAM3_ETAT_AFFAME && $hobbit->balance_faim_hobbit < 1) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - C");
				$retour = true;
			} elseif ($etape["param_3_etape"] == self::ETAPE_MANGER_PARAM3_ETAT_REPU && $hobbit->balance_faim_hobbit >= 95) {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - D");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam3 - E");
		}
		return $retour;
	}

	private static function calculEtapeMangerParam4($etape, &$hobbit) {
		if ($etape["param_4_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam4 - A");
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Bral_Util_Quete - calculEtapeMangerParam4 - B");
			return false;
		}
	}

	private static function calculEtapeMangerFin($etape, &$hobbit) {
		$etapeTable = new Etape();

		$estFinEtape = false;

		$data = array( "objectif_etape" => $etape["objectif_etape"] + 1);
		if ($etape["objectif_etape"] + 1 >= $etape["param_1_etape"]) {
			$data = array("objectif_etape" => $etape["objectif_etape"] + 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
			Bral_Util_Log::quete()->trace("Bral_Util_Quete::calculEtapeMangerFin - Fin Ok");
			$estFinEtape = true;
		}

		$where = "id_etape = ".$etape["id_etape"];
		$etapeTable->update($data, $where);
		if ($estFinEtape) {
			if (self::activeProchaineEtape($hobbit) == false) { // fin quete
				self::termineQuete($hobbit);
			}
		}
	}
}
