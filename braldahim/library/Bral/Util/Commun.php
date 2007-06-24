<?php

class Bral_Util_Commun {

	function __construct() {
	}
	
	public function getVueBase($x, $y) {
		Zend_Loader::loadClass('Zone');
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findCase($x, $y);
		$zone = $zones[0];

		$r = 0;
		switch($zone["nom_systeme_environnement"]) {
			case "marais":
				$r = 3;
				break;
			case "montagne":
				$r = 5;
				break;
			case "caverne":
				$r = 2;
				break;
			case "plaine" :
				$r = 6;
				break;
			case "foret" :
				$r = 4;
				break;
			default :
				throw new Exception("getVueBase Environnement invalide:".$zone["nom_systeme_environnement"]);
		}
		return $r;
	}
	
	/*
	 * Mise à jour des évènements du hobbit.
	 */
	public function majEvenements($id_hobbit, $id_type_evenement, $details) {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();

		$data = array(
		'id_hobbit_evenement' => $id_hobbit,
		'date_evenement' => date("Y-m-d H:i:s"),
		'id_fk_type_evenement' => $id_type_evenement,
		'details_evenement' => $details,
		);
		$evenementTable->insert($data);
	}
}