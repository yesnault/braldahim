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
		Zend_Loader::loadClass("ReferentielMonstre");
		Zend_Loader::loadClass("ZoneNid");
		Zend_Loader::loadClass("TailleMonstre");
		Zend_Loader::loadClass("TypeMonstre");

		$retour = null;

		$retour .= $this->calculZonesHorsVille();
		$retour .= $this->calculZonesVille();

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

		$nbMonstresDansNidsEnCours = $nidTable->countMonstresACreerByIdZone($zone["id_zone_nid"]);
		$nbMonstresActuels = $monstreTable->countAllByIdZoneNid($zone["id_zone_nid"]);
		$totalMonstres = $nbMonstresDansNidsEnCours + $nbMonstresActuels;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - idZoneNid ".$zone["id_zone_nid"].". nbMonstresDansNidsEnCours:".$nbMonstresDansNidsEnCours. " nbMonstresActuels:".$nbMonstresActuels);

		$nbCasesDansZone = ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) * ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]);
		$couvertureMonstres = 100 * $totalMonstres / $nbCasesDansZone;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - idZoneNid ".$zone["id_zone_nid"].". NbMonstresTotal:".$totalMonstres. " couvertureDemandee:".$zone["couverture_zone_nid"]. " couvertureActuelle:".$couvertureMonstres);

		// s'il la couverture des monstres n'est pas suffisante
		if ($couvertureMonstres < $zone["couverture_zone_nid"]) {
			$this->calculCreationNidsHorsVille($zone);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneHorsVille - exit -");
		return $retour;
	}

	private function calculCreationNidsHorsVille($zone) {
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

			Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - idTypeMonstre:".$t["id_fk_type_monstre_creation_nid"]." nbMonstresManquants:".$nbMonstresManquants);

			// il n'y a pas assez de monstre du type dans la zone
			if ($nbMonstresManquants > self::NB_MONSTRES_PAR_NID_MOYENNE) {
				$this->creationNidsParTypeMonstre($zone, $t["id_fk_type_monstre_creation_nid"], $nbMonstresManquants, $zone["x_min_zone_nid"], $zone["x_max_zone_nid"], $zone["y_min_zone_nid"], $zone["y_max_zone_nid"]);
			}

		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - exit -");
		return $retour;
	}

	private function creationNidsParTypeMonstre($zone, $idTypeMonstre, $nbMonstreACreer, $xMin, $xMax, $yMin, $yMax) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - creationNidsParTypeMonstre - enter -");
		$retour = "";

		$nidTable = new Nid();

		$nbNidACreer = floor($nbMonstreACreer / self::NB_MONSTRES_PAR_NID_MOYENNE);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - nbMonstreACreer:".$nbMonstreACreer." nbNidACreer:".$nbNidACreer);
		

		for($i=1; $i <= $nbNidACreer; $i++) {
			usleep(Bral_Util_De::get_de_specifique(1, 1000000));

			$nbMonstres = Bral_Util_De::get_de_specifique(8, 12);
			$nbJours = Bral_Util_De::get_de_specifique(0, 4);

			usleep(Bral_Util_De::get_de_specifique(1, 1000000));
			$x =  Bral_Util_De::get_de_specifique($xMin, $xMax);

			usleep(Bral_Util_De::get_de_specifique(1, 1000000));
			$y =  Bral_Util_De::get_de_specifique($yMin, $yMax);

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

	private function calculZonesVille() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZonesVille - enter -");
		$retour = "";

		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->findZonesVille();

		foreach($zones as $z) {

			$this->calculZoneVille($z);
			break; // zone 1 dev
		}


		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZonesVille - exit -");
		return $retour;
	}

	private function calculZoneVille($zone) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneVille - enter - idZoneNid:".$zone["id_zone_nid"]);
		$retour = "";

		// Récupération des types de monstres associés à la ville
		$creationNidsTable = new CreationNids();
		$typesMonstresCreationNids = $creationNidsTable->findByIdZoneNid($zone["id_zone_nid"]);

		$monstreTable = new Monstre();
		$monstresDansZone = $monstreTable->countAllByTypeAndIdZoneNid($zone["id_zone_nid"]);

		$nidTable = new Nid();
		$monstresDansNids = $nidTable->countMonstresACreerByTypeMonstreAndIdZone($zone["id_zone_nid"]);

		foreach($typesMonstresCreationNids as $t) {
			$nbMonstre = 0;
			foreach($monstresDansZone as $m) {
				if ($t["id_fk_type_monstre_creation_nid"] == $m["id_fk_type_monstre"]) {
					$nbMonstre = $nbMonstre + $m["nombre"];
					break;
				}
			}
			foreach($monstresDansNids as $n) {
				if ($t["id_fk_type_monstre_creation_nid"] == $n["id_fk_type_monstre_nid"]) {
					$nbMonstre = $nbMonstre + $n["nombre"];
					break;
				}
			}
			$this->calculCreationNidsVille($zone, $t, $nbMonstre);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneVille - exit -");
		return $retour;
	}

	private function calculCreationNidsVille($zone, $typeMonstreCreationNid, $nbMonstrePresents) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - enter -");
		$retour = "";

		if ($typeMonstreCreationNid["nb_monstres_ville_creation_nid"] == null) {
			throw new Zend_Exception("calculCreationNidsVille zone_nid:".$zone["id_zone_nid"]. " nbMonstresInvalide nb_monstres_ville_creation_nid:".$typeMonstreCreationNid["nb_monstres_ville_creation_nid"]);
		}

		$nbMonstreACreer = $typeMonstreCreationNid["nb_monstres_ville_creation_nid"] - $nbMonstrePresents;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - nbAAvoir:".$typeMonstreCreationNid["nb_monstres_ville_creation_nid"]." nbMonstrePresents:".$nbMonstrePresents." nbMonstreACreer:".$nbMonstreACreer);

		if ($nbMonstreACreer > self::NB_MONSTRES_PAR_NID_MOYENNE) {

			// récupération de la référence
			$referentielMonstreTable = new ReferentielMonstre();
			$ref = $referentielMonstreTable->findByTailleAndType(TailleMonstre::ID_TAILLE_GIGANTESQUE, $typeMonstreCreationNid["id_fk_type_monstre_creation_nid"]);
			if (count($ref) != 1) {
				throw new Zend_Exception("calculCreationNidsVille Ref Invalide dans ref_monstre idTypeMonstre:".$typeMonstreCreationNid["id_fk_type_monstre_creation_nid"]);
			}

			$niveauMaxMonstre = $ref[0]["niveau_max_ref_monstre"];

			$xCentreVille = $zone["x_min_zone_nid"] + ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) / 2;
			$yCentreVille = $zone["y_min_zone_nid"] + ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]) / 2;

			$rayonMin = $niveauMaxMonstre * 3; // le nid, avec un niveau gigantesque à 5, sera généré au minimum à 15 cases du centre de la ville
			$rayonMax = $rayonMin + 20; // et donc à 35 cases du centre de la ville au maximum

			for($nbMonstres = self::NB_MONSTRES_PAR_NID_MOYENNE; $nbMonstres <= $nbMonstreACreer; $nbMonstres = $nbMonstres + self::NB_MONSTRES_PAR_NID_MOYENNE) {
				$this->calculCreationNidsVilleZone($zone, $typeMonstreCreationNid["id_fk_type_monstre_creation_nid"], $xCentreVille, $yCentreVille, $rayonMin, $rayonMax, $niveauMaxMonstre, self::NB_MONSTRES_PAR_NID_MOYENNE);
			}
				
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - exit -");
		return $retour;
	}

	private function calculCreationNidsVilleZone($zone, $idTypeMonstre, $xCentreVille, $yCentreVille, $rayonMin, $rayonMax, $niveauMaxMonstre, $nbMonstreACreer) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVilleZone - enter -");
		$retour = "";

		$xMin = null;
		$xMax = null;
		$this->determineZoneDepuisCentre($xMin, $xMax, $xCentreVille, $rayonMin, $rayonMax);

		$yMin = null;
		$yMax = null;
		$this->determineZoneDepuisCentre($yMin, $yMax, $yCentreVille, $rayonMin, $rayonMax);
		
		$d = Bral_Util_De::get_de_specifique(1, 2); // repartition pour eviter une repartition aux 4 coins
		if ($d == 1) {
			$xMin = $xCentreVille - $rayonMax;
			$xMax = $xCentreVille + $rayonMax;
		} else if ($d == 2) {
			$yMin = $yCentreVille - $rayonMax;
			$yMax = $yCentreVille + $rayonMax;
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - niveauMaxMonstre:".$niveauMaxMonstre." rayonMin:".$rayonMin." rayonMax:".$rayonMax." xMin:".$xMin. " xMax:".$xMax. " yMin:".$yMin." yMax:".$yMax. " nbMonstreACreer:".$nbMonstreACreer);
		$this->creationNidsParTypeMonstre($zone, $idTypeMonstre, $nbMonstreACreer, $xMin, $xMax, $yMin, $yMax);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVilleZone - exit -");
		return $retour;
	}

	private function determineZoneDepuisCentre(&$min, &$max, $posCentre, $rayonMin, $rayonMax) {
		usleep(Bral_Util_De::get_de_specifique(1, 1000000));
		// Choix : gauche / droite si x concerne, ou haut ou en bas du centre si y concerne
		$d = Bral_Util_De::get_de_specifique(1, 2);
		if ($d == 1) { // en bas
			$min = $posCentre - $rayonMin;
			$max = $posCentre - $rayonMax;
		} else {
			$min = $posCentre + $rayonMin;
			$max = $posCentre + $rayonMax;
		}

		if ($min > $max) {
			$tmp = $max;
			$max = $min;
			$min = $tmp;
		}
	}

}