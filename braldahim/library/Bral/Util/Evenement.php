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
	 * Mise � jour des �v�nements du hobbit / du monstre.
	 */
	public static function majEvenements($idConcerne, $idTypeEvenement, $details, $detailsBot, $type="hobbit") {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();
		
		if ($type == "hobbit") {
			$data = array(
				'id_fk_hobbit_evenement' => $idConcerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $idTypeEvenement,
				'details_evenement' => $details,
				'details_bot_evenement' => $detailsBot,
			);
		} else {
			$data = array(
				'id_fk_monstre_evenement' => $idConcerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $idTypeEvenement,
				'details_evenement' => $details,
			);
		}
		$evenementTable->insert($data);
	}
}
