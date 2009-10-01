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
class Bral_Batchs_CreationNids extends Bral_Batchs_Batch {

	const NB_MONSTRES_PAR_NID_MOYENNE = 10;
	const NB_MONSTRES_PAR_NID_MIN = 8;
	const NB_MONSTRES_PAR_NID_MAX = 12;

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculBatchImpl - enter -");

		Zend_Loader::loadClass("CreationNids");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Nid");
		Zend_Loader::loadClass("ZoneNid");
		Zend_Loader::loadClass("TypeMonstre");

		$retour = null;

		$retour .= $this->calculZonesHorsVille();

		$zoneNidTable = new ZoneNid();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculZonesHorsVille() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZonesHorsVille - enter -");
		$retour = "";

		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->findZonesHorsVille();

		foreach($zones as $z) {

			$this->calculZoneHorsVille($z);
			break; // zone 1 dev
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZonesHorsVille - exit -");
		return $retour;
	}

	// pour init uniquement de creationNids
	private function creationCreationNidsAll() {
		$creationNidsTable = new CreationNids();
		for ($i=1; $i<=59; $i++) {
			for ($j=1; $j<=11; $j++) {
				$data = array('id_fk_zone_creation_nid' => $i,
					'id_fk_type_monstre_creation_nid' => $j);
				$creationNidsTable->insert($data);
			}
		}
	}

	private function calculZoneHorsVille($zone) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneHorsVille - enter -");
		$retour = "";

		$monstreTable = new Monstre();
		$nidTable = new Nid();

		$nidsEnCours = $nidTable->countMonstresACreerByIdZone($zone["id_zone_nid"]);
		$nbMonstres = $monstreTable->countAllByIdZoneNid($zone["id_zone_nid"]);
		$totalMonstres = $nidsEnCours + $nbMonstres;

		$nbCasesDansZone = ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) * ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]);
		$couvertureMonstres = 100 * $nbMonstres / $nbCasesDansZone;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - idZoneNid ".$zone["id_zone_nid"].". NbMonstresTotal:".$totalMonstres. " couvertureDemandee:".$zone["couverture_zone_nid"]. " couvertureActuelle:".$couvertureMonstres);

		// s'il la couverture des monstres n'est pas suffisante
		if ($couvertureMonstres < $zone["couverture_zone_nid"]) {
			$this->calculCreationNids($zone);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneHorsVille - exit -");
		return $retour;
	}

	private function calculCreationNids($zone) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - enter -");
		$retour = "";

		$nidTable = new Nid();
		$monstreTable = new Monstre();

		$monstres = $monstreTable->countAllByTypeAndIdZoneNid($zone["id_zone_nid"]);
		$nids = $nidTable->countMonstresACreerByTypeMonstreAndIdZone($zone["id_zone_nid"]);

		$creationNidsTable = new CreationNids();
		$typesMonstres = $creationNidsTable->findByIdZoneNid($zone["id_zone_nid"]);
		$nbTypesTotalDansZone = count($typesMonstres);

		$nbCasesDansZone = ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) * ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]);

		$nbPourcentMonstresParTypeAAvoir = $zone["couverture_zone_nid"] / $nbTypesTotalDansZone;
		$nbMonstresParTypeAAvoir = $nbPourcentMonstresParTypeAAvoir * $nbCasesDansZone / 100;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - nbCasesDansZone:".$nbCasesDansZone." nbTypesTotalDansZone:".$nbTypesTotalDansZone." nbMonstresParTypeAAvoir ".$nbMonstresParTypeAAvoir);


		foreach($typesMonstres as $t) {
			$nbMonstre = 0;
			foreach($monstres as $m) {
				if ($t["id_fk_type_monstre_creation_nid"] == $m["id_fk_type_monstre"]) {
					$nbMonstre = $nbMonstre + $m["nombre"];
					break;
				}
			}
			foreach($nids as $n) {
				if ($t["id_fk_type_monstre_creation_nid"] == $n["id_fk_type_monstre_nid"]) {
					$nbMonstre = $nbMonstre + $n["nombre"];
					break;
				}
			}

			$nbMonstresManquants = $nbMonstresParTypeAAvoir - $nbMonstre;

			Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - idTypeMonstre:".$t["id_fk_type_monstre_creation_nid"]." nbMonstresManquants:".$nbMonstresManquants." -");

			// il n'y a pas assez de monstre du type dans la zone
			if ($nbMonstresManquants > self::NB_MONSTRES_PAR_NID_MOYENNE) {
				$this->creationNidsParTypeMonstre($zone, $t["id_fk_type_monstre_creation_nid"], $nbMonstresManquants);
			}

		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - exit -");
		return $retour;
	}

	private function creationNidsParTypeMonstre($zone, $idTypeMonstre, $nbMonstreACreer) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - creationNidsParTypeMonstre - enter -");
		$retour = "";

		$nidTable = new Nid();

		$nbNidACreer = floor($nbMonstreACreer / self::NB_MONSTRES_PAR_NID_MOYENNE);

		for($i=1; $i <= $nbNidACreer; $i++) {
			usleep(Bral_Util_De::get_de_specifique(1, 1000000));

			$nbMonstres = Bral_Util_De::get_de_specifique(8, 12);
			$nbJours = Bral_Util_De::get_de_specifique(0, 4);

			usleep(Bral_Util_De::get_de_specifique(1, 1000000));
			$x =  Bral_Util_De::get_de_specifique($zone["x_min_zone_nid"], $zone["x_max_zone_nid"]);

			usleep(Bral_Util_De::get_de_specifique(1, 1000000));
			$y =  Bral_Util_De::get_de_specifique($zone["y_min_zone_nid"], $zone["y_max_zone_nid"]);

			$data = array(
				'x_nid' => $x,
				'y_nid' => $y,
				'z_nid' => 0,
				'nb_monstres_total_nid' => $nbMonstres,
				'nb_monstres_restants_nid' => $nbMonstres,
				'id_fk_zone_nid' => $zone["id_zone_nid"],
				'id_fk_type_monstre_nid' => $idTypeMonstre,
				'date_creation_nid' => date("Y-m-d H:i:s"),
				'date_generation_nid' =>  Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), $nbJours),
			);
			$nidTable->insert($data);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - creationNidsParTypeMonstre - exit -");
		return $retour;
	}

}