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
		$tabRetour["cout_castar"] = $niveau * 10;
		return $tabRetour;
	}

}