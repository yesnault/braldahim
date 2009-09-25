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


	public static function messageSignature(&$message, $donjonCourant) {
		$message .= $donjonCourant["prenom_hobbit"]. " ".$donjonCourant["nom_hobbit"]. ", ";
		if ($donjonCourant["sexe_hobbit"] == "masculin") {
			$message .= "garde";
		} else {
			$message .= "gardienne";
		}
		$message .= " du donjon de la ".$donjonCourant["nom_region"].PHP_EOL;
		$message .= "Inutile de répondre à ce message.";
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

}
