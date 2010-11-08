<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_CreationMinerais extends Bral_Batchs_Batch {

	const MIN_SOL = 10;
	const MAX_SOL = 20;
	
	const MIN_SOUS_SOL = 100;
	const MAX_SOUS_SOL = 170;
	
	const COEF_QUANTITE_SOUS_SOL = 1.5;
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - calculBatchImpl - enter -");

		Zend_Loader::loadClass('CreationMinerais');
		Zend_Loader::loadClass('Filon');
		Zend_Loader::loadClass('TypeMinerai');
		Zend_Loader::loadClass('Zone');

		$retour = null;

		$retour .= $this->calculCreation(0);
		$retour .= $this->calculCreation(-10);
		$retour .= $this->calculCreation(-11);
		$retour .= $this->calculCreation(-12);
		$retour .= $this->calculCreation(-13);
		$retour .= $this->suppressionSurEau();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - calculBatchImpl - exit -");
		return $retour;
	}

	private function suppressionSurEau() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - suppressionSurEau - enter -");
		$retour = "";

		// Suppression des filons partout où il y a une eau
		Zend_Loader::loadClass("Eau");
		$eauTable = new Eau();
		$filonTable = new Filon();

		$nbEaux = $eauTable->countAll();
		$limit = 1000;

		$where = "";

		for ($offset = 0; $offset <= $nbEaux + $limit; $offset =  $offset + $limit) {
			$eaux = $eauTable->fetchall(null, null, $limit, $offset);
			$nb = 0;
			$where = "";
			foreach($eaux as $r) {
				$or = "";
				if ($where != "") {
					$or = " OR ";
				}

				$where .= $or." (x_filon = ".$r["x_eau"]. " AND y_filon = ".$r["y_eau"]." AND z_filon = ".$r["z_eau"].") ";
				$nb++;
				if ($nb == $limit) {
					$filonTable->delete($where);
					$nb = 0;
					$where = "";
				}
			}

			if ($where != "") {
				$filonTable->delete($where);
			}
		}

		if ($where != "") {
			$filonTable->delete($where);
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - suppressionSurEau - exit -");
		return $retour;
	}

	private function calculCreation($zposition) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - calculCreation - enter -");
		$retour = "";

		$zoneTable = new Zone();

		$creationMineraisTable = new CreationMinerais();
		$creationMinerais = $creationMineraisTable->fetchAll(null, "id_fk_type_minerai_creation_minerais");
		$nbCreationMinerais = count($creationMinerais);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - nbCreationMinerais=" .$nbCreationMinerais);

		$typeMineraiTable = new TypeMinerai();
		$typeMinerais = $typeMineraiTable->fetchAll();
		$nbTypeMinerais = count($typeMinerais);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - nbTypeMinerais=" .$nbTypeMinerais);

		// selection des environnements / zones concernes
		$environnementIds = $this->getEnvironnementsConcernes($creationMinerais);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - nb environnement concernes=" .count($environnementIds));
		$zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - nb zones concernees=" .count($zones));

		$filonTable = new Filon();
		$tmp = "";

		$superficieZones = array();
		$superficieTotale = array();

		foreach($creationMinerais as $c) {
			// on recupere la supercifie totale de toutes les zones concernees par ce type
			foreach($zones as $z) {
				if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_minerais"]) {
					$superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
					if (array_key_exists($c["id_fk_type_minerai_creation_minerais"], $superficieTotale)) {
						$superficieTotale[$c["id_fk_type_minerai_creation_minerais"]] = $superficieTotale[$c["id_fk_type_minerai_creation_minerais"]] + ( $superficieZones[$z["id_zone"]] );
					} else {
						$superficieTotale[$c["id_fk_type_minerai_creation_minerais"]] = $superficieZones[$z["id_zone"]];
					}
				}
			}
		}

		foreach($creationMinerais as $c) {
			$t = null;
			foreach($typeMinerais as $type) {
				if ($c["id_fk_type_minerai_creation_minerais"] == $type["id_type_minerai"]) {
					$t = $type;
					break;
				}
			}

			if ($t != null) {
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - traitement du minerai ".$t["id_type_minerai"]. " nbMaxMonde(".$t["nb_creation_type_minerai"].") environnement(".$c["id_fk_environnement_creation_minerais"].") suptotal(". $superficieTotale[$c["id_fk_type_minerai_creation_minerais"]].")");
				foreach($zones as $z) {

					if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_minerais"]) {
						$tmp = "";
						$nbCreation = ceil($t["nb_creation_type_minerai"] * ($superficieZones[$z["id_zone"]] / $superficieTotale[$c["id_fk_type_minerai_creation_minerais"]]));
						$nbActuel = $filonTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], $zposition, $t["id_type_minerai"]);

						if ($zposition != 0) {
							$nbCreation = intval($nbCreation * self::COEF_QUANTITE_SOUS_SOL);
						}
						$aCreer = $nbCreation - $nbActuel;
						if ($aCreer <= 0) {
							$tmp = " deja pleine";
						}
						Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - zone(".$z["id_zone"].") nbActuel:".$nbActuel. " max:".$nbCreation.$tmp. " supzone(".$superficieZones[$z["id_zone"]].") suptotal(". $superficieTotale[$c["id_fk_type_minerai_creation_minerais"]].")");
						if ($aCreer > 0) {
							$retour .= $this->insert($t["id_type_minerai"], $z, $aCreer, $filonTable, $zposition);
						} else {
							$retour .= "zone(".$z["id_zone"].") pleine de minerai(".$t["id_type_minerai"].") nbActuel(".$nbActuel.") max(".$nbCreation."). ";
							$retour .= $this->supprime($t["id_type_minerai"], $z, $nbActuel, 0 - $aCreer, $filonTable, $zposition);
						}
					}
				}
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - calculCreation - exit -");

		return $retour;
	}

	private function getEnvironnementsConcernes($creationMinerais) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - getEnvironnementsConcernes - enter -");
		$environnementIds = null;
		foreach($creationMinerais as $n) {
			$environnementIds[$n["id_fk_environnement_creation_minerais"]] = $n["id_fk_environnement_creation_minerais"];
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - getEnvironnementsConcernes - exit -");
		return $environnementIds;
	}

	private function supprime($idTypeMinerai, $zone, $nbActuel, $aSupprimer, $filonTable, $zposition) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - supprime - enter - idtype(".$idTypeMinerai.") idzone(".$zone['id_zone'].") aSupprimer(".$aSupprimer.")");
		$retour = "minerai(".$idTypeMinerai.") idzone(".$zone['id_zone'].") aSupprimer(".$aSupprimer."). ";

		if ($aSupprimer <= 0) {
			return $retour;
		}

		$filons = $filonTable->selectVue($zone["x_min_zone"], $zone["y_min_zone"], $zone["x_max_zone"], $zone["y_max_zone"], $zposition, $idTypeMinerai);

		shuffle($filons);

		$total = count($filons);
		$nb = 0;
		$where = "";
		for($i = 1; $i <= $aSupprimer; $i++) {

			$or = "";
			if ($where != "") {
				$or = " OR ";
			}

			$filon = array_pop($filons);

			$where .= $or."id_filon=".$filon["id_filon"];
			$nb++;
			if ($nb == 1000) {
				$filonTable->delete($where);
				$nb = 0;
				$where = "";
			}
		}

		if ($where != "") {
			$filonTable->delete($where);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - supprime - exit -");
		return $retour;
	}

	private function insert($idTypeMinerai, $zone, $aCreer, $filonTable, $zposition) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - insert - enter - idtype(".$idTypeMinerai.") idzone(".$zone['id_zone'].") nbACreer(".$aCreer.")");
		$retour = "minerai(".$idTypeMinerai.") idzone(".$zone['id_zone'].") aCreer(".$aCreer."). ";

		for($i = 1; $i <= $aCreer; $i++) {
			usleep(Bral_Util_De::get_de_specifique(50, 10000));
			$x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
			usleep(Bral_Util_De::get_de_specifique(100, 10000));
			$y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);

				
			if ($zposition == 0) {
				$quantite = Bral_Util_De::get_de_specifique(self::MIN_SOL, self::MAX_SOL);
			} else {
				$quantite = Bral_Util_De::get_de_specifique(self::MIN_SOUS_SOL, self::MAX_SOUS_SOL);
			}

			$data = array(
				'id_fk_type_minerai_filon' => $idTypeMinerai, 
				'x_filon' => $x, 
				'y_filon' => $y, 
				'z_filon' => $zposition,
				'quantite_restante_filon' => $quantite, 
				'quantite_max_filon' => $quantite
			);
			$filonTable->insert($data);
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMinerais - insert - exit -");
		return $retour;
	}
}