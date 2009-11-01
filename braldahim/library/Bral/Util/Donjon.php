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
class Bral_Util_Donjon {

	public static function messageSignature(&$message, $donjonCourant, $avecMessageInutile = true) {
		$message .= $donjonCourant["prenom_hobbit"]. " ".$donjonCourant["nom_hobbit"]. ", ";
		if ($donjonCourant["sexe_hobbit"] == "masculin") {
			$message .= "garde";
		} else {
			$message .= "gardienne";
		}
		$message .= " du donjon de la ".$donjonCourant["nom_region"].PHP_EOL;

		if ($avecMessageInutile) {
			$message .= "Inutile de répondre à ce message.";
		}
	}

	public static function controleInscriptionEquipe($donjon, $view) {
		Zend_Loader::loadClass('DonjonEquipe');

		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($donjon["id_donjon"]);

		if (count($donjonEquipe) > 1) {
			throw new Exception('Equipe de donjon en cours > 1 idDonjon:'.$donjon["id_donjon"]);
		}

		if (count($donjonEquipe) == 1) {
			$equipeCourante = $donjonEquipe[0];
			if ($equipeCourante["etat_donjon_equipe"] == "inscription" && $equipeCourante["date_limite_inscription_donjon_equipe"] <= date("Y-m-d H:i:s")) {
				self::annuleInscriptionEquipe($donjon, $equipeCourante, $view);
			}
		}
	}

	private static function annuleInscriptionEquipe($donjon, $equipe, $view) {
		$donjonEquipeTable = new DonjonEquipe();

		$data = array(
			'etat_donjon_equipe' => 'annule',
		);

		$where = 'id_donjon_equipe='.$equipe['id_donjon_equipe'];
		$donjonEquipeTable->update($data, $where);

		Zend_Loader::loadClass("DonjonHobbit");
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdEquipe($equipe["id_donjon_equipe"]);

		foreach($donjonHobbit as $h) {
			self::envoieMessageAnnulationHobbit($donjon, $h["id_hobbit"], $view);
		}
	}

	private static function envoieMessageAnnulationHobbit($donjon, $idHobbit, $view) {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Ahhhh ! ".PHP_EOL;
		$message .= "Vos coéquipiers n'ont pas validé à temps et la porte du Donjon n'a donc pas été ouverte.".PHP_EOL.PHP_EOL;
		$message .= " Votre inscription au Donjon est annulée.".PHP_EOL.PHP_EOL;

		Bral_Util_Donjon::messageSignature($message, $donjon);
		Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $idHobbit, $message, $view);
	}

	public static function controleFin($donjon, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - enter -");
		Zend_Loader::loadClass('DonjonEquipe');

		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($donjon["id_donjon"]);

		if (count($donjonEquipe) > 1) {
			throw new Exception('Equipe de donjon en cours > 1 idDonjon:'.$donjon["id_donjon"]);
		}

		if (count($donjonEquipe) == 1) {
			$equipeCourante = $donjonEquipe[0];
			if ($equipeCourante["etat_donjon_equipe"] == "en_cours" && $equipeCourante["date_fin_donjon_equipe"] <= date("Y-m-d H:i:s")) {
				self::finaliseDonjonEchec($donjon, $equipeCourante, $view);
			}
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - exit -");
	}

	private static function finaliseDonjonEchec($donjon, $equipe, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonEchec - enter -");

		Zend_Loader::loadClass("Bral_Util_Lien");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$donjonEquipeTable = new DonjonEquipe();

		$data = array(
			'etat_donjon_equipe' => 'termine',
		);

		$where = 'id_donjon_equipe='.$equipe['id_donjon_equipe'];
/*
 * 
 * 
 * 
 * 
 * 
 *  A Decommenter à la fin des devs des donjons
 * 
 * 
 * 
		$donjonEquipeTable->update($data, $where);

		Zend_Loader::loadClass("DonjonHobbit");
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdEquipe($equipe["id_donjon_equipe"]);

		$listeHobbits = "";
		foreach($donjonHobbit as $h) {
			$listeHobbits .= $h["prenom_hobbit"]. " ".$h["nom_hobbit"]. "(".$h["id_hobbit"]."), ";
			self::envoieMessageEchecHobbit($donjon, $equipe, $h, $view);
			self::finaliseHobbitEchec($h);
		}

		self::envoieMessageEchecHobbits($donjon, $equipe, $listeHobbits, $view);
*/
		self::creationEmissaires($donjon);
			
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonEchec - exit -");
	}

	private static function finaliseHobbitEchec($hobbit) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseHobbitEchec - enter h:".$hobbit["id_hobbit"]);
		$hobbit["nb_ko_hobbit"] = $hobbit["nb_ko_hobbit"] + 1;

		$data = array(
				'pv_restant_hobbit' => 0,
				'est_ko_hobbit' => 'oui',
				'nb_ko_hobbit' => $hobbit["nb_ko_hobbit"],
				'date_fin_tour_hobbit' =>  date("Y-m-d H:i:s"),
				'est_donjon_hobbit' => 'non',
		);
		$where = "id_hobbit = ".$hobbit["id_hobbit"];
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseHobbitEchec - exit -");
	}

	private static function envoieMessageEchecHobbit($donjon, $equipe, $hobbit, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbit - enter h:".$hobbit["id_hobbit"]);
		$detailsBot = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$detailsBot .= "Ahhhh ! ".PHP_EOL;
		$detailsBot .= "Vous n'avez réussi à venir à bout de [m".$equipe["id_fk_monstre_donjon_equipe"]."] en 2 lunes. ".PHP_EOL.PHP_EOL;
		$detailsBot .= "Vous êtes KO et renvoyé à l'hôpital le plus proche.".PHP_EOL.PHP_EOL;

		Bral_Util_Donjon::messageSignature($detailsBot, $donjon, false);

		$details = "[h".$hobbit["id_hobbit"]."] a échoué au Donjon, face à [m".$equipe["id_fk_monstre_donjon_equipe"]."]";

		$config = Zend_Registry::get('config');
		$idTypeEvenementCible = $config->game->evenements->type->ko;
		Bral_Util_Evenement::majEvenements($hobbit["id_hobbit"], $idTypeEvenementCible, $details, $detailsBot, $hobbit["niveau_hobbit"], "hobbit", true, $view);

		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbit - exit -");
	}

	private static function envoieMessageEchecHobbits($donjon, $equipe, $listeHobbits, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbits - enter -");
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Bonjour. ".PHP_EOL;
		$message .= "Tristre nouvelle aujourd'hui : ils ont échoué ! Oui, ".PHP_EOL;
		$message .= $listeHobbits.PHP_EOL;

		$message .= " n'ont pas réussi à vaincre [m".$equipe["id_fk_monstre_donjon_equipe"]."].".PHP_EOL.PHP_EOL;
		$message .= "Il est très en colère et a envoyé un émissaire dans ";
		$message .= "chaque ville de la comté !".PHP_EOL.PHP_EOL;
		$message .= "Aux armes ! Défendons nos villes !".PHP_EOL.PHP_EOL;

		$message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

		Bral_Util_Donjon::messageSignature($message, $donjon);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findAllJoueurs();
		foreach($hobbits as $h) {
			Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $h["id_hobbit"], $message, $view);
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbits - exit -");
	}

	private function creationEmissaires() {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - enter -");
		//TODO
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - exit -");
	}
}
