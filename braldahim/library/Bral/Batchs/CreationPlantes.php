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
class Bral_Batchs_CreationPlantes extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - calculBatchImpl - enter -");
		$retour = null;
		
		Zend_Loader::loadClass('CreationPlantes');
		Zend_Loader::loadClass('Plante');
		Zend_Loader::loadClass('TypePlante');
		Zend_Loader::loadClass('Zone');
		
		$retour .= $this->calculCreation();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - calculBatchImpl - exit -");
		return $retour;
	}
	
	private function calculCreation() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - calculCreation - enter -");
		$retour = "";
		
		$zoneTable = new Zone();
		
		$creationPlantesTable = new CreationPlantes();
		$creationPlantes = $creationPlantesTable->fetchAll(null, "id_fk_type_plante_creation_plantes");
		$nbCreationPlantes = count($creationPlantes);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - nbCreationPlantes=" .$nbCreationPlantes);

		$typePlanteTable = new TypePlante();
		$typePlantes = $typePlanteTable->fetchAll();
		$nbTypePlantes = count($typePlantes);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - nbTypePlantes=" .$nbTypePlantes);

		// selection des environnements / zones concernes
		$environnementIds = $this->getEnvironnementsConcernes($creationPlantes);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - nb environnement concernes=" .count($environnementIds));
		$zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - nb zones concernees=" .count($zones));
		
		$envNbZones = array();
		// pour chaque type d'environnement
		// on compte le nombre de zone concernees
		foreach($zones as $z) {
			if (array_key_exists($z["id_fk_environnement_zone"], $envNbZones)) {
				$envNbZones[$z["id_fk_environnement_zone"]] = $envNbZones[$z["id_fk_environnement_zone"]] + 1;
			} else {
				$envNbZones[$z["id_fk_environnement_zone"]] = 1;
			}
		}
		
		// Pour chaque zone et chaque type de plante, on insert
		
		$planteTable = new Plante();
		$tmp = "";
		
		$superficieZones = array();
		$superficieTotale = array();
		
		foreach($creationPlantes as $c) {
			// on recupere la supercifie totale de toutes les zones concernees par ce type
			foreach($zones as $z) {
				if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_plantes"]) {
					$superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
					if (array_key_exists($c["id_fk_type_plante_creation_plantes"], $superficieTotale)) {
						$superficieTotale[$c["id_fk_type_plante_creation_plantes"]] = $superficieTotale[$c["id_fk_type_plante_creation_plantes"]] + ( $superficieZones[$z["id_zone"]] );
					} else {
						$superficieTotale[$c["id_fk_type_plante_creation_plantes"]] = $superficieZones[$z["id_zone"]];
					}
				}
			}
		}
		
		foreach($creationPlantes as $c) {
			$t = null;
			foreach($typePlantes as $type) {
				if ($c["id_fk_type_plante_creation_plantes"] == $type["id_type_plante"]) {
					$t = $type;
					break;
				}
			}
			
			if ($t != null) {
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - traitement du type plante ".$t["id_type_plante"]. " nbMaxMonde(".$t["nb_creation_type_plante"].") environnement(".$c["id_fk_environnement_creation_plantes"].") suptotal(". $superficieTotale[$c["id_fk_type_plante_creation_plantes"]].")");
				foreach($zones as $z) {
					if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_plantes"]) {
						$tmp = "";
						$nbCreation = ceil($t["nb_creation_type_plante"] / $envNbZones[$z["id_fk_environnement_zone"]]);
						$nbActuel = $planteTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"]);
						
						$aCreer = $nbCreation - $nbActuel;
						if ($aCreer <= 0) { 
							$tmp = " deja pleine";
						}
						Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - zone(".$z["id_zone"].") nbActuel:".$nbActuel. " max:".$nbCreation.$tmp. " supzone(".$superficieZones[$z["id_zone"]].") suptotal(". $superficieTotale[$c["id_fk_type_plante_creation_plantes"]].")");
						if ($aCreer > 0) { 
							$retour .= $this->insert($t, $z, $aCreer, $planteTable);
						} else {
							$retour .= "zone(".$z["id_zone"].") pleine de plante(".$t["id_type_plante"].") nbActuel(".$nbActuel.") max(".$nbCreation."). ";
						}
					}
				}
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - calculCreation - exit -");
		
		return $retour;
	}
	
	private function getEnvironnementsConcernes($creationPlantes) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - getEnvironnementsConcernes - enter -");
		$environnementIds = null;
		foreach($creationPlantes as $n) {
			$environnementIds[$n["id_fk_environnement_creation_plantes"]] = $n["id_fk_environnement_creation_plantes"];
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - getEnvironnementsConcernes - exit -");
		return $environnementIds;
	}
	
	private function insert($typePlante, $zone, $aCreer, $planteTable) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - insert - enter - idtype(".$typePlante["id_type_plante"].") idzone(".$zone['id_zone'].") nbACreer(".$aCreer.")");
		$retour = "plante(".$typePlante["id_type_plante"].") idzone(".$zone['id_zone'].") aCreer(".$aCreer."). ";
		
		$min = 5;
		$max = 10;
		
		for ($i=1; $i<= $aCreer; $i++) {
			$x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
			$y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);

			$partie_1 = Bral_Util_De::get_de_specifique($min, $max);
			$partie_2 = null;
			$partie_3 = null;
			$partie_4 = null;
			
			if ($typePlante["id_fk_partieplante2_type_plante"] != null) {
				$partie_2 = Bral_Util_De::get_de_specifique($min, $max);
			}
			if ($typePlante["id_fk_partieplante3_type_plante"] != null) {
				$partie_3 = Bral_Util_De::get_de_specifique($min, $max);
			}
			if ($typePlante["id_fk_partieplante4_type_plante"] != null) {
				$partie_4 = Bral_Util_De::get_de_specifique($min, $max);
			}
			$data = array(
				'id_fk_type_plante' => $typePlante["id_type_plante"],
				'x_plante' => $x,
				'y_plante' => $y,
				'partie_1_plante' => $partie_1,
				'partie_2_plante' => $partie_2,
				'partie_3_plante' => $partie_3,
				'partie_4_plante' => $partie_4,
			);

			$planteTable->insert($data);
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationPlantes - insert - exit -");
		return $retour;
	}
	
}