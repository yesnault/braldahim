<?php

class Bral_Util_Evenement {

	/*
	 * Mise à jour des évènements du hobbit / du monstre.
	 */
	public static function majEvenements($id_concerne, $id_type_evenement, $details, $type="hobbit") {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();
		
		if ($type == "hobbit") {
			$data = array(
				'id_fk_hobbit_evenement' => $id_concerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $id_type_evenement,
				'details_evenement' => $details,
			);
		} else {
			$data = array(
				'id_fk_monstre_evenement' => $id_concerne,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $id_type_evenement,
				'details_evenement' => $details,
			);
		}
		$evenementTable->insert($data);
	}
}
?>