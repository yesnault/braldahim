<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Batchs_CreationMonstres extends Bral_Batchs_Batch {

	const USLEEP_DELTA = 100;

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculBatchImpl - enter -");

		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('GroupeMonstre');
		Zend_Loader::loadClass('TypeMonstre');
		Zend_Loader::loadClass('ZoneNid');
		Zend_Loader::loadClass('TailleMonstre');
		Zend_Loader::loadClass('ReferentielMonstre');
		Zend_Loader::loadClass('CreationNids');
		Zend_Loader::loadClass("Palissade");
		Zend_Loader::loadClass("Nid");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");

		$retour = null;

		$retour .= $this->calculCreation();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCreation() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculCreation - enter -");
		$retour = "";

		$refTable = new ReferentielMonstre();
		$taillesTable = new TailleMonstre();

		$refRowset = $refTable->findAll();
		$taillesRowset = $taillesTable->fetchall();

		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->fetchAll();

		$creationNidsTable = new CreationNids();

		$nidTable = new Nid();

		foreach($zones as $z) {
			// Récupération des types de monstres associés à la zone de nid
			$nids = $nidTable->findByIdZoneNid($z['id_zone_nid']);

			foreach($nids as $n) { // pour tous les nids dans la zone de nid
				$retour .= $this->calculZoneNid($n, $z, $refRowset, $taillesRowset);
			}
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculCreation - exit -");
		return $retour;
	}

	private function calculZoneNid($nid, $zone, $refRowset, $taillesMonstre) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculZoneNid - enter - idNid(".$nid["id_nid"].") id_zone_nid(".$zone['id_zone_nid'].")");
	
		$retour = "";
		$referenceCourante = $this->recupereReferenceMonstre($refRowset, $nid["id_fk_type_monstre_nid"]);
		$creation = true;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculZoneNid - idtypeGroupe(".$referenceCourante["id_fk_type_groupe_monstre"].")");

		$aCreer = 0;
		$nbRestantsDansNid = 0;
		$dateCourante = date("Y-m-d H:i:s");
		if ($nid["nb_monstres_restants_nid"] <= 0) {
			$nbRestantsDansNid = 0;
			$aCreer = 0;
		} elseif ($nid["date_generation_nid"] >= $dateCourante) {
			usleep(Bral_Util_De::get_de_specifique(1, self::USLEEP_DELTA));
			$aCreer = Bral_Util_De::get_de_specifique(1, $nid["nb_monstres_restants_nid"]);
			$nbRestantsDansNid = $nid["nb_monstres_restants_nid"] - $aCreer;
		} elseif ($dateCourante > Bral_Util_ConvertDate::get_date_add_day_to_date($nid["date_generation_nid"], 5)) { // date de generation + 5 jours : on prend totu ce qu'il reste
			$aCreer = $nid["nb_monstres_restants_nid"];
			$nbRestantsDansNid = $nid["nb_monstres_restants_nid"] - $aCreer;
		}

		if ($referenceCourante["id_fk_type_groupe_monstre"] > 1) { //1 => type Solitaire

			if ($aCreer < $referenceCourante["nb_membres_min_type_groupe_monstre"]) { // s'il n'y a pas assez a creer pour le type de groupe
				if ($aCreer + $nbRestantsDansNid < $referenceCourante["nb_membres_min_type_groupe_monstre"]) { // et qu'en rajoutant le reste du nid il n'y a pas assez
					// on supprime le nid
					$nbRestantsDansNid = -1;
				} else { // il y a assez pour faire un groupe avec ce qu'il reste dans le nid
					$aCreer = $referenceCourante["nb_membres_min_type_groupe_monstre"];
					$nbRestantsDansNid = $nid["nb_monstres_restants_nid"] - $aCreer;
				}
			}

			for ($i = 1; $i < $aCreer; $i++) {
				$nb_membres = Bral_Util_De::get_de_specifique($referenceCourante["nb_membres_min_type_groupe_monstre"], $referenceCourante["nb_membres_max_type_groupe_monstre"]);
				$i = $i + $nb_membres;

				$nid["nb_monstres_restants_nid"] = $nid["nb_monstres_restants_nid"] - $nb_membres;

				$id_groupe = $this->creationGroupe($referenceCourante["id_fk_type_groupe_monstre"], $nb_membres);
				$num_role_a = Bral_Util_De::get_de_specifique(1, $nb_membres);
				$num_role_b = Bral_Util_De::get_de_specifique(1, $nb_membres);
				while($num_role_a == $num_role_b) {
					$num_role_b = Bral_Util_De::get_de_specifique(1, $nb_membres);
				}

				for ($j = 1; $j <= $nb_membres; $j++) {
					$est_role_a = false;
					$est_role_b = false;
					if ($j == $num_role_a) {
						$est_role_a = true;
					}
					if ($j == $num_role_b) {
						$est_role_b = true;
					}

					$id_fk_taille_monstre = $this->creationCalculTaille($taillesMonstre);
					$referenceCourante = $this->recupereReferenceMonstre($refRowset, $referenceCourante["id_fk_type_ref_monstre"], $id_fk_taille_monstre);
					$niveau_monstre = Bral_Util_De::get_de_specifique($referenceCourante["niveau_min_ref_monstre"], $referenceCourante["niveau_max_ref_monstre"]);
					$positions = $this->calculPositions($zone, $niveau_monstre);
					$this->creationCalcul($zone['id_zone_nid'], $refRowset, $referenceCourante, $id_fk_taille_monstre, $niveau_monstre, $nid["x_nid"], $nid["y_nid"], $positions["x_min"], $positions["x_max"], $positions["y_min"], $positions["y_max"], $id_groupe, $est_role_a, $est_role_b);
				}
			}
		} else {
			// insertion de solitaires
			for ($i = 1; $i < $aCreer; $i++) {
				$id_fk_taille_monstre = $this->creationCalculTaille($taillesMonstre);
				$referenceCourante = $this->recupereReferenceMonstre($refRowset, $referenceCourante["id_fk_type_ref_monstre"], $id_fk_taille_monstre);
				$niveau_monstre = Bral_Util_De::get_de_specifique($referenceCourante["niveau_min_ref_monstre"], $referenceCourante["niveau_max_ref_monstre"]);
				$positions = $this->calculPositions($zone, $niveau_monstre);
				$this->creationCalcul($zone['id_zone_nid'], $refRowset, $referenceCourante, $id_fk_taille_monstre, $niveau_monstre, $nid["x_nid"], $nid["y_nid"], $positions["x_min"], $positions["x_max"], $positions["y_min"], $positions["y_max"]);
				$nid["nb_monstres_restants_nid"] = $nid["nb_monstres_restants_nid"] - 1;
			}
		}

		$nidTable = new Nid();

		if ($nbRestantsDansNid > 0) {
			$data = array("nb_monstres_restants_nid" => $nid["nb_monstres_restants_nid"]);
			$where = "id_nid=".$nid["id_nid"];
			$nidTable->update($data, $where);
		} else {
			$where = "id_nid=".$nid["id_nid"];
			$nidTable->delete($where);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculZoneNid - exit -");
		return $retour;
	}

	private function recupereReferenceMonstre($refMonstre, $id_fk_type_ref_monstre, $taille = 1) {
		$referenceCourante = null;
		foreach($refMonstre as $r) {
			if (($id_fk_type_ref_monstre == $r["id_fk_type_ref_monstre"]) && ((int)$taille == (int)$r["id_fk_taille_ref_monstre"])) {
				$referenceCourante = $r;
				break;
			}
		}

		if ($referenceCourante == null) {
			throw new Zend_Exception(get_class($this)." creationCalcul referenceCourante invalide. id_fk_type_ref_monstre=".$id_fk_type_ref_monstre. " taille=".$taille);
		}
		return $referenceCourante;
	}

	private function creationGroupe($id_type, $nb_membres) {
		$data = array(
			"id_fk_type_groupe_monstre" => $id_type,
			"date_creation_groupe_monstre" => date("Y-m-d H:i:s"),
			"id_fk_hobbit_cible_groupe_monstre"  => null,
			"nb_membres_max_groupe_monstre"  => $nb_membres,
			"nb_membres_restant_groupe_monstre" => $nb_membres,
			"phase_tactique_groupe_monstre" => 0,
			"id_role_a_groupe_monstre" => null,
			"id_role_b_groupe_monstre" => null
		);

		$groupeMonstreTable = new GroupeMonstre();
		$id_groupe = $groupeMonstreTable->insert($data);
		$data["id_groupe_monstre"] = $id_groupe;
		return $id_groupe;
	}

	private function creationCalcul($id_zone_nid, $refMonstre, $referenceCourante, $id_fk_taille_monstre, $niveau_monstre, $x, $y, $x_min, $x_max, $y_min, $y_max, $id_groupe_monstre = null, $est_role_a = false, $est_role_b = false) {

		$id_fk_type_monstre = $referenceCourante["id_fk_type_ref_monstre"];
		$id_type_groupe_monstre = $referenceCourante["id_type_groupe_monstre"];

		// NiveauSuivantPX = NiveauSuivant x 3 + debutNiveauPrecedentPx
		$pi_min = 0;
		for ($n = 0; $n <= $niveau_monstre; $n++) {
			$pi_min = $pi_min + 3 * $n;
		}
		$pi_max = 0;
		for ($n = 0; $n <=$niveau_monstre + 1; $n++) {
			$pi_max = $pi_max + 3 * $n;
		}
		if ($pi_max > $pi_min) {
			$pi_max = $pi_max - 1;
		}

		$nb_pi = Bral_Util_De::get_de_specifique($pi_min, $pi_max);
		$nb_pi = floor($nb_pi * $referenceCourante["coef_pi_ref_monstre"]);

		// Application de +/- 5% sur chaque carac
		$alea = Bral_Util_De::get_de_specifique(0, 10) - 5; // entre -5 et 5
		$p_force = $referenceCourante["pourcentage_force_ref_monstre"] + $alea;
		$alea = Bral_Util_De::get_de_specifique(0, 10) - 5; // entre -5 et 5
		$p_sagesse = $referenceCourante["pourcentage_sagesse_ref_monstre"] + $alea;
		$alea = Bral_Util_De::get_de_specifique(0, 10) - 5; // entre -5 et 5
		$p_agilite = $referenceCourante["pourcentage_agilite_ref_monstre"] + $alea;
		$alea = Bral_Util_De::get_de_specifique(0, 10) - 5; // entre -5 et 5
		$p_vigueur = $referenceCourante["pourcentage_vigueur_ref_monstre"] + $alea;

		//Calcul des pi pour chaque caractéristique
		$pi_force = round($nb_pi * $p_force / 100);
		$pi_sagesse = round($nb_pi * $p_sagesse / 100);
		$pi_agilite = round($nb_pi * $p_agilite / 100);
		$pi_vigueur = round($nb_pi * $p_vigueur / 100);

		// Détermination du nb d'améliorations possibles avec les PI dans chaque caractéristique
		$niveau_force = $this->calculNiveau($pi_force);
		$niveau_sagesse = $this->calculNiveau($pi_sagesse);
		$niveau_agilite = $this->calculNiveau($pi_agilite);
		$niveau_vigueur = $this->calculNiveau($pi_vigueur);

		$force_base_monstre = $this->config->game->inscription->force_base + $niveau_force;
		$sagesse_base_monstre = $this->config->game->inscription->sagesse_base + $niveau_sagesse;
		$agilite_base_monstre = $this->config->game->inscription->agilite_base + $niveau_agilite;
		$vigueur_base_monstre = $this->config->game->inscription->vigueur_base + $niveau_vigueur;

		//REG
		$regeneration_monstre = floor(($niveau_sagesse / 4) + 1);

		//ARMNAT
		$aleaArmNat = Bral_Util_De::get_de_specifique($referenceCourante["min_alea_pourcentage_armure_naturelle_ref_monstre"], $referenceCourante["min_alea_pourcentage_armure_naturelle_ref_monstre"]);
		$armure_naturelle_monstre = floor(($force_base_monstre + $vigueur_base_monstre) / 5) + $aleaArmNat;

		//DLA
		$dla_monstre = Bral_Util_ConvertDate::get_time_from_minutes(720 - 10 * $niveau_sagesse);
		$date_fin_tour_monstre = Bral_Util_ConvertDate::get_date_add_time_to_date(date("Y-m-d H:i:s"), "01:00:00");

		//PV
		$pv_restant_monstre = 20 + $niveau_vigueur * 4;

		//Vue
		$vue_monstre = $referenceCourante["vue_ref_monstre"];

		$data = array(
			"id_fk_type_monstre" => $id_fk_type_monstre,
			"id_fk_taille_monstre" => $id_fk_taille_monstre,
			"id_fk_groupe_monstre" => $id_groupe_monstre,
			"id_fk_zone_nid_monstre" => $id_zone_nid,
			"x_monstre" => $x,
			"y_monstre" => $y,
			"x_min_monstre" => $x_min,
			"x_max_monstre" => $x_max,
			"y_min_monstre" => $y_min,
			"y_max_monstre" => $y_max,
			"x_direction_monstre" => $x,
			"y_direction_monstre" => $y,
			"id_fk_hobbit_cible_monstre" => null,
			"pv_restant_monstre" => $pv_restant_monstre,
			"pv_max_monstre" => $pv_restant_monstre,
			"niveau_monstre" => $niveau_monstre,
			"vue_monstre" => $vue_monstre,
			"force_base_monstre" => $force_base_monstre,
			"force_bm_monstre" => 0,
			"agilite_base_monstre" => $agilite_base_monstre,
			"agilite_bm_monstre" => 0,
			"sagesse_base_monstre" => $sagesse_base_monstre,
			"sagesse_bm_monstre" => 0,
			"vigueur_base_monstre" => $vigueur_base_monstre,
			"vigueur_bm_monstre" => 0,
			"regeneration_monstre" => $regeneration_monstre,
			"armure_naturelle_monstre" => $armure_naturelle_monstre,
			"date_fin_tour_monstre" => $date_fin_tour_monstre,
			"duree_base_tour_monstre" => $dla_monstre,
			"nb_kill_monstre" => 0,
			"date_creation_monstre" => date("Y-m-d H:i:s"),
			"est_mort_monstre" => 'non',
			"pa_monstre" => 6, // pas de PA à la creation.
		);

		$monstreTable = new Monstre();
		$id_monstre = $monstreTable->insert($data);

		// mise à jour des roles
		if (($est_role_a === true) || ($est_role_b === true)) {
			if ($est_role_a) {
				$data = array(
					"id_role_a_groupe_monstre" => $id_monstre,
					"x_direction_groupe_monstre" => $x,
					"y_direction_groupe_monstre" => $y,
					"date_fin_tour_groupe_monstre" => $date_fin_tour_monstre,
				);
			}
			if ($est_role_b) {
				$data = array("id_role_b_groupe_monstre" => $id_monstre);
			}
			$groupeMonstreTable = new GroupeMonstre();
			$where = "id_groupe_monstre=".$id_groupe_monstre;
			$groupeMonstreTable->update($data, $where);
		}
	}

	private function creationCalculTaille($taillesMonstre) {
		$id_taille = null;

		usleep(Bral_Util_De::get_de_specifique(1, self::USLEEP_DELTA));
		$n = Bral_Util_De::get_de_specifique(1, 100);
		$total = 0;
		foreach($taillesMonstre as $t) {
			$total = $total + $t["pourcentage_taille_monstre"]; // % d'apparition
			if ($total >= $n) {
				$id_taille = $t["id_taille_monstre"];
				break;
			}
		}
		return $id_taille;
	}

	private function calculNiveau($pi_caract) {
		$niveau = 0;
		$pi = 0;
		for ($a=1; $a <= 100; $a++) {
			$pi = $pi + ($a - 1) * $a;
			if ($pi >= $pi_caract) {
				$niveau = $a;
				break;
			}
		}
		return $niveau;
	}

	private function calculPositions($zone_nid, $niveauMonstre) {

		$position["x_min"] = $zone_nid["x_min_zone_nid"];
		$position["x_max"] = $zone_nid["x_max_zone_nid"];
		$position["y_min"] = $zone_nid["y_min_zone_nid"];
		$position["y_max"] = $zone_nid["y_max_zone_nid"];

		if ($zone_nid["est_ville_zone_nid"] == "oui") {
			$xCentre =  $zone_nid["x_min_zone_nid"] + ($zone_nid["x_max_zone_nid"] - $zone_nid["x_min_zone_nid"]) /2;
			$yCentre =  $zone_nid["y_min_zone_nid"] + ($zone_nid["y_max_zone_nid"] - $zone_nid["y_min_zone_nid"]) /2;
				
			$position["x_min"] = $xCentre - ($niveauMonstre * 3) - 20;
			$position["x_max"] = $xCentre + ($niveauMonstre * 3) + 20;
			$position["y_min"] = $yCentre - ($niveauMonstre * 3) - 20;
			$position["y_max"] = $yCentre + ($niveauMonstre * 3) + 20;
		}

		return $position;
	}
}