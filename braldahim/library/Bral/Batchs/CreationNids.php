<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_CreationNids extends Bral_Batchs_Batch {

	const NB_MONSTRES_PAR_NID_MOYENNE = 8;
	const NB_MONSTRES_PAR_NID_MIN = 5;
	const NB_MONSTRES_PAR_NID_MAX = 12;

	const USLEEP_DELTA = 10000;

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculBatchImpl - enter -");

		Zend_Loader::loadClass("CreationNids");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Nid");
		Zend_Loader::loadClass("ReferentielMonstre");
		Zend_Loader::loadClass("ZoneNid");
		Zend_Loader::loadClass("TailleMonstre");
		Zend_Loader::loadClass("TypeMonstre");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$retour = null;

		/* Les nids dans les donjons et les dans les mines ne sont pas traités ici.
		 * Mine : compétence creuser.
		 * Donjon : Util Donjon
		 */

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
			if ($z["z_zone_nid"] == 0) {
				$this->calculZoneHorsVille($z);
			}
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

	// pour init uniquement de creationNids
	private function creationCreationNidsAllVilles() {
		$creationNidsTable = new CreationNids();
		for ($i=60; $i<=74; $i++) {
			for ($j=1; $j<=11; $j++) {

				if ($j == 2 || $j == 3 || $j == 4) {
					$nb = 190;
				} else {
					$nb = 30;
				}
				$data = array('id_fk_zone_creation_nid' => $i,
					'id_fk_type_monstre_creation_nid' => $j,
					'nb_monstres_ville_creation_nid' => $nb);
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

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - idZoneNid:".$zone["id_zone_nid"].". nbMonstresDansNidsEnCours:".$nbMonstresDansNidsEnCours. " nbMonstresActuels:".$nbMonstresActuels. " totalMonstres:".$totalMonstres);

		$nbCasesDansZone = ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) * ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]);
		$couvertureMonstres = 100 * $totalMonstres / $nbCasesDansZone;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - idZoneNid ".$zone["id_zone_nid"].". NbMonstresTotal:".$totalMonstres. " couvertureDemandee:".$zone["couverture_zone_nid"]. " couvertureActuelle:".$couvertureMonstres);

		// meme si la couverture est suffisante, on rentre dans calculCreationNidsHorsVille pour
		// eventuellement supprimer les monstres d'un type qui n'est plus associé à la zone
		// ou les monstres en trop
		$this->calculCreationNidsHorsVille($zone);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneHorsVille - exit -");
		return $retour;
	}

	private function calculCreationNidsHorsVille($zone) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - enter idZone:".$zone["id_zone_nid"]." -");
		$retour = "";

		$nidTable = new Nid();
		$monstreTable = new Monstre();

		$monstres = $monstreTable->countAllByTypeAndIdZoneNid($zone["id_zone_nid"]);
		$nids = $nidTable->countMonstresACreerByTypeMonstreAndIdZone($zone["id_zone_nid"]);

		$creationNidsTable = new CreationNids();
		$typesMonstresDansZone = $creationNidsTable->findByIdZoneNid($zone["id_zone_nid"]);
		$nbTypesTotalDansZone = count($typesMonstresDansZone);

		$typeMonstreTable = new TypeMonstre();
		$tousTypesMontres = $typeMonstreTable->fetchAllSansGibier();

		$nbCasesDansZone = ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) * ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]);

		$nbPourcentMonstresParTypeAAvoir = $zone["couverture_zone_nid"] / $nbTypesTotalDansZone;
		$nbMonstresParTypeAAvoir = $nbPourcentMonstresParTypeAAvoir * $nbCasesDansZone / 100;

		$nbMonstresTotalAAvoir = $nbCasesDansZone * $zone["couverture_zone_nid"] / 100;

		Bral_Util_Log::batchs()->debug("Bral_Batchs_CreationNids - calculZoneHorsVille - nbCasesDansZone:".$nbCasesDansZone." nbTypesTotalDansZone:".$nbTypesTotalDansZone." nbMonstresParTypeAAvoir ".$nbMonstresParTypeAAvoir. " nbMonstresTotalAAvoir ".$nbMonstresTotalAAvoir);

		foreach($tousTypesMontres as $t) {
			$nbMonstre = 0;
			foreach($monstres as $m) {
				if ($m["z_monstre"] == 0 && $t["id_type_monstre"] == $m["id_fk_type_monstre"]) {
					$nbMonstre = $nbMonstre + $m["nombre"];
					break;
				}
			}
			foreach($nids as $n) {
				if ($n["z_nid"] == 0 && $t["id_type_monstre"] == $n["id_fk_type_monstre_nid"]) {
					$nbMonstre = $nbMonstre + $n["nombre"];
					break;
				}
			}

			$nbMonstresManquants = 0;
			$typeDansZone = false;
			foreach($typesMonstresDansZone as $z) { // si le type de monstre est rattaché à la zone nid
				if ($z["id_fk_type_monstre_creation_nid"] == $t["id_type_monstre"]) {
					$nbMonstresManquants = $nbMonstresParTypeAAvoir - $nbMonstre;
					Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - idTypeMonstre:".$t["id_type_monstre"]." nbMonstresManquants dans zone:".$nbMonstresManquants. " nbMonstre:".$nbMonstre);
					$typeDansZone = true;
					break;
				}
			}

			if ($typeDansZone == false) { // si le type n'est pas rattaché à la zone, il faut supprimer les restants de type
				$nbMonstresManquants = - $nbMonstre;
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - idTypeMonstre:".$t["id_type_monstre"]." non present dans la zone. Nb monstres à supprimer:".$nbMonstre);
			}

			// il n'y a pas assez de monstre du type dans la zone
			if ($nbMonstresManquants > self::NB_MONSTRES_PAR_NID_MOYENNE) {
				$this->creationNidsParTypeMonstre($zone, $t["id_type_monstre"], $nbMonstresManquants, $zone["x_min_zone_nid"], $zone["x_max_zone_nid"], $zone["y_min_zone_nid"], $zone["y_max_zone_nid"]);
			} elseif ($nbMonstresManquants < 0) { // s'il y a trop de monstres du type
				// $nbMonstresManquants ==> nombre à supprimer en négatif
				$this->suppressionMonstresParTypeMonstre($zone, $t["id_type_monstre"], -$nbMonstresManquants);
			}

		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNids - exit -");
		return $retour;
	}

	private function creationNidsParTypeMonstre($zone, $idTypeMonstre, $nbMonstreACreer, $xMin, $xMax, $yMin, $yMax) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - creationNidsParTypeMonstre - enter - idz:".$zone["id_zone_nid"] . " idTypeMonstre:".$idTypeMonstre. "xmin:$xMin , xmax:$xMax , ymin:$yMin , ymax:$yMax ");
		$retour = "";

		$nidTable = new Nid();

		$nbNidACreer = floor($nbMonstreACreer / self::NB_MONSTRES_PAR_NID_MOYENNE);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - idz:".$zone["id_zone_nid"]. " - nbMonstreACreer:".$nbMonstreACreer." nbNidACreer:".$nbNidACreer);

		$config = Zend_Registry::get('config');

		for($i=1; $i <= $nbNidACreer; $i++) {
			usleep(Bral_Util_De::get_de_specifique(1, self::USLEEP_DELTA));

			$nbMonstres = Bral_Util_De::get_de_specifique(self::NB_MONSTRES_PAR_NID_MIN, self::NB_MONSTRES_PAR_NID_MAX);
			$nbJours = Bral_Util_De::get_de_specifique(0, 4);

			usleep(Bral_Util_De::get_de_specifique(50, self::USLEEP_DELTA));
			$x =  Bral_Util_De::get_de_specifique($xMin, $xMax);

			if ($x > $xMax || $x < $xMin) {
				echo "ERREUR idz:".$zone["id_zone_nid"]." x:$x xmin:$xMin xmax:$xMax";
			}
			
			// il faut vérifier que l'on n'est pas sur de l'eau ou sur une ruine
			//TODO

			usleep(Bral_Util_De::get_de_specifique(100, self::USLEEP_DELTA));
			$y =  Bral_Util_De::get_de_specifique($yMin, $yMax);

			if ($x <= $config->game->x_min) {
				$x = $config->game->x_min + 1;
			}
			if ($x >= $config->game->x_max) {
				$x = $config->game->x_max - 1;
			}
			if ($y <= $config->game->y_min) {
				$y = $config->game->y_min + 1;
			}
			if ($y >= $config->game->y_max) {
				$y = $config->game->y_max - 1;
			}

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

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - creationNidsParTypeMonstre - exit - idz:".$zone["id_zone_nid"]);
		return $retour;
	}

	private function suppressionMonstresParTypeMonstre($zone, $idTypeMonstre, $nbMonstreASupprimer) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - suppressionMonstresParTypeMonstre - enter - idz:".$zone["id_zone_nid"]. " - idTypeMonstre:".$idTypeMonstre." -");
		$retour = "";

		// s'il y a des nids de ce type, on les supprime
		$nidTable = new Nid();
		$nids = $nidTable->findACreerByTypeMonstreAndIdZone($zone["id_zone_nid"], $idTypeMonstre);

		$nbSupprime = 0;
		foreach($nids as $n) {
			$nbSupprime = $nbSupprime + $n["nb_monstres_restants_nid"];
			$where = "id_nid = ".$n["id_nid"];
			$nidTable->delete($where);

			if ($nbSupprime >= $nbMonstreASupprimer) {
				break;
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - idz:".$zone["id_zone_nid"]. " - suppressionMonstresParTypeMonstre A - nbMonstreASupprimer:".$nbMonstreASupprimer." nbSupprime dans nids:".$nbSupprime);

		if ($nbSupprime <= $nbMonstreASupprimer) { // s'il n'y a pas assez de monstres supprimés, il faut en supprimer des vivants
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findVivantByIdZoneNidAndIdType($zone["id_zone_nid"], $idTypeMonstre);

			foreach($monstres as $m) {
				$where = "id_monstre=".$m["id_monstre"];
				$nbJours = Bral_Util_De::get_1d2();
				$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), $nbJours);

				$data = array(
					"date_fin_cadavre_monstre" => $dateFin,
					"est_mort_monstre" => "oui",
					"id_fk_groupe_monstre" => null,
				);
				$monstreTable->update($data, $where);
				$details = "[m".$m["id_monstre"]."] est mort de vieillesse.";
				Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $m["id_monstre"], $this->config->game->evenements->type->killmonstre, $details, "", $m["niveau_monstre"], $this->view);

				$nbSupprime = $nbSupprime + 1;

				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - idz:".$zone["id_zone_nid"]. " - suppressionMonstresParTypeMonstre B - nbMonstreASupprimer:".$nbMonstreASupprimer." nbSupprime dans vivants:".$nbSupprime);
					
				if ($nbSupprime >= $nbMonstreASupprimer) {
					break;
				}

			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - suppressionMonstresParTypeMonstre - exit - idz:".$zone["id_zone_nid"]);
		return $retour;
	}

	private function calculZonesVille() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZonesVille - enter -");
		$retour = "";

		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->findZonesVille();

		foreach($zones as $z) {
			if ($z["z_zone_nid"] == 0) {
				$this->calculZoneVille($z);
			}
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

		$typeMonstreTable = new TypeMonstre();
		$tousTypesMontres = $typeMonstreTable->fetchAllSansGibier();

		foreach($tousTypesMontres as $t) {
			$nbMonstre = 0;
			foreach($monstresDansZone as $m) {
				if ($t["id_type_monstre"] == $m["id_fk_type_monstre"]) {
					$nbMonstre = $nbMonstre + $m["nombre"];
					break;
				}
			}
			foreach($monstresDansNids as $n) {
				if ($t["id_type_monstre"] == $n["id_fk_type_monstre_nid"]) {
					$nbMonstre = $nbMonstre + $n["nombre"];
					break;
				}
			}

			$creationNidsRow = null;
			$nbACreerDansZone = 0;
			$nb_monstres_ville_creation_nid = 0;
			foreach($typesMonstresCreationNids as $c) {
				if ($t["id_type_monstre"] == $c["id_fk_type_monstre_creation_nid"]) {
					$creationNidsRow = $c;
					$nb_monstres_ville_creation_nid = $c["nb_monstres_ville_creation_nid"];
					break;
				}
			}

			$nbACreerDansZone = $nb_monstres_ville_creation_nid - $nbMonstre;

			Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneVille - nb_monstres_ville_creation_nid:".$nb_monstres_ville_creation_nid. " nbMonstre:".$nbMonstre." nbACreerDansZone:".$nbACreerDansZone);

			if ($nbACreerDansZone < 0) {  // il faut supprimer les monstres en trop
				$this->suppressionMonstresParTypeMonstre($zone, $t["id_type_monstre"], -$nbACreerDansZone);
			} elseif ($creationNidsRow != null) {
				$this->calculCreationNidsVille($zone, $creationNidsRow, $nbMonstre);
			}

			//
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculZoneVille - exit -");
		return $retour;
	}

	private function calculCreationNidsVille($zone, $typeMonstreCreationNid, $nbMonstrePresents) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - enter - idz:".$zone["id_zone_nid"]);
		$retour = "";

		if ($typeMonstreCreationNid["nb_monstres_ville_creation_nid"] == null) {
			throw new Zend_Exception("calculCreationNidsVille zone_nid:".$zone["id_zone_nid"]. " nbMonstresInvalide nb_monstres_ville_creation_nid:".$typeMonstreCreationNid["nb_monstres_ville_creation_nid"]);
		}

		$nbMonstreACreer = $typeMonstreCreationNid["nb_monstres_ville_creation_nid"] - $nbMonstrePresents;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - idz:".$zone["id_zone_nid"]. " - nbAAvoir:".$typeMonstreCreationNid["nb_monstres_ville_creation_nid"]." nbMonstrePresents:".$nbMonstrePresents." nbMonstreACreer:".$nbMonstreACreer);

		if ($nbMonstreACreer > self::NB_MONSTRES_PAR_NID_MOYENNE) {

			// récupération de la référence
			$referentielMonstreTable = new ReferentielMonstre();
			$ref = $referentielMonstreTable->findByTailleAndType(TailleMonstre::ID_TAILLE_GIGANTESQUE, $typeMonstreCreationNid["id_fk_type_monstre_creation_nid"]);
			if (count($ref) != 1) {
				throw new Zend_Exception("calculCreationNidsVille Ref Invalide dans ref_monstre idz:".$zone["id_zone_nid"]. " idTypeMonstre:".$typeMonstreCreationNid["id_fk_type_monstre_creation_nid"]);
			}

			$niveauMaxMonstre = $ref[0]["niveau_max_ref_monstre"];

			$xCentreVille = $zone["x_min_zone_nid"] + ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) / 2;
			$yCentreVille = $zone["y_min_zone_nid"] + ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]) / 2;

			$rayonMin = $niveauMaxMonstre * 3; // le nid, avec un niveau gigantesque à 5, sera généré au minimum à 15 cases du centre de la ville
			$rayonMax = $rayonMin + 20; // et donc à 35 cases du centre de la ville au maximum

			if ($rayonMax > 100) {
				$rayonMax = 99;
			}

			for($nbMonstres = self::NB_MONSTRES_PAR_NID_MOYENNE; $nbMonstres <= $nbMonstreACreer; $nbMonstres = $nbMonstres + self::NB_MONSTRES_PAR_NID_MOYENNE) {
				$this->calculCreationNidsVilleZone($zone, $typeMonstreCreationNid["id_fk_type_monstre_creation_nid"], $xCentreVille, $yCentreVille, $rayonMin, $rayonMax, $niveauMaxMonstre, self::NB_MONSTRES_PAR_NID_MOYENNE);
			}

		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - exit - idz:".$zone["id_zone_nid"]);
		return $retour;
	}

	private function calculCreationNidsVilleZone($zone, $idTypeMonstre, $xCentreVille, $yCentreVille, $rayonMin, $rayonMax, $niveauMaxMonstre, $nbMonstreACreer) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVilleZone - enter - idz:".$zone["id_zone_nid"]);
		$retour = "";

		$xMin = null;
		$xMax = null;
		$this->determineZoneDepuisCentre($xMin, $xMax, $xCentreVille, $rayonMin, $rayonMax);

		$yMin = null;
		$yMax = null;
		$this->determineZoneDepuisCentre($yMin, $yMax, $yCentreVille, $rayonMin, $rayonMax);

		if ($xMin < $zone["x_min_zone_nid"]) {
			$xMin = $xMin + Bral_Util_De::get_de_specifique(1, 5);
		}

		if ($xMax > $zone["x_max_zone_nid"]) {
			$xMax = $xMax - Bral_Util_De::get_de_specifique(1, 5);
		}

		if ($yMin < $zone["y_min_zone_nid"]) {
			$yMin = $yMin + Bral_Util_De::get_de_specifique(1, 5);
		}

		if ($yMax > $zone["y_max_zone_nid"]) {
			$yMax = $yMax - Bral_Util_De::get_de_specifique(1, 5);
		}

		$d = Bral_Util_De::get_de_specifique(1, 2); // repartition pour eviter une repartition aux 4 coins
		if ($d == 1) {
			$xMin = $xCentreVille - $rayonMax;
			$xMax = $xCentreVille + $rayonMax;
		} else if ($d == 2) {
			$yMin = $yCentreVille - $rayonMax;
			$yMax = $yCentreVille + $rayonMax;
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVille - idz:".$zone["id_zone_nid"]. " - niveauMaxMonstre:".$niveauMaxMonstre." rayonMin:".$rayonMin." rayonMax:".$rayonMax." xMin:".$xMin. " xMax:".$xMax. " yMin:".$yMin." yMax:".$yMax. " nbMonstreACreer:".$nbMonstreACreer);
		$this->creationNidsParTypeMonstre($zone, $idTypeMonstre, $nbMonstreACreer, $xMin, $xMax, $yMin, $yMax);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationNids - calculCreationNidsVilleZone - exit - idz:".$zone["id_zone_nid"]);
		return $retour;
	}

	private function determineZoneDepuisCentre(&$min, &$max, $posCentre, $rayonMin, $rayonMax) {
		usleep(Bral_Util_De::get_de_specifique(1, self::USLEEP_DELTA));
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