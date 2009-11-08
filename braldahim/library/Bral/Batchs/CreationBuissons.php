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
class Bral_Batchs_CreationBuissons extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - calculBatchImpl - enter -");

		Zend_Loader::loadClass('CreationBuissons');
		Zend_Loader::loadClass('Buisson');
		Zend_Loader::loadClass('TypeBuisson');
		Zend_Loader::loadClass('Zone');

		$retour = null;

		$retour .= $this->calculCreation();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCreation() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - calculCreation - enter -");
		$retour = "";

		$zoneTable = new Zone();

		$creationBuissonsTable = new CreationBuissons();
		$creationBuissons = $creationBuissonsTable->fetchAll(null, "id_fk_type_buisson_creation_buissons");
		$nbCreationBuissons = count($creationBuissons);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - nbCreationBuissons=" .$nbCreationBuissons);

		$typeBuissonTable = new TypeBuisson();
		$typeBuissons = $typeBuissonTable->fetchAll();
		$nbTypeBuissons = count($typeBuissons);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - nbTypeBuissons=" .$nbTypeBuissons);

		// selection des environnements / zones concernes
		$environnementIds = $this->getEnvironnementsConcernes($creationBuissons);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - nb environnement concernes=" .count($environnementIds));
		$zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - nb zones concernees=" .count($zones));

		$buissonTable = new Buisson();
		$tmp = "";

		$superficieZones = array();
		$superficieTotale = array();

		foreach($creationBuissons as $c) {
			// on recupere la supercifie totale de toutes les zones concernees par ce type
			foreach($zones as $z) {
				if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_buissons"]) {
					$superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
					if (array_key_exists($c["id_fk_type_buisson_creation_buissons"], $superficieTotale)) {
						$superficieTotale[$c["id_fk_type_buisson_creation_buissons"]] = $superficieTotale[$c["id_fk_type_buisson_creation_buissons"]] + ( $superficieZones[$z["id_zone"]] );
					} else {
						$superficieTotale[$c["id_fk_type_buisson_creation_buissons"]] = $superficieZones[$z["id_zone"]];
					}
				}
			}
		}

		foreach($creationBuissons as $c) {
			$t = null;
			foreach($typeBuissons as $type) {
				if ($c["id_fk_type_buisson_creation_buissons"] == $type["id_type_buisson"]) {
					$t = $type;
					break;
				}
			}
				
			if ($t != null) {
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - traitement du buisson ".$t["id_type_buisson"]. " nbMaxMonde(".$t["nb_creation_type_buisson"].") environnement(".$c["id_fk_environnement_creation_buissons"].") suptotal(". $superficieTotale[$c["id_fk_type_buisson_creation_buissons"]].")");
				foreach($zones as $z) {
					if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_buissons"]) {
						$tmp = "";
						$nbCreation = ceil($t["nb_creation_type_buisson"] * ($superficieZones[$z["id_zone"]] / $superficieTotale[$c["id_fk_type_buisson_creation_buissons"]]));
						$nbActuel = $buissonTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], 0, $t["id_type_buisson"]);

						$aCreer = $nbCreation - $nbActuel;
						if ($aCreer <= 0) {
							$tmp = " deja pleine";
						}
						Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - zone(".$z["id_zone"].") nbActuel:".$nbActuel. " max:".$nbCreation.$tmp. " supzone(".$superficieZones[$z["id_zone"]].") suptotal(". $superficieTotale[$c["id_fk_type_buisson_creation_buissons"]].")");
						if ($aCreer > 0) {
							$retour .= $this->insert($t["id_type_buisson"], $z, $aCreer, $buissonTable);
						} else {
							$retour .= "zone(".$z["id_zone"].") pleine de buisson(".$t["id_type_buisson"].") nbActuel(".$nbActuel.") max(".$nbCreation."). ";
						}
					}
				}
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - calculCreation - exit -");
		return $retour;
	}

	private function getEnvironnementsConcernes($creationBuissons) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - getEnvironnementsConcernes - enter -");
		$environnementIds = null;
		foreach($creationBuissons as $n) {
			$environnementIds[$n["id_fk_environnement_creation_buissons"]] = $n["id_fk_environnement_creation_buissons"];
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - getEnvironnementsConcernes - exit -");
		return $environnementIds;
	}

	private function insert($idTypeBuisson, $zone, $aCreer, $buissonTable) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - insert - enter - idtype(".$idTypeBuisson.") idzone(".$zone['id_zone'].") nbACreer(".$aCreer.")");
		$retour = "buisson(".$idTypeBuisson.") idzone(".$zone['id_zone'].") aCreer(".$aCreer."). ";

		for($i = 1; $i <= $aCreer; $i++) {
			$x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
			$y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);
			
			usleep(Bral_Util_De::get_de_specifique(1, 300000));
			$this->insertDb($buissonTable, $idTypeBuisson, $x, $y, 0, 1);
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBuissons - insert - exit -");
		return $retour;
	}

	private function insertDb($buissonTable, $idTypeBuisson, $x, $y, $z, $quantite) {
		if ($buissonTable->countByCase($y, $y, $z) == 0) {
			$data = array(
				'id_fk_type_buisson_buisson' => $idTypeBuisson, 
				'x_buisson' => $x, 
				'y_buisson' => $y, 
				'z_buisson' => $z, 
				'quantite_restante_buisson' => $quantite, 
				'quantite_max_buisson' => $quantite
			);
			$buissonTable->insert($data);
		}
	}
}