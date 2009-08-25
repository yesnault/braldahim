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
class Bral_Batchs_CreationBosquets extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculBatchImpl - enter -");

		Zend_Loader::loadClass('CreationBosquets');
		Zend_Loader::loadClass('Bosquet');
		Zend_Loader::loadClass('TypeBosquet');
		Zend_Loader::loadClass('Zone');

		$retour = null;

		$retour .= $this->calculCreation();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCreation() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculCreation - enter -");
		$retour = "";

		$zoneTable = new Zone();

		$creationBosquetsTable = new CreationBosquets();
		$creationBosquets = $creationBosquetsTable->fetchAll(null, "id_fk_type_bosquet_creation_bosquets");
		$nbCreationBosquets = count($creationBosquets);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nbCreationBosquets=" .$nbCreationBosquets);

		$typeBosquetTable = new TypeBosquet();
		$typeBosquets = $typeBosquetTable->fetchAll();
		$nbTypeBosquets = count($typeBosquets);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nbTypeBosquets=" .$nbTypeBosquets);

		// selection des environnements / zones concernes
		$environnementIds = $this->getEnvironnementsConcernes($creationBosquets);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nb environnement concernes=" .count($environnementIds));
		$zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nb zones concernees=" .count($zones));

		$bosquetTable = new Bosquet();
		$tmp = "";

		$superficieZones = array();
		$superficieTotale = array();

		foreach($creationBosquets as $c) {
			// on recupere la supercifie totale de toutes les zones concernees par ce type
			foreach($zones as $z) {
				if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_bosquets"]) {
					$superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
					if (array_key_exists($c["id_fk_type_bosquet_creation_bosquets"], $superficieTotale)) {
						$superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] = $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] + ( $superficieZones[$z["id_zone"]] );
					} else {
						$superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] = $superficieZones[$z["id_zone"]];
					}
				}
			}
		}

		foreach($creationBosquets as $c) {
			$t = null;
			foreach($typeBosquets as $type) {
				if ($c["id_fk_type_bosquet_creation_bosquets"] == $type["id_type_bosquet"]) {
					$t = $type;
					break;
				}
			}
				
			if ($t != null) {
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - traitement du bosquet ".$t["id_type_bosquet"]. " nbMaxMonde(".$t["nb_creation_type_bosquet"].") environnement(".$c["id_fk_environnement_creation_bosquets"].") suptotal(". $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]].")");
				foreach($zones as $z) {
					if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_bosquets"]) {
						$tmp = "";
						$nbCreation = ceil($t["nb_creation_type_bosquet"] * ($superficieZones[$z["id_zone"]] / $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]]));
						$nbActuel = $bosquetTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], $t["id_type_bosquet"]);

						$aCreer = $nbCreation - $nbActuel;
						if ($aCreer <= 0) {
							$tmp = " deja pleine";
						}
						Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - zone(".$z["id_zone"].") nbActuel:".$nbActuel. " max:".$nbCreation.$tmp. " supzone(".$superficieZones[$z["id_zone"]].") suptotal(". $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]].")");
						if ($aCreer > 0) {
							$retour .= $this->insert($t["id_type_bosquet"], $z, $aCreer, $bosquetTable);
						} else {
							$retour .= "zone(".$z["id_zone"].") pleine de bosquet(".$t["id_type_bosquet"].") nbActuel(".$nbActuel.") max(".$nbCreation."). ";
						}
					}
				}
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculCreation - exit -");

		return $retour;
	}

	private function getEnvironnementsConcernes($creationBosquets) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - getEnvironnementsConcernes - enter -");
		$environnementIds = null;
		foreach($creationBosquets as $n) {
			$environnementIds[$n["id_fk_environnement_creation_bosquets"]] = $n["id_fk_environnement_creation_bosquets"];
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - getEnvironnementsConcernes - exit -");
		return $environnementIds;
	}

	private function insert($idTypeBosquet, $zone, $aCreer, $bosquetTable) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - insert - enter - idtype(".$idTypeBosquet.") idzone(".$zone['id_zone'].") nbACreer(".$aCreer.")");
		$retour = "bosquet(".$idTypeBosquet.") idzone(".$zone['id_zone'].") aCreer(".$aCreer."). ";

		for($i = 1; $i <= $aCreer; $i++) {
			$x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
			$y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);
				
			$nbCasesAutour = Bral_Util_De::get_de_specifique(3, 10);
			for($j=0; $j<=$nbCasesAutour; $j++) {
				for($k=0; $k<=$nbCasesAutour; $k++) {
					$i = $i + 1;
					$this->insertDb($bosquetTable, $idTypeBosquet, $x + $j, $y + $k, Bral_Util_De::get_de_specifique(5, 15));
				}
			}
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - insert - exit -");
		return $retour;
	}

	private function insertDb($bosquetTable, $idTypeBosquet, $x, $y, $quantite) {
		if ($bosquetTable->countByCase($y, $y) == 0) {
			$data = array(
				'id_fk_type_bosquet_bosquet' => $idTypeBosquet, 
				'x_bosquet' => $x, 
				'y_bosquet' => $y, 
				'quantite_restante_bosquet' => $quantite, 
				'quantite_max_bosquet' => $quantite
			);
			$bosquetTable->insert($data);
		}
	}
}