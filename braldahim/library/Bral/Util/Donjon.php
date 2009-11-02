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
			if ($equipeCourante["etat_donjon_equipe"] == "en_cours" && $equipeCourante["date_mort_monstre_donjon_equipe"] <= Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), 3)) {
				self::finaliseDonjonReussi($donjon, $equipeCourante, $view);
			} elseif ($equipeCourante["etat_donjon_equipe"] == "en_cours" && $equipeCourante["date_mort_monstre_donjon_equipe"] == null && $equipeCourante["date_fin_donjon_equipe"] <= date("Y-m-d H:i:s")) {
				self::finaliseDonjonEchec($donjon, $equipeCourante, $view);
			}
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - exit -");
	}

	private static function finaliseDonjonEchec($donjon, $equipe, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonEchec - enter -");

		Zend_Loader::loadClass("Bral_Util_Lien");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findById($donjon["id_fk_region_donjon"]);
		$nomComte = $region["nom_region"];

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


		 Zend_Loader::loadClass("DonjonHobbit");
		 $donjonHobbitTable = new DonjonHobbit();
		 $donjonHobbit = $donjonHobbitTable->findByIdEquipe($equipe["id_donjon_equipe"]);

		 $listeHobbits = "";
		 foreach($donjonHobbit as $h) {
			$listeHobbits .= $h["prenom_hobbit"]. " ".$h["nom_hobbit"]. "(".$h["id_hobbit"]."), ";
			self::envoieMessageEchecHobbit($donjon, $equipe, $h, $view);
			self::finaliseHobbitEchec($h);
			}

			self::envoieMessageEchecHobbits($donjon, $equipe, $nomComte, $listeHobbits, $view);
			*/
		self::creationEmissaires($donjon, $view);

		// et l'on termine le donjon
		$donjonEquipeTable = new DonjonEquipe();
		$data = array(
			'etat_donjon_equipe' => 'termine',
		);
		$where = 'id_donjon_equipe='.$equipe['id_donjon_equipe'];
		//		$donjonEquipeTable->update($data, $where);
			
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

		Bral_Util_Donjon::messageSignature($detailsBot, $donjon);
		$detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs($detailsBot, false);

		$details = "[h".$hobbit["id_hobbit"]."] a échoué au Donjon, face à [m".$equipe["id_fk_monstre_donjon_equipe"]."]";

		$config = Zend_Registry::get('config');
		$idTypeEvenementCible = $config->game->evenements->type->ko;
		Bral_Util_Evenement::majEvenements($hobbit["id_hobbit"], $idTypeEvenementCible, $details, $detailsBot, $hobbit["niveau_hobbit"], "hobbit", true, $view);

		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbit - exit -");
	}

	private static function envoieMessageEchecHobbits($donjon, $equipe, $nomComte, $listeHobbits, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbits - enter -");
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Bonjour. ".PHP_EOL;
		$message .= "Tristre nouvelle aujourd'hui : ils ont échoué ! Oui, ".PHP_EOL;
		$message .= $listeHobbits.PHP_EOL;

		$message .= " n'ont pas réussi à vaincre [m".$equipe["id_fk_monstre_donjon_equipe"]."].".PHP_EOL.PHP_EOL;
		$message .= "Il est très en colère et a envoyé un émissaire dans ";
		$message .= "chaque ville de la ".$nomComte." !".PHP_EOL.PHP_EOL;
		$message .= "Aux armes ! Défendons nos villes !".PHP_EOL.PHP_EOL;

		$message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

		Bral_Util_Donjon::messageSignature($message, $donjon);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findAllJoueurs();
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbits - nbJoueurs:".count($hobbits));
		foreach($hobbits as $h) {
			Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $h["id_hobbit"], $message, $view);
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecHobbits - exit -");
	}

	private static function creationEmissaires($donjon, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - enter -");
		Bral_Util_Donjon::creationNids($donjon, "echec");
		Zend_Loader::loadClass("Bral_Batchs_Factory");
		Bral_Batchs_Factory::calculBatch("CreationMonstres", $view, $donjon["id_donjon"]);
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - exit -");
	}

	public static function creationNids($donjon, $type) {
		Zend_Loader::loadClass("Nid");
		$nidTable = new Nid();
		$where = "id_fk_donjon_nid = ".$donjon["id_donjon"];
		$nidTable->delete($where);

		Zend_Loader::loadClass("DonjonNid");
		$donjonNidTable = new DonjonNid();
		if ($type == "creation") {
			$nids = $donjonNidTable->findByIdDonjonCreation($donjon["id_donjon"]);
		} else {
			$nids = $donjonNidTable->findByIdDonjonEchec($donjon["id_donjon"]);
		}

		foreach ($nids as $n) {
			$nbMonstres = Bral_Util_De::get_de_specifique($n["nb_membres_min_type_groupe_monstre"], $n["nb_membres_max_type_groupe_monstre"]);
			$data["x_nid"] = $n["x_donjon_nid"];
			$data["y_nid"] = $n["y_donjon_nid"];
			$data["z_nid"] = $n["z_donjon_nid"];
			$data["nb_monstres_total_nid"] = $nbMonstres;
			$data["nb_monstres_restants_nid"] = $nbMonstres;

			$data["id_fk_zone_nid"] = $n["id_fk_zone_nid_donjon_nid"];
			$data["id_fk_type_monstre_nid"] = $n["id_fk_type_monstre_donjon_nid"];

			$data["id_fk_donjon_nid"] = $n["id_fk_donjon_nid"];
			$data["date_creation_nid"] = date("Y-m-d H:i:s");

			$data["date_generation_nid"] = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), abs($n["z_donjon_nid"]) - 7);

			$nidTable->insert($data);
		}
	}

	public static function dropGainsEtUpdateDonjon($idDonjon) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGainsEtUpdateDonjon - enter -");

		Zend_Loader::loadClass("DonjonEquipe");
		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($donjon["id_donjon"]);

		if (count($donjonEquipe) != 1) {
			throw new Exception('Equipe de donjon en cours != 1 idDonjon:'.$donjon["id_donjon"]);
		}
		
		self::dropGains($equipe);
		
		$data["date_mort_monstre_donjon_equipe"] = date("Y-m-d H:i:s");
		$where = "id_donjon_equipe = ".$donjonEquipe["id_donjon_equipe"];
		$donjonEquipeTable->update($data, $where);
		
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGainsEtUpdateDonjon - exit -");
		return true;
	}

	public static function dropGains($equipe) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGains - enter -");
		
		// TODO
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGains - exit -");
	}
	
	public static function finaliseDonjonReussi($donjon, $equipe, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonReussi - enter -");

		Zend_Loader::loadClass("Bral_Util_Lien");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findById($donjon["id_fk_region_donjon"]);
		$nomComte = $region["nom_region"];

		Zend_Loader::loadClass("DonjonHobbit");
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdEquipe($equipe["id_donjon_equipe"]);

		$listeHobbits = "";
		foreach($donjonHobbit as $h) {
			$listeHobbits .= $h["prenom_hobbit"]. " ".$h["nom_hobbit"]. "(".$h["id_hobbit"]."), ";
			self::envoieMessageReussiHobbit($donjon, $equipe, $nomComte, $h, $view);
			self::finaliseHobbitReussi($donjon, $h);
		}

		self::envoieMessageReussiHobbits($donjon, $equipe, $nomComte, $listeHobbits, $view);
			
		$donjonEquipeTable = new DonjonEquipe();
		$data = array(
			'etat_donjon_equipe' => 'termine',
		);
		$where = 'id_donjon_equipe='.$equipe['id_donjon_equipe'];
		//		$donjonEquipeTable->update($data, $where);

		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonReussi - exit -");
	}

	private static function finaliseHobbitReussi($donjon, $hobbit) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseHobbitReussi - enter h:".$hobbit["id_hobbit"]);

		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieuxRowset = $lieuTable->findByTypeAndRegion(TypeLieu::ID_TYPE_MARIE, $donjon["id_fk_region_donjon"], "non", "oui");
		$lieu = $lieuxRowset[0];

		$data = array(
				'est_donjon_hobbit' => 'non',
				'x_hobbit' => $lieu["x_lieu"],
				'y_hobbit' => $lieu["y_lieu"],
				'z_hobbit' => $lieu["z_lieu"],
		);
		$where = "id_hobbit = ".$hobbit["id_hobbit"];
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseHobbitReussi - exit -");
	}

	private static function envoieMessageReussiHobbit($donjon, $equipe, $nomComte, $hobbit, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiHobbit - enter h:".$hobbit["id_hobbit"]);
		$detailsBot = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$detailsBot .= "Féliciations ! ".PHP_EOL;
		$detailsBot .= "Vous avez réussi à venir à bout de [m".$equipe["id_fk_monstre_donjon_equipe"]."] en 2 lunes. ".PHP_EOL.PHP_EOL;
		$detailsBot .= "Vous êtes victorieux envoyé à la mairie de la capital de la ".$nomComte.".".PHP_EOL.PHP_EOL;

		Bral_Util_Donjon::messageSignature($detailsBot, $donjon);
		$detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs($detailsBot, false);

		$details = "[h".$hobbit["id_hobbit"]."] sort victorieux du Donjon face à [m".$equipe["id_fk_monstre_donjon_equipe"]."]";

		$config = Zend_Registry::get('config');
		$idTypeEvenementCible = $config->game->evenements->type->special;
		Bral_Util_Evenement::majEvenements($hobbit["id_hobbit"], $idTypeEvenementCible, $details, $detailsBot, $hobbit["niveau_hobbit"], "hobbit", true, $view);

		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiHobbit - exit -");
	}

	private static function envoieMessageReussiHobbits($donjon, $equipe, $nomComte, $listeHobbits, $view) {
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiHobbits - enter -");

		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Bonjour. ".PHP_EOL;
		$message .= "Ils l'ont fait ! Qui sont ces héros qui sont allés mettre une bonne rouste à [m".$equipe["id_fk_monstre_donjon_equipe"]."] ?".PHP_EOL;
		$message .= "Les voilà : ".$listeHobbits.PHP_EOL;

		$message .= " revenant chargés de trésors et de gloire ! ".PHP_EOL.PHP_EOL;
		$message .= "Sortez les toneaux, la bière va couler à flot ce soir dans notre belle ".$nomComte.".".PHP_EOL.PHP_EOL;

		$message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

		Bral_Util_Donjon::messageSignature($message, $donjon);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findAllJoueurs();
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiHobbits - nbJoueurs:".count($hobbits));
		foreach($hobbits as $h) {
			Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $h["id_hobbit"], $message, $view);
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiHobbits - exit -");
	}
}
