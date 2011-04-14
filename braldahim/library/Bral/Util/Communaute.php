<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Communaute {

	const ID_RANG_GESTIONNAIRE = 1;
	const ID_RANG_ADJOINT = 2;
	const ID_RANG_TENANCIER = 3;
	const ID_RANG_NOUVEAU = 20;

	const NIVEAU_EN_CONSTRUCTION = -2;
	
	const NIVEAU_GRENIER_RECOLTER = 1;
	const NIVEAU_GRENIER_ENTRETENIR = 2;
	const NIVEAU_GRENIER_SEMER = 3;

	const NIVEAU_BARAQUEMENT_ACADEMIE = 1;
	const NIVEAU_BARAQUEMENT_POSITION_NIV_METIER = 2;
	const NIVEAU_BARAQUEMENT_PV_DLA = 3;
	const NIVEAU_BARAQUEMENT_PA_BM = 4;

	const NIVEAU_INFIRMERIE_SOIGNER = 1;
	const NIVEAU_INFIRMERIE_REVENIR = 2;

	const NIVEAU_ATELIER_ASSEMBLEUR = 1;
	const NIVEAU_ATELIER_JOAILLIER = 2;
	const NIVEAU_ATELIER_RECHERCHE = 3;
	
	const NIVEAU_TRIBUNE_GARE = 1;
	const NIVEAU_TRIBUNE_OFFICE_NOTARIAL = 2;
	const NIVEAU_TRIBUNE_CSS = 3;
	
	const NIVEAU_MARCHE_BANQUE = 1;
	const NIVEAU_MARCHE_HOTEL = 2;
	const NIVEAU_MARCHE_COFFRE_PERSO_COMMUN = 3;
	const NIVEAU_MARCHE_COFFRE_PERSO_VERS_AUTRE = 4;
	const NIVEAU_MARCHE_COFFRE_COMMUN_VERS_HOTEL = 5;

	public static function calculNouveauGestionnaire($idCommunaute, $idRangGestionnaire, $prenomGestionnaire, $nomGestionnaire, $sexeGestionnaire, $idGestionnaire, &$view) {

		$nouveauGestionnaire = null;

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByIdCommunaute($idCommunaute, -1, null, null, 'ordre_rang_communaute', ' ASC');

		foreach($bralduns as $b) {
			if ($b["ordre_rang_communaute"] < Bral_Util_Communaute::ID_RANG_NOUVEAU && $b["ordre_rang_communaute"] != Bral_Util_Communaute::ID_RANG_GESTIONNAIRE) {
				$data = array('id_fk_communaute_braldun' => $idCommunaute,
					'date_entree_communaute_braldun' => date("Y-m-d H:i:s"),
					'id_fk_rang_communaute_braldun' => $idRangGestionnaire,
				);
				$where = "id_braldun=".$b['id_braldun'];
				$braldunTable->update($data, $where);

				$communauteTable = new Communaute();
				$data = array(
					'id_fk_braldun_gestionnaire_communaute' => $b['id_braldun'],
				);
				$where = 'id_communaute = '.$idCommunaute;
				$communauteTable->update($data, $where);

				$message = "[Ceci est un message automatique de communauté]".PHP_EOL;
				$message .= $prenomGestionnaire. " ". $nomGestionnaire;
				$e = "";
				if ($sexeGestionnaire == "feminin") {
					$e = "e";
				}
				$message .= " (".$idGestionnaire.") est sorti".$e." de votre communauté.".PHP_EOL.PHP_EOL;

				if ($b['sexe_braldun'] == "feminin") {
					$message .= " Vous êtes devenue la nouvelle gestionnaire !".PHP_EOL;
				} else {
					$message .= " Vous êtes devenu le nouveau gestionnaire !".PHP_EOL;
				}

				Bral_Util_Messagerie::envoiMessageAutomatique($b['id_braldun'], $b['id_braldun'], $message, $view);
				$nouveauGestionnaire = $b['prenom_braldun'].' '.$b['nom_braldun']. ' ('.$b['id_braldun'].')';

				Zend_Loader::loadClass("TypeEvenementCommunaute");
				Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

				$details = "[b".$b['id_braldun']."]";
				$detailsBot = "[b".$b['id_braldun']."] est ";

				if ($nouveauGestionnaire['sexe_braldun'] == "feminin") {
					$detailsBot .= " devenue la nouvelle gestionnaire.".PHP_EOL;
				} else {
					$detailsBot .= " devenu le nouveau gestionnaire.".PHP_EOL;
				}

				$detailsBot .= PHP_EOL."Action réalisée automatiquement par la sortie de [b".$idGestionnaire."] de votre communauté.";
				Bral_Util_EvenementCommunaute::ajoutEvenements($idCommunaute, TypeEvenementCommunaute::ID_TYPE_GESTIONNAIRE, $details, $detailsBot, $view);

				break;
			}
		}
		return $nouveauGestionnaire;
	}

	/**
	 * Il faut dépenser 50xle niveau à atteindre en PA et 100xle niveau à atteindre en castar.
	 */
	public static function getCoutsAmeliorationBatiment($niveauAAtteindre) {
		$tabRetour["cout_pa"] = $niveauAAtteindre * 50;
		$tabRetour["cout_castar"] = $niveauAAtteindre * 100;
		return $tabRetour;
	}

	/**
	 * Niveau du bâtiment * 10 castars.
	 */
	public static function getCoutsEntretienBatiment($niveau) {
		if ($niveau < 1) {
			$niveau = 1;
		}
		$tabRetour["cout_castar"] = $niveau * 100;
		return $tabRetour;
	}

	/**
	 * Verifie que la position x,y,z correspond au hall de la communaute idCommunaute.
	 *
	 * @param int $x Position x
	 * @param int $y Position y
	 * @param int $z Position z
	 * @param int $idCommunaute Identifiant de la communaute
	 */
	public static function estSurHall($x, $y, $z, $idCommunaute) {
		$retour = false;
		Zend_Loader::loadClass("Communaute");
		$communauteTable = new Communaute();
		$communaute = $communauteTable->findById($idCommunaute);
		if ($communaute != null && count($communaute) == 1) {
			$communaute = $communaute[0];
			if ($communaute["x_communaute"] == $x
			&& $communaute["y_communaute"] == $y
			&& $communaute["z_communaute"] == $z) {
				$retour = true;
			}
		}
		return $retour;
	}

	/**
	 * Pour une communauté (idCommunauté), recherche si un type de
	 * Bâtiment d'un certain niveau est possédé et retourne son niveau.
	 * @param int $idCommunaute
	 * @param int $idTypeLieuCommunaute
	 */
	public static function getNiveauDuLieu($idCommunaute, $idTypeLieu) {
		$retour = -1;

		if ($idCommunaute == null) {
			return $retour;
		}

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByIdCommunaute($idCommunaute, null, null, null, false, $idTypeLieu);
		if ($lieux != null && count($lieux) > 0) {
			if ($lieux[0]["niveau_lieu"] != $lieux[0]["niveau_prochain_lieu"]) {
				$retour = self::NIVEAU_EN_CONSTRUCTION;
			} else {
				$retour = $lieux[0]['niveau_lieu'];
			}
			
		}
		return $retour;
	}

	/**
	 * Pour une communauté (idCommunauté), recherche si un type de
	 * Bâtiment d'un certain niveau est possédé. (niveau Min)
	 * @param int $idCommunaute
	 * @param int $idTypeLieuCommunaute
	 * @param int $niveauMin
	 */
	public static function possedeNiveauDuLieu($idCommunaute, $idTypeLieuCommunaute, $niveauMin) {
		$retour = false;
		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByIdCommunaute($idCommunaute, null, null, null, false, $idTypeLieuCommunaute, $niveauMin);
		if ($lieux != null && count($lieux) > 0) {
			$retour = true;
		}
		return $retour;
	}

}