<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Quete
{

	const QUETE_ID_LIEU_INITIATIQUE = 256;

	const QUETE_ETAPE_TUER_ID = 1;
	const QUETE_ETAPE_MANGER_ID = 2;
	const QUETE_ETAPE_FUMER_ID = 3;
	const QUETE_ETAPE_MARCHER_ID = 4;
	const QUETE_ETAPE_POSSEDER_ID = 5;
	const QUETE_ETAPE_EQUIPER_ID = 6;
	const QUETE_ETAPE_CONSTRUIRE_ID = 7;
	const QUETE_ETAPE_FABRIQUER_ID = 8;
	const QUETE_ETAPE_COLLECTER_ID = 9;
	const QUETE_ETAPE_CONTACTER_PARENTS_ID = 10;
	const QUETE_ETAPE_APPRENDRE_METIER_ID = 11;
	const QUETE_ETAPE_AMELIORER_CARACTERISTIQUE_ID = 12;
	const QUETE_ETAPE_APPRENDRE_IDENTIFICATION_RUNES_ID = 13;

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

	const ETAPE_FUMER_PARAM2_JOUR = 1;
	const ETAPE_FUMER_PARAM2_ETAT = 2;

	const ETAPE_FUMER_PARAM3_ETAT_AFFAME = 1;
	const ETAPE_FUMER_PARAM3_ETAT_REPU = 2;

	const ETAPE_FUMER_PARAM4_TERRAIN = 1;
	const ETAPE_FUMER_PARAM4_VILLE = 2;

	const ETAPE_MARCHER_PARAM1_JOUR = 1;
	const ETAPE_MARCHER_PARAM1_ETAT = 2;
	const ETAPE_MARCHER_PARAM1_RIEN = 3;

	const ETAPE_MARCHER_PARAM2_ETAT_AFFAME = 1;
	const ETAPE_MARCHER_PARAM2_ETAT_REPU = 2;

	const ETAPE_MARCHER_PARAM3_TERRAIN = 1;
	const ETAPE_MARCHER_PARAM3_LIEU = 2;
	const ETAPE_MARCHER_PARAM3_POSITION = 3; // A supprimer quand tout les étapes de ce type sont terminées

	const ETAPE_POSSEDER_PARAM2_COFFRE = 1;
	const ETAPE_POSSEDER_PARAM2_LABAN = 2;

	const ETAPE_POSSEDER_PARAM3_MINERAI = 1;
	const ETAPE_POSSEDER_PARAM3_PLANTE = 2;
	const ETAPE_POSSEDER_PARAM3_PEAU = 3;
	const ETAPE_POSSEDER_PARAM3_FOURRURE = 4;
	const ETAPE_POSSEDER_PARAM3_CASTAR = 5;

	const ETAPE_EQUIPER_PARAM2_JOUR = 1;
	const ETAPE_EQUIPER_PARAM2_VILLE = 2;

	const ETAPE_CONSTRUIRE_PARAM1_CUISINIER = 8;
	const ETAPE_CONSTRUIRE_PARAM1_BUCHERON = 3;
	const ETAPE_CONSTRUIRE_PARAM1_TERRASSIER = 11;

	const ETAPE_CONSTRUIRE_PARAM3_VILLE = 1;
	const ETAPE_CONSTRUIRE_PARAM3_TERRAIN = 2;

	const ETAPE_CONSTUIRE_COMPETENCE_MONTERPALISSADE = "monterpalissade";
	const ETAPE_CONSTUIRE_COMPETENCE_CUISINER = "cuisiner";
	const ETAPE_CONSTUIRE_COMPETENCE_CONSTUIRE = "construire";

	const ETAPE_FABRIQUER_PARAM1_TYPE_PIECE = 1;

	public static function creationQueteInitiatique($braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::creationQueteInitiatique - enter");

		Zend_Loader::loadClass("Etape");
		Zend_Loader::loadClass("Lieu");

		$idQuete = self::creationQueteDb($braldun->id_braldun, self::QUETE_ID_LIEU_INITIATIQUE);
		$etapes = self::prepareEtapeQueteInitiatique($idQuete, $braldun, $config);
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::creationQueteInitiatique - exit");
		return $etapes;
	}

	private static function prepareEtapeQueteInitiatique($idQuete, $braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::prepareEtapeQueteInitiatique - enter");
		$numero = 1;
		$etapeContact = self::prepareEtapeQueteInitiatiqueContacterParents($numero, $idQuete, $braldun);
		if ($etapeContact != null) {
			$etapes[] = $etapeContact;
			$dateDebut = null;
		} else {
			$dateDebut = date("Y-m-d H:i:s");
		}
		$etapes[] = self::prepareEtapeQueteInitiatiqueManger($numero, $idQuete, $braldun, $config, $dateDebut);
		$etapes[] = self::prepareEtapeQueteInitiatiqueMetier($numero, $idQuete, $braldun, $config);
		$etapes[] = self::prepareEtapeQueteInitiatiqueAmeliorerCaracteristique($numero, $idQuete, $braldun, $config);
		$etapes[] = self::prepareEtapeQueteInitiatiqueApprendreIdentifier($numero, $idQuete, $braldun, $config);
		$etapes[] = self::prepareEtapeQueteInitiatiqueMarcherMaison($numero, $idQuete, $braldun, $config);
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::prepareEtapeQueteInitiatique - exit");
		return $etapes;
	}

	private static function getDataEtape($idQuete, $idBraldun, $idTypeEtape, $dateDebutEtape, $libelleEtape, $ordre)
	{
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::getDataEtape - enter");
		$data = array(
			"id_fk_quete_etape" => $idQuete,
			"id_fk_type_etape" => $idTypeEtape,
			"id_fk_braldun_etape" => $idBraldun,
			"libelle_etape" => $libelleEtape,
			"param_1_etape" => null,
			"param_2_etape" => null,
			"param_3_etape" => null,
			"param_4_etape" => null,
			"param_5_etape" => null,
			"date_debut_etape" => $dateDebutEtape,
			"ordre_etape" => $ordre,
		);
		Bral_Util_Log::quete()->trace("Bral_Util_Quete::getDataEtape - exit");
		return $data;
	}

	private static function prepareEtapeQueteInitiatiqueContacterParents(&$numero, $idQuete, $braldun)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueContacterParents - enter");

		Zend_Loader::loadClass("Bral_Util_Famille");
		$tabPossedeParents = Bral_Util_Famille::getTabPossedeParentsActif($braldun);

		$dataEtape = null;

		if ($tabPossedeParents["est_orphelin"] == false && $tabPossedeParents["est_pere_actif"] == true && $tabPossedeParents["est_mere_actif"] == true) {
			$libelleEtape = "Vous devez contacter vos parents à l'aide de la messagerie du jeu, en les mettant tous les deux destinataires du même message.";
			$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_CONTACTER_PARENTS_ID, date("Y-m-d H:i:s"), $libelleEtape, $numero);

			$etapeTable = new Etape();
			$etapeTable->insert($dataEtape);
			$numero++;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueContacterParents - orphelin, etape contacter parents annulee");
		}
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueContacterParents - exit");
		return $dataEtape;
	}

	private static function prepareEtapeQueteInitiatiqueManger(&$numero, $idQuete, $braldun, $config, $dateDebut)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueManger - enter");

		$libelleEtape = "Vous devez manger un repas";
		$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_MANGER_ID, $dateDebut, $libelleEtape, $numero);

		$dataEtape["param_1_etape"] = 1;
		$dataEtape["param_2_etape"] = self::ETAPE_MANGER_PARAM2_AUBERGE;

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_AUBERGE, $braldun->x_braldun, $braldun->y_braldun);

		if ($lieux == null || count($lieux) < 1) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueManger auberge invalide nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		$auberge = $lieux[0];

		$dataEtape["param_3_etape"] = $auberge["id_lieu"];
		$dataEtape["libelle_etape"] .= " dans l'auberge de " . $auberge["nom_ville"] . " en x:" . $auberge["x_lieu"] . " et y:" . $auberge["y_lieu"];

		$etapeTable = new Etape();
		$etapeTable->insert($dataEtape);
		$numero++;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueManger - exit");
		return $dataEtape;
	}

	private static function prepareEtapeQueteInitiatiqueMetier(&$numero, $idQuete, $braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMetier - enter");

		$libelleEtape = "Vous devez apprendre un métier";
		$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_APPRENDRE_METIER_ID, null, $libelleEtape, $numero);

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_CENTREFORMATION, $braldun->x_braldun, $braldun->y_braldun);

		if ($lieux == null || count($lieux) < 1) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMetier lieu invalide nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		$lieu = $lieux[0];
		$dataEtape["libelle_etape"] .= " au centre de formation de " . $lieu["nom_ville"] . " en x:" . $lieu["x_lieu"] . " et y:" . $lieu["y_lieu"] . ".";
		$dataEtape["libelle_etape"] .= " Vous gagnerez 5 PX à l'accomplissement de cette étape.";

		$etapeTable = new Etape();
		$etapeTable->insert($dataEtape);
		$numero++;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMetier - exit");
		return $dataEtape;
	}

	private static function prepareEtapeQueteInitiatiqueAmeliorerCaracteristique(&$numero, $idQuete, $braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueAmeliorerCaracteristique - enter");

		$libelleEtape = "Vous devez améliorer une caractéristique ";
		$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_AMELIORER_CARACTERISTIQUE_ID, null, $libelleEtape, $numero);

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_ACADEMIE, $braldun->x_braldun, $braldun->y_braldun);

		if ($lieux == null || count($lieux) < 1) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueAmeliorerCaracteristique lieu invalide nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		$lieu = $lieux[0];
		$dataEtape["libelle_etape"] .= " à l'académie de " . $lieu["nom_ville"] . " en x:" . $lieu["x_lieu"] . " et y:" . $lieu["y_lieu"];

		$etapeTable = new Etape();
		$etapeTable->insert($dataEtape);
		$numero++;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueAmeliorerCaracteristique - exit");
		return $dataEtape;
	}

	private static function prepareEtapeQueteInitiatiqueApprendreIdentifier(&$numero, $idQuete, $braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueApprendreIdentifier - enter");

		$libelleEtape = "Vous devez apprendre la compétence Identification des runes ";
		$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_APPRENDRE_IDENTIFICATION_RUNES_ID, null, $libelleEtape, $numero);

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_BIBLIOTHEQUE, $braldun->x_braldun, $braldun->y_braldun);

		if ($lieux == null || count($lieux) < 1) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueApprendreIdentifier lieu invalide nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		$lieu = $lieux[0];
		$dataEtape["libelle_etape"] .= " à la Bibliothèque de " . $lieu["nom_ville"] . " en x:" . $lieu["x_lieu"] . " et y:" . $lieu["y_lieu"];

		$etapeTable = new Etape();
		$etapeTable->insert($dataEtape);
		$numero++;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueApprendreIdentifier - exit");
		return $dataEtape;
	}

	private static function prepareEtapeQueteInitiatiqueMarcherMaison(&$numero, $idQuete, $braldun, $config)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMarcherMaison - enter");

		$libelleEtape = "Vous devez marcher jusqu'à la ";
		$dataEtape = self::getDataEtape($idQuete, $braldun->id_braldun, self::QUETE_ETAPE_MARCHER_ID, null, $libelleEtape, $numero);

		Zend_Loader::loadClass("Bral_Helper_Calendrier");
		$dataEtape["param_1_etape"] = self::ETAPE_MARCHER_PARAM1_RIEN;
		$dataEtape["param_3_etape"] = self::ETAPE_MARCHER_PARAM3_LIEU;

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_QUETE, $braldun->x_braldun, $braldun->y_braldun);

		if ($lieux == null || count($lieux) < 1) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMarcherMaison lieu invalide nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		if ($lieux[0]["id_lieu"] != self::QUETE_ID_LIEU_INITIATIQUE) {
			$lieu = $lieux[0];
		} else if (count($lieux) >= 2 && $lieux[1]["id_lieu"] != self::QUETE_ID_LIEU_INITIATIQUE) {
			$lieu = $lieux[1];
		} else {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMarcherMaison lieu invalide 2 nb:" . count($lieux) . " x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun);
		}

		$dataEtape["libelle_etape"] .= $lieu["nom_lieu"] . ", à " . $lieu["nom_ville"] . ", en x:" . $lieu["x_lieu"] . " et y:" . $lieu["y_lieu"];
		$dataEtape["param_4_etape"] = $lieu["id_lieu"];

		$etapeTable = new Etape();
		$etapeTable->insert($dataEtape);
		$numero++;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::prepareEtapeQueteInitiatiqueMarcherMaison - exit");
		return $dataEtape;
	}

	public static function creationQueteDb($idBraldun, $idLieu)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $idBraldun . " - Bral_Util_Quete::creationQueteDb - enter");
		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();

		$estInitiatique = "non";

		if ($idLieu == self::QUETE_ID_LIEU_INITIATIQUE) {
			$estInitiatique = "oui";
		}

		$data = array(
			"id_fk_lieu_quete" => $idLieu,
			"id_fk_braldun_quete" => $idBraldun,
			"date_creation_quete" => date("Y-m-d H:i:s"),
			"est_initiatique_quete" => $estInitiatique,
		);
		$idQuete = $queteTable->insert($data);

		Bral_Util_Log::quete()->trace("Braldun " . $idBraldun . " - Bral_Util_Quete::creationQueteDb - exit (" . $idQuete . ")");
		return $idQuete;
	}


	private static function estQueteEnCours($braldun)
	{
		if ($braldun->est_quete_braldun == "oui") {
			return true;
		} else {
			return false;
		}
	}

	private static function getEtapeCourante(&$braldun, $idTypeEtape)
	{
		Zend_Loader::loadClass("Etape");
		$etapeTable = new Etape();
		return $etapeTable->findEnCoursByIdBraldunAndIdTypeEtape($braldun->id_braldun, $idTypeEtape);
	}

	private static function activeProchaineEtape(&$braldun)
	{
		$etapeTable = new Etape();
		$etape = $etapeTable->findProchaineEtape($braldun->id_braldun);
		if ($etape) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::activeProchaineEtape - Activation prochaine etape");
			$data = array("date_debut_etape" => date("Y-m-d H:i:s"));
			$where = "id_etape=" . $etape["id_etape"];
			$etapeTable->update($data, $where);
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::activeProchaineEtape - Pas de prochaine etape");
			return false; // fin quete
		}
	}

	private static function termineQuete(&$braldun)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQuete - Fin de la quete - enter");

		$braldun->est_quete_braldun = 'non';
		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();
		$quete = $queteTable->findEnCoursByIdBraldun($braldun->id_braldun);
		if ($quete == null) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQuete nbInvalide:" . $braldun->id_braldun);
		} else {
			$etapeTable = new Etape();
			$nbEtape = $etapeTable->countByIdQuete($quete["id_quete"]);
			$quete["gain_quete"] = self::calculGain($braldun, $nbEtape, $quete);
			$data = array(
				"date_fin_quete" => date("Y-m-d H:i:s"),
				"gain_quete" => $quete["gain_quete"],
			);
			$where = "id_quete=" . $quete["id_quete"];

			$queteTable->update($data, $where);

			self::termineQueteDistinction($quete, $braldun);
		}
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQuete - Fin de la quete - exit");
	}

	private static function termineQueteDistinction($quete, &$braldun)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQueteDistinction - enter");

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findAllLieuQueteAvecRegion();

		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();
		$quetes = $queteTable->findByIdBraldun($braldun->id_braldun);

		$lieuxQuetes = null;
		$idRegionCourante = null;
		$nomRegionCourante = null;

		foreach ($lieux as $l) {
			$lieu = array(
				'date_fin_quete' => null,
			);

			foreach ($quetes as $q) {
				if ($q["id_fk_lieu_quete"] == $l["id_lieu"]) {
					if ($quete["id_quete"] == $q["id_quete"]) {
						$idRegionCourante = $l["id_region"];
						$nomRegionCourante = $l["nom_region"];
						$idTypeDistinctionQueteRegion = $l["id_fk_distinction_quete_region"];
					}
					$lieu["date_fin_quete"] = $q["date_fin_quete"];
				}
			}
			$lieuxQuetes[$l["id_region"]]["lieux"][] = $lieu;
			$lieuxQuetes[$l["id_region"]]["nom"] = $l["nom_region"];
		}

		if ($lieuxQuetes != null && array_key_exists($idRegionCourante, $lieuxQuetes)
			&& array_key_exists("lieux", $lieuxQuetes[$idRegionCourante])
			&& $idRegionCourante != null && $nomRegionCourante != null
		) {
			$terminee = true;
			foreach ($lieuxQuetes[$idRegionCourante]["lieux"] as $l) {
				if ($l["date_fin_quete"] == null) {
					$terminee = false;
					break;
				}
			}

			if ($terminee == true) {
				Zend_Loader::loadClass("Bral_Util_Distinction");
				$texte = "Bourlingueur de la " . $nomRegionCourante;
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQueteDistinction - Ajout d'une distinction : " . $texte);
				Bral_Util_Distinction::ajouterDistinction($braldun->id_braldun, $idTypeDistinctionQueteRegion, $texte, null, $quete);
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQueteDistinction - Pas de distinction à ajouter A");
			}
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQueteDistinction - Pas de distinction à ajouter B");
		}

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::termineQueteDistinction - exit");
	}


	private static function calculGain(&$braldun, $nbEtape, $quete)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGain - enter");

		$idCoffre = self::getIdCoffre($braldun);

		if ($quete["est_initiatique_quete"] == "oui") {
			return self::calculGainQueteInitiatique($braldun, $idCoffre);
		} else {
			return self::calculGainStandard($braldun, $idCoffre, $nbEtape);
		}
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGain - exit");
	}

	private static function getIdCoffre($braldun)
	{
		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();

		$coffre = $coffreTable->findByIdBraldun($braldun->id_braldun);
		if ($coffre == null || count($coffre) != 1) {
			throw new Zend_Eception("Erreur calculGainQueteInitiatiqueAliment idb:" . $braldun->id_braldun);
		}

		$idCoffre = $coffre[0]["id_coffre"];

		return $idCoffre;
	}

	private static function calculGainQueteInitiatique(&$braldun, $idCoffre)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatique - enter");
		$retour = self::calculGainQueteInitiatiqueAliment($braldun, $idCoffre);
		$retour .= self::calculGainQueteInitiatiqueTabac($braldun);

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatique - enter");
		return $retour;
	}

	private static function calculGainQueteInitiatiqueAliment(&$braldun, $idCoffre)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatiqueAliment - enter");
		$nbRagouts = 5;

		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);

		$qualiteAliment = 3; // qualite bonne
		$bbdfAliment = 100;

		Zend_Loader::loadClass("ElementAliment");
		Zend_Loader::loadClass("CoffreAliment");

		$elementAlimentTable = new ElementAliment();
		$coffreAlimentTable = new CoffreAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		for ($i = 1; $i <= $nbRagouts; $i++) {
			$id_aliment = $idsAliment->prepareNext();

			$data = array(
				"id_aliment" => $id_aliment,
				"id_fk_type_aliment" => TypeAliment::ID_TYPE_RAGOUT,
				"id_fk_type_qualite_aliment" => $qualiteAliment,
				"bbdf_aliment" => $bbdfAliment,
			);
			$alimentTable->insert($data);

			$data = array(
				'id_coffre_aliment' => $id_aliment,
				'id_fk_coffre_coffre_aliment' => $idCoffre,
			);
			$coffreAlimentTable->insert($data);
		}

		$retour = " " . $nbRagouts . " ragoûts (dans votre coffre) " . PHP_EOL;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatiqueAliment - exit");
		return $retour;
	}

	private static function calculGainQueteInitiatiqueTabac(&$braldun)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatiqueTabac - enter");
		$nbFeuilles = 5;

		// on recupere les competences du metier courant
		Zend_Loader::loadClass("BraldunsCompetences");
		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldunAndMetierCourant($braldun->id_braldun);

		$idTypeTabac = 1;
		if ($braldunCompetences == null || count($braldunCompetences) == 0) {
			Bral_Util_Log::quete()->err("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatiqueTabac - impossible de trouver le metier par defaut avec les competences associees. Erreur, idTypeTabacParDefaut:1, idH:" . $braldun->id_braldun);
		} else {
			$idTypeTabac = $braldunCompetences[0]["id_fk_type_tabac_competence"];
		}

		$data = array(
			"quantite_feuille_laban_tabac" => $nbFeuilles,
			"id_fk_type_laban_tabac" => $idTypeTabac,
			"id_fk_braldun_laban_tabac" => $braldun->id_braldun,
		);

		Zend_Loader::loadClass("LabanTabac");
		$labanTabacTable = new LabanTabac();
		$labanTabacTable->insertOrUpdate($data);

		$retour = " " . $nbFeuilles . " feuilles de tabac (dans votre laban) " . PHP_EOL;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainQueteInitiatiqueTabac - exit");
		return $retour;
	}

	private static function calculGainStandard(&$braldun, $idCoffre, $nbEtape)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainStandard - enter");

		$nbRecompenses = $nbEtape - Bral_Util_De::get_1d3();
		if ($nbRecompenses < 1) {
			$nbRecompenses = 1;
		}

		$retour = "";

		$liste = array();
		for ($i = 1; $i <= $nbRecompenses; $i++) {
			$n = Bral_Util_De::get_de_specifique_hors_liste(1, 5, $liste);
			$liste[] = $n;

			if ($n == 1) {
				$retour .= self::calculGainRune($braldun, $idCoffre);
			} elseif ($n == 2) {
				$retour .= self::calculGainExperience($braldun, $nbRecompenses, $nbEtape);
			} elseif ($n == 3) {
				$retour .= self::calculGainCastars($braldun, $idCoffre, $nbRecompenses, $nbEtape);
			} elseif ($n == 4) {
				$retour .= self::calculGainMinerais($braldun, $idCoffre, $nbRecompenses, $nbEtape);
			} elseif ($n == 5) {
				$retour .= self::calculGainPlantes($braldun, $idCoffre, $nbRecompenses, $nbEtape);
			}
		}

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainStandard - exit:" . $retour);
		return $retour;
	}

	private static function calculGainRune(&$braldun, $idCoffre)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainRune - enter");
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
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainRune niveauRune:" . $niveauRune);
		}

		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType - 1);

		$typeRune = $typeRuneRowset[$numeroRune];

		Zend_Loader::loadClass("IdsRune");
		$idsRuneTable = new IdsRune();
		$idRune = $idsRuneTable->prepareNext();

		Zend_Loader::loadClass("Rune");
		$runeTable = new Rune();
		$dataRune = array(
			"id_rune" => $idRune,
			"id_fk_type_rune" => $typeRune["id_type_rune"],
			"est_identifiee_rune" => "oui",
		);
		$runeTable->insert($dataRune);

		$coffreRuneTable = new CoffreRune();
		$data = array(
			"id_rune_coffre_rune" => $idRune,
			"id_fk_coffre_coffre_rune" => $idCoffre,
		);
		$coffreRuneTable->insert($data);

		$details = "[b" . $braldun->id_braldun . "] a reçu la rune n°" . $idRune . " en récompense de quête";
		Zend_Loader::loadClass("Bral_Util_Rune");
		Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_CREATION_ID, $idRune, $details);

		$retour = " une rune de type " . $typeRune["nom_type_rune"] . PHP_EOL;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainRune - exit");
		return $retour;
	}

	private static function calculGainExperience(&$braldun, $nbRecompenses, $nbEtape)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainExperience - enter");
		$nbPx = floor((($braldun->niveau_braldun / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_de_specifique(1, $braldun->niveau_braldun));
		if ($nbPx < 1) {
			$nbPx = 1;
		}

		$braldun->px_perso_braldun = $braldun->px_perso_braldun + $nbPx;
		$retour = " " . $nbPx . " PX " . PHP_EOL;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainExperience - exit");
		return $retour;
	}

	private static function calculGainCastars(&$braldun, $idCoffre, $nbRecompenses, $nbEtape)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainCastars - enter");
		$nbCastars = floor((($braldun->niveau_braldun / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_de_specifique(1, $braldun->niveau_braldun)) * 10;
		if ($nbCastars < 2) {
			$nbCastars = 2;
		}

		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();
		$data = array(
			"quantite_castar_coffre" => $nbCastars,
			"id_coffre" => $idCoffre,
		);
		$coffreTable->insertOrUpdate($data);

		$retour = " " . $nbCastars . " castars (dans votre coffre) " . PHP_EOL;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainCastars - exit");
		return $retour;
	}

	private static function calculGainMinerais(&$braldun, $idCoffre, $nbRecompenses, $nbEtape)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainMinerais - enter");
		$nbMinerais = floor((($braldun->niveau_braldun / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_1d6());
		if ($nbMinerais < 2) {
			$nbMinerais = 2;
		}

		$typeMinerai = "todo";
		Zend_Loader::loadClass("TypeMinerai");
		$typeMineraiTable = new TypeMinerai();
		$types = $typeMineraiTable->fetchAll();

		$n = Bral_Util_De::get_de_specifique(1, count($types));
		$typeMinerai = $types[$n - 1];

		$data = array(
			"id_fk_coffre_coffre_minerai" => $idCoffre,
			"id_fk_type_coffre_minerai" => $typeMinerai["id_type_minerai"],
			"quantite_brut_coffre_minerai" => $nbMinerais,
		);

		Zend_Loader::loadClass("CoffreMinerai");
		$coffreMineraiTable = new CoffreMinerai();
		$coffreMineraiTable->insertOrUpdate($data);

		$retour = " " . $nbMinerais . " minerais bruts de type " . $typeMinerai["nom_type_minerai"] . " (dans votre coffre) " . PHP_EOL;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainMinerais - exit");
		return $retour;
	}

	private static function calculGainPlantes(&$braldun, $idCoffre, $nbRecompenses, $nbEtape)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainPlantes - enter");
		$retour = "";

		$nbPartiesPlantes = floor((($braldun->niveau_braldun / $nbRecompenses) * $nbEtape) + Bral_Util_De::get_1d6());
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

		$nbUnitaireGain = ceil($nbPartiesPlantes / 3);
		$retour .= self::calculGainPlantesDb($braldun, $idCoffre, $plantes, $tirage1, $nbUnitaireGain);
		$retour .= self::calculGainPlantesDb($braldun, $idCoffre, $plantes, $tirage2, $nbUnitaireGain);
		$retour .= self::calculGainPlantesDb($braldun, $idCoffre, $plantes, $tirage3, $nbUnitaireGain);

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainPlantes - exit");
		return $retour;
	}

	private static function calculGainPlantesDb(&$braldun, $idCoffre, $plantes, $tirage, $nbUnitaireGain)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainPlantesDb - enter");
		$coffrePartieplanteTable = new CoffrePartieplante();
		$data = array(
			"id_fk_coffre_coffre_partieplante" => $idCoffre,
			"id_fk_type_coffre_partieplante" => $plantes[$tirage]["id_type_partieplante"],
			"id_fk_type_plante_coffre_partieplante" => $plantes[$tirage]["id_type_plante"],
			"quantite_coffre_partieplante" => $nbUnitaireGain,
		);
		$coffrePartieplanteTable->insertOrUpdate($data);

		$s = "";
		if ($nbUnitaireGain > 1) {
			$s = "s";
		}
		$texte = "  " . $nbUnitaireGain . " " . $plantes[$tirage]["nom_type_partieplante"] . "$s de " . $plantes[$tirage]["nom_type_plante"] . PHP_EOL;

		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculGainPlantesDb - exit");
		return $texte;
	}

	public static function etapeTuer(&$braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeTuer - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_TUER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeTuer - pas d'etape tuer en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeTuer - etape tuer en cours");
				return self::calculEtapeTuer($etape, $braldun, $tailleMonstre, $typeMonstre, $niveauMonstre);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeTuer($etape, &$braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
	{
		if (self::calculEtapeTuerParam1($etape, $braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
			&& self::calculEtapeTuerParam3($etape, $braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeTuer - conditions remplies, calcul fin etape");
			self::calculEtapeTuerFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeTuerParam1($etape, &$braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - param1:" . $etape["param_1_etape"] . " param2:" . $etape["param_2_etape"]);
		if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_NOMBRE) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - A");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_JOUR && $etape["param_2_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - B");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_ETAT) {
			if ($etape["param_2_etape"] == self::ETAPE_TUER_PARAM2_ETAT_AFFAME && $braldun->balance_faim_braldun < 1) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - C");
				$retour = true;
			} elseif ($etape["param_2_etape"] == self::ETAPE_TUER_PARAM2_ETAT_REPU && $braldun->balance_faim_braldun >= 95) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - D");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 - E");
		}
		return $retour;
	}

	private static function calculEtapeTuerParam3($etape, &$braldun, $tailleMonstre, $typeMonstre, $niveauMonstre)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam3 - param3:" . $etape["param_3_etape"] . " param4:" . $etape["param_4_etape"] . " taille:" . $tailleMonstre . " type:" . $typeMonstre . " niv:" . $niveauMonstre);
		if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_TAILLE && $etape["param_4_etape"] == $tailleMonstre) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam3 - A");
			return true;
		} else if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_TYPE && $etape["param_4_etape"] == $typeMonstre) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam3 - B");
			return true;
		} else if ($etape["param_3_etape"] == self::ETAPE_TUER_PARAM3_NIVEAU && $etape["param_4_etape"] == $niveauMonstre) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam3 - C");
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam3 - D");
			return false;
		}
		return $retour;
	}

	private static function calculEtapeTuerFin($etape, &$braldun)
	{
		$etapeTable = new Etape();
		$estFinEtape = false;
		if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_NOMBRE) {
			$data = array("objectif_etape" => $etape["objectif_etape"] + 1);
			if ($etape["objectif_etape"] + 1 >= $etape["param_2_etape"]) {
				$data = array("objectif_etape" => $etape["objectif_etape"] + 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerFin - Fin Ok 1");
				$estFinEtape = true;
			}
		} else if ($etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_JOUR ||
			$etape["param_1_etape"] == self::ETAPE_TUER_PARAM1_ETAT
		) {
			$data = array("objectif_etape" => 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerFin - Fin Ok 2");
			$estFinEtape = true;
		} else {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeTuerParam1 param1 invalide:" . $etape["param_1_etape"]);
		}
		$where = "id_etape = " . $etape["id_etape"];
		$etapeTable->update($data, $where);
		if ($estFinEtape) {
			if (self::activeProchaineEtape($braldun) == false) { // fin quete
				self::termineQuete($braldun);
			}
		}
	}

	public static function etapeManger(&$braldun, $estDansLieu)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeManger - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_MANGER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeManger - pas d'etape manger en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeManger - etape manger en cours");
				return self::calculEtapeManger($etape, $braldun, $estDansLieu);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeManger($etape, &$braldun, $estDansLieu)
	{
		if (self::calculEtapeMangerParam3($etape, $braldun, $estDansLieu)
			&& self::calculEtapeMangerParam4($etape, $braldun)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeManger::conditions remplies, calcul fin etape");
			self::calculEtapeMangerFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeMangerParam3($etape, &$braldun, $estDansLieu)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - param2:" . $etape["param_2_etape"] . " param3:" . $etape["param_3_etape"]);
		if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_AUBERGE && $estDansLieu) {
			Zend_Loader::loadClass("Lieu");
			$lieuxTable = new Lieu();
			$lieuRowset = $lieuxTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
			if ($lieuRowset != null && count($lieuRowset) == 1 && $lieuRowset[0]["id_lieu"] == $etape["param_3_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - A - sur le lieu");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - A - non sur le lieu");
			}
		} else if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_TERRAIN && $estDansLieu == false) {
			Zend_Loader::loadClass("Zone");
			$zoneTable = new Zone();
			$zones = $zoneTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

			Zend_Loader::loadClass("Bosquet");
			$bosquetTable = new Bosquet();
			$nombreBosquets = $bosquetTable->countByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

			if ($zones != null && count($zones) == 1 &&
				($zones[0]["id_environnement"] == $etape["param_3_etape"]
					|| ($etape["param_3_etape"] == 2 && $nombreBosquets >= 1))
			) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - B - sur l'environnement");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - B - non sur l'environnement");
			}
		} else if ($etape["param_2_etape"] == self::ETAPE_MANGER_PARAM2_ETAT && $estDansLieu == false) {
			if ($etape["param_3_etape"] == self::ETAPE_MANGER_PARAM3_ETAT_AFFAME && $braldun->balance_faim_braldun < 1) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - C");
				$retour = true;
			} elseif ($etape["param_3_etape"] == self::ETAPE_MANGER_PARAM3_ETAT_REPU && $braldun->balance_faim_braldun >= 95) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - D");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam3 - E");
		}
		return $retour;
	}

	private static function calculEtapeMangerParam4($etape, &$braldun)
	{
		if ($etape["param_4_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam4 - A");
			return true;
		} else if ($etape["param_4_etape"] == null) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam4 - B (quete initiatique)");
			return true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMangerParam4 - C");
			return false;
		}
	}

	private static function calculEtapeMangerFin($etape, &$braldun)
	{
		$finEtape = self::calculEtapeFinStandardNbObjectif($etape, $braldun, "param_1_etape");
		if ($finEtape) {
			// on regarde s'il n'a pas deja acheté un metier
			Zend_Loader::loadClass("BraldunsMetiers");
			$braldunsMetiersTable = new BraldunsMetiers();
			$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($braldun->id_braldun);
			if ($braldunsMetierRowset != null && count($braldunsMetierRowset) > 0) {
				self::etapeApprendreMetier($braldun);
			}
		}
	}

	public static function etapeFumer(&$braldun, $idTypeTabac)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFumer - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_FUMER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFumer - pas d'etape fumer en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFumer - etape fumer en cours");
				return self::calculEtapeFumer($etape, $braldun, $idTypeTabac);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeFumer($etape, &$braldun, $idTypeTabac)
	{
		if (self::calculEtapeFumerParam1($etape, $braldun, $idTypeTabac)
			&& self::calculEtapeFumerParam2et3($etape, $braldun)
			&& self::calculEtapeFumerParam4et5($etape, $braldun)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumer - conditions remplies, calcul fin etape");
			self::calculEtapeFumerFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeFumerParam1($etape, &$braldun, $idTypeTabac)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam1 - param1:" . $etape["param_1_etape"]);
		if ($etape["param_1_etape"] == $idTypeTabac) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam1 - A");
			$retour = true;
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam1 - B");
		}
		return $retour;
	}

	private static function calculEtapeFumerParam2et3($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - param2:" . $etape["param_2_etape"] . " param3:" . $etape["param_3_etape"]);
		if ($etape["param_2_etape"] == self::ETAPE_FUMER_PARAM2_JOUR && $etape["param_3_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - A");
			$retour = true;
		} else if ($etape["param_2_etape"] == self::ETAPE_FUMER_PARAM2_ETAT) {
			if ($etape["param_3_etape"] == self::ETAPE_FUMER_PARAM3_ETAT_AFFAME && $braldun->balance_faim_braldun < 1) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - B");
				$retour = true;
			} elseif ($etape["param_3_etape"] == self::ETAPE_FUMER_PARAM3_ETAT_REPU && $braldun->balance_faim_braldun >= 95) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - D");
			}
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam2et3 - E");
		}
		return $retour;
	}

	private static function calculEtapeFumerParam4et5($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - param4:" . $etape["param_4_etape"] . " param5:" . $etape["param_5_etape"]);
		if ($etape["param_4_etape"] == self::ETAPE_FUMER_PARAM4_TERRAIN) {
			Zend_Loader::loadClass("Zone");
			$zoneTable = new Zone();
			$zones = $zoneTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

			if ($etape["param_5_etape"] == 2) { // anciennement foret
				Zend_Loader::loadClass("Bosquet");
				$bosquetTable = new Bosquet();
				$nombreBosquets = $bosquetTable->countByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
			}

			if ($zones != null && count($zones) == 1 &&
				($zones[0]["id_environnement"] == $etape["param_5_etape"]
					|| ($etape["param_5_etape"] == 2 && $nombreBosquets >= 1))
			) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - A - sur l'environnement");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - A - non sur l'environnement");
			}
		} else if ($etape["param_4_etape"] == self::ETAPE_FUMER_PARAM4_VILLE) {
			Zend_Loader::loadClass("Ville");
			$villeTable = new Ville();
			$villes = $villeTable->findByCase($braldun->x_braldun, $braldun->y_braldun);
			if ($villes != null && count($villes) == 1 && $villes[0]["id_ville"] == $etape["param_5_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - B - sur la ville");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - B - non sur la ville");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFumerParam4et5 - C");
		}
		return $retour;
	}


	private static function calculEtapeFumerFin($etape, &$braldun)
	{
		self::calculEtapeFinStandard($etape, $braldun);
	}

	public static function etapeMarcher(&$braldun)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeMarcher - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_MARCHER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeMarcher - pas d'etape marcher en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeMarcher - etape marcher en cours");
				return self::calculEtapeMarcher($etape, $braldun);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeMarcher($etape, &$braldun)
	{
		if (self::calculEtapeMarcherParam1et2($etape, $braldun)
			&& self::calculEtapeMarcherParam3et4et5($etape, $braldun)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcher::conditions remplies, calcul fin etape");
			self::calculEtapeMarcherFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeMarcherParam1et2($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - param1:" . $etape["param_1_etape"] . " param2:" . $etape["param_2_etape"]);
		if ($etape["param_1_etape"] == self::ETAPE_MARCHER_PARAM1_JOUR && $etape["param_2_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - A");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_MARCHER_PARAM1_ETAT) {
			if ($etape["param_2_etape"] == self::ETAPE_MARCHER_PARAM2_ETAT_AFFAME && $braldun->balance_faim_braldun < 1) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - B");
				$retour = true;
			} elseif ($etape["param_2_etape"] == self::ETAPE_MARCHER_PARAM2_ETAT_REPU && $braldun->balance_faim_braldun >= 95) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - C");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - D");
			}
		} else if ($etape["param_1_etape"] == self::ETAPE_MARCHER_PARAM1_RIEN) {
			$retour = true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam1et2 - E");
		}
		return $retour;
	}

	private static function calculEtapeMarcherParam3et4et5($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - param3:" . $etape["param_3_etape"] . " param4:" . $etape["param_4_etape"] . " param5:" . $etape["param_5_etape"]);
		if ($etape["param_3_etape"] == self::ETAPE_MARCHER_PARAM3_TERRAIN) {
			Zend_Loader::loadClass("Zone");
			$zoneTable = new Zone();
			$zones = $zoneTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

			if ($etape["param_4_etape"] == 2) { // anciennement foret
				Zend_Loader::loadClass("Bosquet");
				$bosquetTable = new Bosquet();
				$nombreBosquets = $bosquetTable->countByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
			}

			if ($zones != null && count($zones) == 1 &&
				($zones[0]["id_environnement"] == $etape["param_4_etape"]
					|| ($etape["param_4_etape"] == 2 && $nombreBosquets >= 1))
			) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - A - sur l'environnement");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - A - non sur l'environnement");
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_MARCHER_PARAM3_LIEU) {
			Zend_Loader::loadClass("Lieu");
			$lieuxTable = new Lieu();
			$lieuRowset = $lieuxTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
			if ($lieuRowset != null && count($lieuRowset) == 1 && $lieuRowset[0]["id_lieu"] == $etape["param_4_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - B - sur le lieu");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - B - non sur le lieu");
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_MARCHER_PARAM3_POSITION) {
			if ($braldun->x_braldun == $etape["param_4_etape"] && $braldun->y_braldun == $etape["param_5_etape"]) {
				$retour = true;
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - C - sur x y");
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - C - non sur x y");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeMarcherParam3et4et5 - C");
		}
		return $retour;
	}

	private static function calculEtapeMarcherFin($etape, &$braldun)
	{
		self::calculEtapeFinStandard($etape, $braldun);
	}

	private static function calculEtapeFinStandard($etape, &$braldun)
	{
		$etapeTable = new Etape();
		$data = array("objectif_etape" => $etape["objectif_etape"] + 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFinStandard - Fin Ok");
		$where = "id_etape = " . $etape["id_etape"];
		$etapeTable->update($data, $where);
		if (self::activeProchaineEtape($braldun) == false) { // fin quete
			self::termineQuete($braldun);
		}
	}

	public static function etapePosseder(&$braldun)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapePosseder - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_POSSEDER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapePosseder - pas d'etape posseder en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapePosseder - etape posseder en cours");
				return self::calculEtapePosseder($etape, $braldun);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapePosseder($etape, &$braldun)
	{
		if (self::calculEtapePossederParams($etape, $braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePosseder::conditions remplies, calcul fin etape");
			self::calculEtapePossederFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapePossederParams($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParams - param1:" . $etape["param_1_etape"]);
		if ($etape["param_2_etape"] == self::ETAPE_POSSEDER_PARAM2_COFFRE) {
			$retour = self::calculEtapePossederParamsCoffre($etape, $braldun);
		} else if ($etape["param_2_etape"] == self::ETAPE_POSSEDER_PARAM2_LABAN) {
			$retour = self::calculEtapePossederParamsLaban($etape, $braldun);
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParams - C");
		}
		return $retour;
	}

	private static function calculEtapePossederParamsCoffre($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParamsCoffre - param3:" . $etape["param_3_etape"]);

		if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_MINERAI ||
			$etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PLANTE ||
			$etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PEAU ||
			$etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_FOURRURE ||
			$etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_CASTAR
		) {
			Zend_Loader::loadClass("Coffre");
			$coffreTable = new Coffre();

			$coffre = $coffreTable->findByIdBraldun($braldun->id_braldun);
			if ($coffre == null || count($coffre) != 1) {
				throw new Zend_Eception("Erreur calculEtapePossederParamsCoffre idb:" . $braldun->id_braldun);
			}

			$idCoffre = $coffre[0]["id_coffre"];
		}

		if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_MINERAI) {
			Zend_Loader::loadClass("CoffreMinerai");
			$coffreMineraiTable = new CoffreMinerai();
			$idCoffre = self::getIdCoffre($braldun);
			$coffreMinerai = $coffreMineraiTable->findByIdCoffre($idCoffre);
			if ($coffreMinerai != null && count($coffreMinerai) >= 1) {
				foreach ($coffreMinerai as $l) {
					if ($l["id_fk_type_coffre_minerai"] == $etape["param_4_etape"]) {
						if ($l["quantite_brut_coffre_minerai"] >= $etape["param_1_etape"]) {
							$data = array(
								"id_fk_coffre_coffre_minerai" => $idCoffre,
								"id_fk_type_coffre_minerai" => $l["id_fk_type_coffre_minerai"],
								"quantite_brut_coffre_minerai" => -$etape["param_1_etape"],
							);
							$coffreMineraiTable->insertOrUpdate($data);
							$retour = true;
						}
						break;
					}
				}
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PLANTE) {
			Zend_Loader::loadClass("CoffrePartieplante");
			$coffrePartieplanteTable = new CoffrePartieplante();
			$idCoffre = self::getIdCoffre($braldun);
			$coffrePartieplante = $coffrePartieplanteTable->findByIdCoffre($idCoffre);
			if ($coffrePartieplante != null && count($coffrePartieplante) >= 1) {
				foreach ($coffrePartieplante as $p) {
					if ($p["id_fk_type_plante_coffre_partieplante"] == $etape["param_4_etape"] && $p["id_fk_type_coffre_partieplante"] == $etape["param_5_etape"]) {
						if ($p["quantite_coffre_partieplante"] >= $etape["param_1_etape"]) {
							Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParamsCoffre - B");
							$data = array(
								"id_fk_coffre_coffre_partieplante" => $idCoffre,
								"id_fk_type_coffre_partieplante" => $p["id_fk_type_coffre_partieplante"],
								"id_fk_type_plante_coffre_partieplante" => $p["id_fk_type_plante_coffre_partieplante"],
								"quantite_coffre_partieplante" => -$etape["param_1_etape"],
							);
							$coffrePartieplanteTable->insertOrUpdate($data);
							$retour = true;
						}
						break;
					}
				}
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PEAU || $etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_FOURRURE || $etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_CASTAR) {
			Zend_Loader::loadClass("Coffre");
			$coffreTable = new Coffre();
			$coffreRowset = $coffreTable->findByIdBraldun($braldun->id_braldun);
			if ($coffreRowset != null && count($coffreRowset) == 1) {
				$coffre = $coffreRowset[0];
				if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PEAU && $coffre["quantite_peau_coffre"] >= $etape["param_1_etape"]) {
					$data = array(
						"quantite_peau_coffre" => -$etape["param_1_etape"],
						"id_coffre" => $idCoffre,
					);
					$coffreTable->insertOrUpdate($data);
					$retour = true;
				} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_FOURRURE && $coffre["quantite_fourrure_coffre"] >= $etape["param_1_etape"]) {
					$data = array(
						"quantite_fourrure_coffre" => -$etape["param_1_etape"],
						"id_coffre" => $idCoffre,
					);
					$coffreTable->insertOrUpdate($data);
					$retour = true;
				} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_CASTAR && $coffre["quantite_castar_coffre"] >= $etape["param_1_etape"]) {
					$data = array(
						"quantite_castar_coffre" => -$etape["param_1_etape"],
						"id_coffre" => $idCoffre,
					);
					$coffreTable->insertOrUpdate($data);
					$retour = true;
				}
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParamsCoffre - exit");
		}
		return $retour;
	}

	private static function calculEtapePossederParamsLaban($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParamsCoffre - param3:" . $etape["param_3_etape"]);

		if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_MINERAI) {
			Zend_Loader::loadClass("LabanMinerai");
			$labanMineraiTable = new LabanMinerai();
			$labanMinerai = $labanMineraiTable->findByIdBraldun($braldun->id_braldun);
			if ($labanMinerai != null && count($labanMinerai) >= 1) {
				foreach ($labanMinerai as $l) {
					if ($l["id_fk_type_laban_minerai"] == $etape["param_4_etape"]) {
						if ($l["quantite_brut_laban_minerai"] >= $etape["param_1_etape"]) {
							$data = array(
								"quantite_brut_laban_minerai" => -$etape["param_1_etape"],
								"id_fk_type_laban_minerai" => $l["id_fk_type_laban_minerai"],
								"id_fk_braldun_laban_minerai" => $braldun->id_braldun,
							);
							$labanMineraiTable->insertOrUpdate($data);
							$retour = true;
						}
						break;
					}
				}
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PLANTE) {
			Zend_Loader::loadClass("LabanPartieplante");
			$labanPartieplanteTable = new LabanPartieplante();
			$labanPartieplante = $labanPartieplanteTable->findByIdBraldun($braldun->id_braldun);
			if ($labanPartieplante != null && count($labanPartieplante) >= 1) {
				foreach ($labanPartieplante as $p) {
					if ($p["id_fk_type_plante_laban_partieplante"] == $etape["param_4_etape"] && $p["id_fk_type_laban_partieplante"] == $etape["param_5_etape"]) {
						if ($p["quantite_laban_partieplante"] >= $etape["param_1_etape"]) {
							$data = array(
								"quantite_laban_partieplante" => -$etape["param_1_etape"],
								"id_fk_type_laban_partieplante" => $p["id_fk_type_laban_partieplante"],
								"id_fk_type_plante_laban_partieplante" => $p["id_fk_type_plante_laban_partieplante"],
								"id_fk_braldun_laban_partieplante" => $braldun->id_braldun,
							);
							$labanPartieplanteTable->insertOrUpdate($data);
							$retour = true;
						}
						break;
					}
				}
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PEAU || $etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_FOURRURE) {
			Zend_Loader::loadClass("Laban");
			$labanTable = new Laban();
			$labanRowset = $labanTable->findByIdBraldun($braldun->id_braldun);
			if ($labanRowset != null && count($labanRowset) == 1) {
				$laban = $labanRowset[0];
				if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_PEAU && $laban["quantite_peau_laban"] >= $etape["param_1_etape"]) {
					$data = array(
						"quantite_peau_laban" => -$etape["param_1_etape"],
						"id_fk_braldun_laban" => $braldun->id_braldun,
					);
					$labanTable->insertOrUpdate($data);
					$retour = true;
				} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_FOURRURE && $laban["quantite_fourrure_laban"] >= $etape["param_1_etape"]) {
					$data = array(
						"quantite_fourrure_laban" => -$etape["param_1_etape"],
						"id_fk_braldun_laban" => $braldun->id_braldun,
					);
					$labanTable->insertOrUpdate($data);
					$retour = true;
				}
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_POSSEDER_PARAM3_CASTAR) {
			if ($braldun->castars_braldun >= $etape["param_1_etape"]) {
				$braldun->castars_braldun = $braldun->castars_braldun - $etape["param_1_etape"];
				$retour = true;
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapePossederParamsLaban - exit");
		}
		return $retour;
	}

	private static function calculEtapePossederFin($etape, &$braldun)
	{
		self::calculEtapeFinStandard($etape, $braldun);
	}

	public static function etapeEquiper(&$braldun, $idTypeEmplacement)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeEquiper - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_EQUIPER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeEquiper - pas d'etape equiper en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeEquiper - etape equiper en cours");
				return self::calculEtapeEquiper($etape, $braldun, $idTypeEmplacement);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeEquiper($etape, &$braldun, $idTypeEmplacement)
	{
		if (self::calculEtapeEquiperParam1($etape, $braldun, $idTypeEmplacement)
			&& self::calculEtapeEquiperParam2et3($etape, $braldun)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiper::conditions remplies, calcul fin etape");
			self::calculEtapeEquiperFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeEquiperParam1($etape, &$braldun, $idTypeEmplacement)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam1 - param1:" . $etape["param_1_etape"] . " idTypeEmplacement:" . $idTypeEmplacement);
		if ($etape["param_1_etape"] == $idTypeEmplacement) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam1 - Ok");
			$retour = true;
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam1 - non Ok");
		}
		return $retour;
	}

	private static function calculEtapeEquiperParam2et3($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam2et3 - param2:" . $etape["param_2_etape"] . " param3:" . $etape["param_3_etape"]);
		if ($etape["param_2_etape"] == self::ETAPE_EQUIPER_PARAM2_JOUR && $etape["param_3_etape"] == date('N')) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam2et3 - B - jour ok");
			$retour = true;
		} else if ($etape["param_2_etape"] == self::ETAPE_EQUIPER_PARAM2_VILLE) {
			Zend_Loader::loadClass("Ville");
			$villeTable = new Ville();
			$villes = $villeTable->findByCase($braldun->x_braldun, $braldun->y_braldun);
			if ($villes != null && count($villes) == 1 && $villes[0]["id_ville"] == $etape["param_3_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam2et3 - B - sur la ville");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam2et3 - B - non sur la ville");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeEquiperParam2et3 - C");
		}
		return $retour;
	}

	private static function calculEtapeEquiperFin($etape, &$braldun)
	{
		self::calculEtapeFinStandard($etape, $braldun);
	}

	public static function etapeConstuire(&$braldun, $nomSystemeCompetence)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeConstuire - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_CONSTRUIRE_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeConstuire - pas d'etape construire en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeConstuire - etape construire en cours");
				return self::calculEtapeConstruire($etape, $braldun, $nomSystemeCompetence);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeConstruire($etape, &$braldun, $nomSystemeCompetence)
	{
		if (self::calculEtapeConstruireParam1($etape, $braldun, $nomSystemeCompetence)
			&& self::calculEtapeConstruireParam3et4($etape, $braldun)
		) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruire::conditions remplies, calcul fin etape");
			self::calculEtapeConstuireFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeConstruireParam1($etape, &$braldun, $nomSystemeCompetence)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam1et2 - param1:" . $etape["param_1_etape"] . " param2:" . $etape["param_2_etape"]);

		Zend_Loader::loadClass("Bral_Util_Metier");
		$idMetierCourant = Bral_Util_Metier::getIdMetierCourant($braldun);

		if ($etape["param_1_etape"] == self::ETAPE_CONSTRUIRE_PARAM1_TERRASSIER && $idMetierCourant == self::ETAPE_CONSTRUIRE_PARAM1_TERRASSIER && $nomSystemeCompetence == self::ETAPE_CONSTUIRE_COMPETENCE_CONSTUIRE) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam1 - A");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_CONSTRUIRE_PARAM1_CUISINIER && $idMetierCourant == self::ETAPE_CONSTRUIRE_PARAM1_CUISINIER && $nomSystemeCompetence == self::ETAPE_CONSTUIRE_COMPETENCE_CUISINER) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam1 - B");
			$retour = true;
		} else if ($etape["param_1_etape"] == self::ETAPE_CONSTRUIRE_PARAM1_BUCHERON && $idMetierCourant == self::ETAPE_CONSTRUIRE_PARAM1_BUCHERON && $nomSystemeCompetence == self::ETAPE_CONSTUIRE_COMPETENCE_MONTERPALISSADE) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam1 - C");
			$retour = true;
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam1 - C");
		}
		return $retour;
	}

	private static function calculEtapeConstruireParam3et4($etape, &$braldun)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - param3:" . $etape["param_3_etape"] . " param4:" . $etape["param_4_etape"]);
		if ($etape["param_3_etape"] == self::ETAPE_CONSTRUIRE_PARAM3_TERRAIN) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - B - jour ok");
			Zend_Loader::loadClass("Zone");
			$zoneTable = new Zone();
			$zones = $zoneTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
			if ($zones != null && count($zones) == 1 && $zones[0]["id_environnement"] == $etape["param_4_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - B - sur l'environnement");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - B - non sur l'environnement");
			}
		} else if ($etape["param_3_etape"] == self::ETAPE_CONSTRUIRE_PARAM3_VILLE) {
			Zend_Loader::loadClass("Ville");
			$villeTable = new Ville();
			$villes = $villeTable->findByCase($braldun->x_braldun, $braldun->y_braldun);
			if ($villes != null && count($villes) == 1 && $villes[0]["id_ville"] == $etape["param_4_etape"]) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - B - sur la ville");
				$retour = true;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - B - non sur la ville");
			}
		} else {
			$retour = false;
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruireParam3et4 - C");
		}
		return $retour;
	}

	private static function calculEtapeConstuireFin($etape, &$braldun)
	{
		self::calculEtapeFinStandardNbObjectif($etape, $braldun, "param_2_etape");
	}

	private static function calculEtapeFinStandardNbObjectif($etape, &$braldun, $champNombreAFaire)
	{
		$etapeTable = new Etape();

		$estFinEtape = false;

		$data = array("objectif_etape" => $etape["objectif_etape"] + 1);
		if ($etape["objectif_etape"] + 1 >= $etape[$champNombreAFaire]) {
			$data = array("objectif_etape" => $etape["objectif_etape"] + 1, "est_terminee_etape" => "oui", "date_fin_etape" => date("Y-m-d H:i:s"));
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFinStandardNbObjectif - Fin Ok");
			$estFinEtape = true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFinStandardNbObjectif - Fin pas encore Ok:" . $etape[$champNombreAFaire] . " / " . ($etape["objectif_etape"] + 1));
		}

		$where = "id_etape = " . $etape["id_etape"];
		$etapeTable->update($data, $where);
		if ($estFinEtape) {
			if (self::activeProchaineEtape($braldun) == false) { // fin quete
				self::termineQuete($braldun);
			}
		}

		return $estFinEtape;
	}

	public static function etapeFabriquer(&$braldun, $idTypeEquipement, $idTypeQualite)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFabriquer - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_FABRIQUER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFabriquer - pas d'etape fabriquer en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeFabriquer - etape fabriquer en cours");
				return self::calculEtapeFabriquer($etape, $braldun, $idTypeEquipement, $idTypeQualite);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeFabriquer($etape, &$braldun, $idTypeEquipement, $idTypeQualite)
	{
		if (self::calculEtapeFabriquerParam1et2et3($etape, $braldun, $idTypeEquipement, $idTypeQualite)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruire::conditions remplies, calcul fin etape");
			self::calculEtapeFabriquerFin($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeFabriquerParam1et2et3($etape, &$braldun, $idTypeEquipement, $idTypeQualite)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFabriquerParam1et2et3 - param1:" . $etape["param_1_etape"] . " param2:" . $etape["param_2_etape"] . " param3:" . $etape["param_3_etape"]);

		if ($etape["param_3_etape"] == date('N') && $etape["param_1_etape"] == 2) { // TODO à SUPPRIMER quand tout le monde a fini ce genre d'etape
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFabriquerParam1et2et3 - A");
			$retour = true;
		} elseif ($etape["param_3_etape"] == date('N') && $etape["param_1_etape"] == self::ETAPE_FABRIQUER_PARAM1_TYPE_PIECE && $etape["param_2_etape"] == $idTypeEquipement) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFabriquerParam1et2et3 - A");
			$retour = true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFabriquerParam1et2et3 - B");
		}
		return $retour;
	}

	private static function calculEtapeFabriquerFin($etape, &$braldun)
	{
		self::calculEtapeFinStandard($etape, $braldun);
	}

	public static function etapeCollecter(&$braldun, $idMetier)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeCollecter - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_COLLECTER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeCollecter - pas d'etape collecter en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeCollecter - etape collecter en cours");
				return self::calculEtapeCollecter($etape, $braldun, $idMetier);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeCollecter($etape, &$braldun, $idMetier)
	{
		if (self::calculEtapeCollecterParam1($etape, $braldun, $idMetier)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeConstruire::conditions remplies, calcul fin etape");
			self::calculEtapeCollecterFin($etape, $braldun, $idMetier);
			return true;
		} else {
			return false;
		}
	}

	private static function calculEtapeCollecterParam1($etape, &$braldun, $idMetier)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam1 - param1:" . $etape["param_1_etape"]);
		if ($etape["param_1_etape"] == $idMetier) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam1 - A");
			$retour = true;
		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam1 - D");
		}
		return $retour;
	}

	private static function calculEtapeCollecterFin($etape, &$braldun, $idMetier)
	{
		$retour = false;
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam2 - param2:" . $etape["param_2_etape"]);

		$moisPrecedent = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
		$moisSuivant = mktime(0, 0, 0, date("m") + 1, 1, date("Y")); // fin du mois en cours dans la requete sql

		$dateDebut = date("Y-m-d H:i:s", $moisPrecedent);
		$dateFin = date("Y-m-d H:i:s", $moisSuivant);

		Zend_Loader::loadClass("StatsRecolteurs");
		$statsRecolteursTable = new StatsRecolteurs();
		$stats = $statsRecolteursTable->findByBraldunAndDateAndIdTypeMetier($braldun->id_braldun, $dateDebut, $dateFin, $etape["param_1_etape"]);
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam2 - dateDebut:" . $dateDebut . " dateFin:" . $dateFin);
		if ($stats != null && count($stats) > 0) { // mise à jour des objectifs avec ce qu'il y a dans la table stats
			$nb = $stats[0]["nombre"];
			$retour = true;

			$etapeTable = new Etape();
			$data = array("objectif_etape" => $nb);
			if ($nb >= $etape["param_2_etape"]) { // fin etape
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam2 nb Ok, nb:" . $nb);
				$data["est_terminee_etape"] = "oui";
				$data["date_fin_etape"] = date("Y-m-d H:i:s");
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeFinStandard - Fin Ok");
				$where = "id_etape = " . $etape["id_etape"];
				$etapeTable->update($data, $where);
				if (self::activeProchaineEtape($braldun) == false) { // fin quete
					self::termineQuete($braldun);
				}
			} else {
				$where = "id_etape = " . $etape["id_etape"];
				$etapeTable->update($data, $where);
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeCollecterParam2 nb non Ok, nb:" . $nb);
			}
		}
		return $retour;
	}

	public static function etapeContacterParents(&$braldun, $idDestinatairesTab)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeContacterParents - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_CONTACTER_PARENTS_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeContacterParents - pas d'etape contacter en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeContacterParents - etape contacter en cours");
				return self::calculEtapeContacterParents($etape, $braldun, $idDestinatairesTab);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeContacterParents($etape, &$braldun, $idDestinatairesTab)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeContacterParents - enter");

		Zend_Loader::loadClass("Bral_Util_Famille");
		$tabPossedeParents = Bral_Util_Famille::getTabPossedeParentsActif($braldun);

		$dataEtape = null;

		if ($tabPossedeParents["est_orphelin"] == false) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeContacterParents - non orphelin - verification destinataires");

			$pereOk = false;
			$mereOk = false;

			foreach ($idDestinatairesTab as $idBraldun) {
				if ($tabPossedeParents["est_pere_actif"] == true && $braldun->id_fk_pere_braldun == $idBraldun) {
					$pereOk = true;
				} else if ($tabPossedeParents["est_pere_actif"] == false) {
					$pereOk = true;
				}

				if ($tabPossedeParents["est_mere_actif"] == true && $braldun->id_fk_mere_braldun == $idBraldun) {
					$mereOk = true;
				} else if ($tabPossedeParents["est_mere_actif"] == false) {
					$mereOk = true;
				}
			}

			if ($pereOk === true && $mereOk === true) {
				self::calculEtapeFinStandard($etape, $braldun);
				return true;
			} else {
				return false;
			}

		} else {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeContacterParents - orphelin, etape contacter parents annulee");
			self::calculEtapeFinStandard($etape, $braldun);
			return true;
		}

	}

	public static function etapeApprendreMetier(&$braldun)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreMetier - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_APPRENDRE_METIER_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreMetier - pas d'etape apprendre metier en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreMetier - etape apprendre metier en cours");
				self::calculEtapeApprendreMetier($etape, $braldun);
				return true;
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeApprendreMetier($etape, &$braldun)
	{
		$braldun->px_perso_braldun = $braldun->px_perso_braldun + 5;
		self::calculEtapeFinStandard($etape, $braldun);
	}

	public static function etapeAmeliorerCaracteristique(&$braldun)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeAmeliorerCaracteristique - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_AMELIORER_CARACTERISTIQUE_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeAmeliorerCaracteristique - pas d'etape ameliorer caract en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeAmeliorerCaracteristique - etape ameliorer caract en cours");
				self::calculEtapeAmeliorerCaracteristique($etape, $braldun);
				return true;
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeAmeliorerCaracteristique($etape, &$braldun)
	{
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeAmeliorerCaracteristique - enter");
		self::calculEtapeFinStandard($etape, $braldun);

		// On verifie ici que le joueur n'a pas déjà la competence identification des runes.
		$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_APPRENDRE_IDENTIFICATION_RUNES_ID);
		if ($etape == null) {
			throw new Zend_Exception("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeAmeliorerCaracteristique prochaine etape invalide idh:" . $braldun->id_braldun);
		} else {
			Zend_Loader::loadClass("Bral_Util_Competence");
			Zend_Loader::loadClass("BraldunsCompetences");
			$braldunsCompetencesTables = new BraldunsCompetences();
			$braldunCompetences = $braldunsCompetencesTables->findByIdBraldunAndNomSysteme($braldun->id_braldun, Bral_Util_Competence::NOM_SYSTEME_IDENTIFIER_RUNE);
			if ($braldunCompetences != null && count($braldunCompetences) >= 1) { // s'il possede la competence, on active le calcul de l'étape
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeAmeliorerCaracteristique - comptence identification rune deja apprise -");
				self::etapeApprendreIdentificationRune(&$braldun, Bral_Util_Competence::NOM_SYSTEME_IDENTIFIER_RUNE);
			}
		}
		Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::calculEtapeAmeliorerCaracteristique - exit");
	}

	public static function etapeApprendreIdentificationRune(&$braldun, $nomSystemeCompetence)
	{
		if (self::estQueteEnCours($braldun)) {
			Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreIdentificationRune - quete en cours -");
			$etape = self::getEtapeCourante($braldun, self::QUETE_ETAPE_APPRENDRE_IDENTIFICATION_RUNES_ID);
			if ($etape == null) {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreIdentificationRune - pas d'etape apprendre identification runes en cours");
				return null;
			} else {
				Bral_Util_Log::quete()->trace("Braldun " . $braldun->id_braldun . " - Bral_Util_Quete::etapeApprendreIdentificationRune - etape identification runes en cours");
				return self::calculEtapeApprendreIdentificationRune($etape, $braldun, $nomSystemeCompetence);
			}
		} else {
			return null;
		}
	}

	private static function calculEtapeApprendreIdentificationRune($etape, &$braldun, $nomSystemeCompetence)
	{
		Zend_Loader::loadClass("Bral_Util_Competence");

		if ($nomSystemeCompetence == Bral_Util_Competence::NOM_SYSTEME_IDENTIFIER_RUNE) {
			self::calculEtapeFinStandard($etape, $braldun);
			return true;
		} else {
			return false;
		}
	}
}
