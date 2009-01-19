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
class Bral_Util_Evenement {

	/*
	 * Mise a jour des Evenements du hobbit / du monstre.
	 */
	public static function majEvenements($idConcerne, $idTypeEvenement, $details, $detailsBot, $niveau, $type="hobbit", $estAEnvoyer = false, $view = null) {
		Zend_Loader::loadClass('Evenement');
		$evenementTable = new Evenement();
		
		if ($type == "hobbit") {
			$data = array(
				'id_fk_hobbit_evenement' => $idConcerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $idTypeEvenement,
				'details_evenement' => $details,
				'details_bot_evenement' => $detailsBot,
				'niveau_evenement' => $niveau,
			);
		} else {
			$data = array(
				'id_fk_monstre_evenement' => $idConcerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $idTypeEvenement,
				'details_evenement' => $details,
				'niveau_evenement' => $niveau,
			);
		}
		$evenementTable->insert($data);
		
		if ($type == "hobbit" && $estAEnvoyer == true && $view != null) {
			self::envoiMail($idConcerne, $detailsBot, $view);
		}
	}
	
	public static function majEvenementsFromVieMonstre($idHobbitConcerne, $idMonstreConcerne, $idTypeEvenement, $details, $detailsBot, $niveau, $view) {
		Zend_Loader::loadClass('Evenement');
		$evenementTable = new Evenement();
		
		$data = array(
			'id_fk_hobbit_evenement' => $idHobbitConcerne,
			'id_fk_monstre_evenement' => $idMonstreConcerne,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $idTypeEvenement,
			'details_evenement' => $details,
			'details_bot_evenement' => $detailsBot,
			'niveau_evenement' => $niveau,
		);
		$evenementTable->insert($data);
		
		if ($idHobbitConcerne != null) {
			self::envoiMail($idHobbitConcerne, $detailsBot, $view);
		}
	}
	
	private static function envoiMail($idHobbitConcerne, $detailsBot, $view) {
		Zend_Loader::loadClass('Bral_Util_Mail');
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($idHobbitConcerne);
		
		if ($hobbitRowset != null) {
			$hobbit = $hobbitRowset->toArray();
			$c = Zend_Registry::get('config');
			if ($hobbit["envoi_mail_evenement_hobbit"] == "oui") {
				Bral_Util_Mail::envoiMailAutomatique($hobbit, $c->mail->evenement->titre, $detailsBot, $view);
			}
		} else {
			throw new Zend_Exception('Bral_Util_Evenement::envoiMail id Hobbit inconnu:'.$idHobbitConcerne);
		}
	}
}
