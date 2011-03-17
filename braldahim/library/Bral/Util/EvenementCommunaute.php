<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_EvenementCommunaute {

	public static function ajoutEvenements($idCommunaute, $idTypeEvenement, $details, $detailsBot, $view) {
		// $view utilisé pour envoyer des mails plus tard
		Zend_Loader::loadClass('EvenementCommunaute');
		Zend_Loader::loadClass("Bral_Util_Lien");

		$evenementCommunauteTable = new EvenementCommunaute();

		$detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);
		$detailsBotTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($detailsBot, false);

		$data = array(
			'id_fk_communaute_evenement_communaute' => $idCommunaute,
			'date_evenement_communaute' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement_communaute' => $idTypeEvenement,
			'details_evenement_communaute' => $detailsTransforme,
			'details_bot_evenement_communaute' => $detailsBotTransforme,
		);
		$evenementCommunauteTable->insert($data);
	}
}
