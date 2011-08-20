<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Distinction {

	const ID_TYPE_BOURLINGUEUR_CENTRE = 1;
	const ID_TYPE_BOURLINGUEUR_SUD_OUEST = 2;
	const ID_TYPE_BOURLINGUEUR_SUD_EST = 3;
	const ID_TYPE_BOURLINGUEUR_NORD = 4;
	const ID_TYPE_BOURLINGUEUR_EST = 5;

	const ID_TYPE_DONJON_CENTRE = 6;
	const ID_TYPE_DONJON_SUD_OUEST = 7;
	const ID_TYPE_DONJON_SUD_EST = 8;
	const ID_TYPE_DONJON_NORD = 9;
	const ID_TYPE_DONJON_EST = 10;

	const ID_TYPE_TEAM = 11;
	const ID_TYPE_QUETE_RP = 12;
	const ID_TYPE_SOULE = 12;
	const ID_TYPE_BETA_TESTEUR = 14;

	const ID_TYPE_GRANDSCOMBATTANTSPVE_MOIS = 48;
	const ID_TYPE_GRANDSCOMBATTANTSPVP_MOIS = 49;
	const ID_TYPE_GRANDSCHASSEURSDEGIBIERS_MOIS = 50;
	const ID_TYPE_KO_MOIS = 51;
	const ID_TYPE_EXPERIENCE_MOIS = 52;
	const ID_TYPE_RECOLTEUR_MOIS_MINEUR = 53;
	const ID_TYPE_RECOLTEUR_MOIS_HERBORISTE = 54;
	const ID_TYPE_RECOLTEUR_MOIS_BUCHERON = 55;
	const ID_TYPE_RECOLTEUR_MOIS_CHASSEUR = 56;
	const ID_TYPE_FABRIQUANT_MOIS_APOTHICAIRE = 57;
	const ID_TYPE_FABRIQUANT_MOIS_MENUISIER = 58;
	const ID_TYPE_FABRIQUANT_MOIS_FORGERON = 59;
	const ID_TYPE_FABRIQUANT_MOIS_TANNEUR = 60;
	const ID_TYPE_FABRIQUANT_MOIS_PALISSADE = 61;
	const ID_TYPE_FABRIQUANT_MOIS_SENTIER = 62;
	const ID_TYPE_FABRIQUANT_MOIS_CUISINIER = 63;

	const ID_TYPE_IRL = 64;

	const ID_TYPE_KO_1_NEUTRE = 65;
	const ID_TYPE_KO_10_NEUTRE = 66;
	const ID_TYPE_KO_20_NEUTRE = 67;
	const ID_TYPE_KO_50_NEUTRE = 68;
	const ID_TYPE_KO_100_NEUTRE = 69;
	const ID_TYPE_KO_500_NEUTRE = 70;
	const ID_TYPE_KO_1000_NEUTRE = 71;

	const ID_TYPE_KO_5_REDRESSEURS_SUITE = 72;
	const ID_TYPE_KO_5_GREDINS_SUITE = 73;

	const ID_TYPE_KO_1_GREDIN_TOP = 74;
	const ID_TYPE_KO_1_REDRESSEUR_TOP = 75;

	const ID_TYPE_KO_1_WANTED = 76;
	const ID_TYPE_MEILLEUR_GREDIN_MOIS = 77;
	const ID_TYPE_MEILLEUR_REDRESSEUR_MOIS = 78;

	const ID_TYPE_KO_1_REDRESSEUR = 79;
	const ID_TYPE_KO_10_REDRESSEUR = 80;
	const ID_TYPE_KO_20_REDRESSEUR = 81;
	const ID_TYPE_KO_50_REDRESSEUR = 82;
	const ID_TYPE_KO_100_REDRESSEUR = 83;
	const ID_TYPE_KO_500_REDRESSEUR = 84;
	const ID_TYPE_KO_1000_REDRESSEUR = 85;

	const ID_TYPE_KO_1_GREDIN = 86;
	const ID_TYPE_KO_10_GREDIN = 87;
	const ID_TYPE_KO_20_GREDIN = 88;
	const ID_TYPE_KO_50_GREDIN = 89;
	const ID_TYPE_KO_100_GREDIN = 90;
	const ID_TYPE_KO_500_GREDIN = 91;
	const ID_TYPE_KO_1000_GREDIN = 92;

	const ID_TYPE_KO_NIVEAU_SUPERIEUR_OU_EGAL = 93;

	const ID_TYPE_GREDIN_MOIS = 94;
	const ID_TYPE_REDRESSEUR_MOIS = 95;

	const ID_TYPE_PLAQUEUR = 102;
	const ID_TYPE_MEILLEUR_PLAQUEUR = 103;
	const ID_TYPE_PASSEUR = 104;
	const ID_TYPE_MARQUEUR = 105;
	const ID_TYPE_GRANDE_COURSE = 106;
	const ID_TYPE_GAGNER_MATCH = 107;
	const ID_TYPE_GAGNER_MATCH_INFERIORITE = 108;
	const ID_TYPE_CHAMPION_SOULE = 109;

	const ID_TYPE_SOULE_JOUEUR_SAISON = 121;

	function __construct() {
	}

	public static function ajouterDistinction($idBraldun, $idTypeDistinction, $texte, $url = null, $quete = null) {
		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();

		$data = array(
			'id_fk_braldun_hdistinction' => $idBraldun,
			'id_fk_type_distinction_hdistinction' => $idTypeDistinction,
			'texte_hdistinction' => $texte,
			'url_hdistinction' => $url,
			'date_hdistinction' => date("Y-m-d"),
		);

		$braldunsDistinctionTable->insert($data);

		if ($quete != null) {
			Zend_Loader::loadClass("Quete");
			$queteTable = new Quete();
			$quete["gain_quete"] .= " Nouvelle distinction:" . $texte . PHP_EOL;
			$data = array(
				"gain_quete" => $quete["gain_quete"],
			);
			$where = "id_quete=" . $quete["id_quete"];
			$queteTable->update($data, $where);
		}
	}

	public static function ajouterDistinctionEtEvenement($idBraldun, $niveauBraldun, $idTypeDistinction, $moisDebut = null, $moisFin = null, $complementDistinction = "", $controlePossede = true) {

		if ($controlePossede) {
			$possede = self::possedeDistinction($idBraldun, $idTypeDistinction, $moisDebut, $moisFin);
		} else {
			$possede = false;
		}

		if ($possede == false) {
			Zend_Loader::loadClass("TypeDistinction");
			$typeDistinctionnTable = new TypeDistinction();
			$distinction = $typeDistinctionnTable->findDistinctionsByIdTypeDistinction($idTypeDistinction);
			$distinction = $distinction[0];

			$config = Zend_Registry::get('config');
			$idEvenement = $config->game->evenements->type->special;
			$details = "[b" . $idBraldun . "] a reçu une distinction : " . $distinction["nom_type_distinction"] . $complementDistinction;
			$detailBot = "Vous avez reçu une nouvelle distinction : " . $distinction["nom_type_distinction"] . $complementDistinction . " " . $distinction["points_type_distinction"] . " pt(s).";
			Bral_Util_Evenement::majEvenements($idBraldun, $idEvenement, $details, $detailBot, $niveauBraldun, "braldun");

			Bral_Util_Distinction::ajouterDistinction($idBraldun, $idTypeDistinction, $distinction["nom_type_distinction"] . $complementDistinction);
			return $detailBot;
		} else {
			return null;
		}
	}

	public static function prepareDistinctions($idBraldun) {
		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();
		$braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($idBraldun);
		unset($braldunsDistinctionTable);
		$tabDistinctions = null;
		$possedeDistinction = false;

		foreach ($braldunsDistinctionRowset as $t) {
			$possedeDistinction = true;

			$tabDistinctions[$t["id_type_categorie"]]["nom"] = $t["nom_type_categorie"];
			$tabDistinctions[$t["id_type_categorie"]]["distinctions"][] = array(
				"nom_systeme" => $t["nom_systeme_type_distinction"],
				"nom_type" => $t["nom_type_distinction"],
				"nom" => $t["texte_hdistinction"],
				"date_hdistinction" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $t["date_hdistinction"]),
				"url_hdistinction" => $t["url_hdistinction"],
				"points" => $t["points_type_distinction"],
			);

		}
		unset($braldunsDistinctionRowset);

		$retour["tabDistinctions"] = $tabDistinctions;
		$retour["possedeDistinction"] = $possedeDistinction;
		return $retour;
	}

	public static function getIdDistinctionDonjonFromIdDistinctionBourlingueur($idType) {
		switch ($idType) {
			case self::ID_TYPE_BOURLINGUEUR_CENTRE:
				return self::ID_TYPE_DONJON_CENTRE;
				break;
			case self::ID_TYPE_BOURLINGUEUR_SUD_OUEST:
				return self::ID_TYPE_DONJON_SUD_OUEST;
				break;
			case self::ID_TYPE_BOURLINGUEUR_SUD_EST:
				return self::ID_TYPE_DONJON_SUD_EST;
				break;
			case self::ID_TYPE_BOURLINGUEUR_NORD:
				return self::ID_TYPE_DONJON_NORD;
				break;
			case self::ID_TYPE_BOURLINGUEUR_EST:
				return self::ID_TYPE_DONJON_EST;
				break;
			default :
				throw new Zend_Exception("getIdDistinctionDonjonFromIdDistinctionBourlingueur invalide:" . $idType);
		}
	}

	public static function possedeDistinction($idBraldun, $idTypeDistinction, $moisDebut, $moisFin) {
		$retour = false;

		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();
		$braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunIdAndIdTypeDistinction($idBraldun, $idTypeDistinction, $moisDebut, $moisFin);
		$possedeDistinction = false;

		if ($braldunsDistinctionRowset != null && count($braldunsDistinctionRowset) > 0) {
			$retour = true;
		}
		return $retour;
	}

	/**
	 * Renvoie true si le joueur possède toutes les distinctions pour être champion de soule.
	 * @param int $idBraldun Identifiant du Braldûn
	 */
	public static function possedeDistinctionSoulePourChampion($idBraldun) {
		$retour = false;

		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();

		$listId[] = self::ID_TYPE_PLAQUEUR;
		$listId[] = self::ID_TYPE_MEILLEUR_PLAQUEUR;
		$listId[] = self::ID_TYPE_PASSEUR;
		$listId[] = self::ID_TYPE_MARQUEUR;
		$listId[] = self::ID_TYPE_GRANDE_COURSE;
		$listId[] = self::ID_TYPE_GAGNER_MATCH;
		$listId[] = self::ID_TYPE_GAGNER_MATCH_INFERIORITE;

		$braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunIdAndListeIdTypeDistinction($idBraldun, $listId);
		$possedeDistinction = false;

		if ($braldunsDistinctionRowset != null && count($braldunsDistinctionRowset) >= 7) {
			$retour = true;
		}
		return $retour;
	}
}