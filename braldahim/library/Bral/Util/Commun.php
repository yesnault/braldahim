<?php

class Bral_Util_Commun {

	function __construct() {
	}
	
	public function getVueBase($x, $y) {
		Zend_Loader::loadClass('Zone');
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y);
		$zone = $zones[0];

		$r = 0;
		switch($zone["nom_systeme_environnement"]) {
			case "marais":
				$r = 5;
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
				throw new Exception("getVueBase Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$x." y=".$y);
		}
		return $r;
	}
	
	public function getEnvironnement($x, $y) {
		Zend_Loader::loadClass('Zone');
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y);
		$zone = $zones[0];
		return $zone["nom_systeme_environnement"];
	}
	
	/*
	 * Mise à jour des évènements du hobbit.
	 */
	public function majEvenements($id_hobbit, $id_type_evenement, $details) {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();

		$data = array(
			'id_fk_hobbit_evenement' => $id_hobbit,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $id_type_evenement,
			'details_evenement' => $details,
		);
		$evenementTable->insert($data);
	}

	/*
	 * Regarde si la rune de @param est portée
	 */
	public function isRunePortee($idHobbit, $nomTypeRune) {
		$retour = false;
		Zend_Loader::loadClass("HobbitEquipement");
		$hobbitEquipementTable = new HobbitEquipement();
		$runesRowset = $hobbitEquipementTable->findRunesOnly($idHobbit);
		
		if ($runesRowset != null && count($runesRowset) > 0) {
			foreach ($runesRowset as $r) {
				if ($r["nom_type_rune"] == $nomTypeRune) {
					$retour = true;
					break;
				}
			}
		}
		return $retour;
	}
	
	public function getEquipementByIdMot($idHobbit, $idMot) {
		$retour = null;
		Zend_Loader::loadClass("HobbitEquipement");
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementRowset = $hobbitEquipementTable->findByIdMot($id_hobbit);
		
		if ($equipementRowset != null && count($equipementRowset) > 0) {
			foreach ($equipementRowset as $e) {
				$retour = $e;
				break;
			}
		}
		return $retour;
	}
}