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
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('GroupeMonstre');
		Zend_Loader::loadClass('TypeMonstre');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('TailleMonstre');
		Zend_Loader::loadClass('ReferentielMonstre');
		Zend_Loader::loadClass('CreationMonstres'); 
		Zend_Loader::loadClass('Ville'); 
		Zend_Loader::loadClass("Palissade");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		
		$retour = null;
		
		$retour .= $this->calculCreation();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculBatchImpl - exit -");
		return $retour;
	}
	
	private function calculCreation() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculCreation - enter -");
		$retour = "";
		
		$villeTable = new Ville();
		$villes = $villeTable->fetchAll();
		
		$refTable = new ReferentielMonstre();
		$taillesTable = new TailleMonstre();
		
		$refRowset = $refTable->findAll();
		$taillesRowset = $taillesTable->fetchall();
		
		$zoneTable = new Zone();
		
		$creationMonstresTable = new CreationMonstres();
		$creationMonstres = $creationMonstresTable->fetchAll(null, "id_fk_type_monstre_creation_monstres");
		$nbCreationMonstres = count($creationMonstres);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - nbCreationMonstres=" .$nbCreationMonstres);

		$typeMonstreTable = new TypeMonstre();
		$typeMonstres = $typeMonstreTable->fetchAllAvecTypeGroupe();
		$nbTypeMonstres = count($typeMonstres);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - nbTypeMonstres=" .$nbTypeMonstres);

		// selection des environnements / zones concernes
		$environnementIds = $this->getEnvironnementsConcernes($creationMonstres);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - nb environnement concernes=" .count($environnementIds));
		$zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - nb zones concernees=" .count($zones));
		
		$monstreTable = new Monstre();
		$tmp = "";
		
		$superficieZones = array();
		$superficieTotale = array();
		
		foreach($creationMonstres as $c) {
			// on recupere la supercifie totale de toutes les zones concernees par ce type
			foreach($zones as $z) {
				if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_monstres"]) {
					$superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
					if (array_key_exists($c["id_fk_type_monstre_creation_monstres"], $superficieTotale)) {
						$superficieTotale[$c["id_fk_type_monstre_creation_monstres"]] = $superficieTotale[$c["id_fk_type_monstre_creation_monstres"]] + ( $superficieZones[$z["id_zone"]] );
					} else {
						$superficieTotale[$c["id_fk_type_monstre_creation_monstres"]] = $superficieZones[$z["id_zone"]];
					}
				}
			}
		}
		
		foreach($creationMonstres as $c) {
			$t = null;
			foreach($typeMonstres as $type) {
				if ($c["id_fk_type_monstre_creation_monstres"] == $type["id_type_monstre"]) {
					$t = $type;
					break;
				}
			}
			
			if ($t != null) {
				Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - traitement du monstre ".$t["id_type_monstre"]. " nbMaxMonde(".$t["nb_creation_type_monstre"].") environnement(".$c["id_fk_environnement_creation_monstres"].") suptotal(". $superficieTotale[$c["id_fk_type_monstre_creation_monstres"]].")");
				foreach($zones as $z) {
					if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_monstres"]) {
						$tmp = "";
						$nbCreation = ceil($t["nb_creation_type_monstre"] * ($superficieZones[$z["id_zone"]] / $superficieTotale[$c["id_fk_type_monstre_creation_monstres"]]));
						$nbActuel = $monstreTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], $t["id_type_monstre"]);
						
						$aCreer = $nbCreation - $nbActuel;
						if ($aCreer <= 0) { 
							$tmp = " deja pleine";
						}
						Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - zone(".$z["id_zone"].") nbActuel:".$nbActuel. " max:".$nbCreation.$tmp. " supzone(".$superficieZones[$z["id_zone"]].") suptotal(". $superficieTotale[$c["id_fk_type_monstre_creation_monstres"]].")");
						if ($aCreer > 0) { 
							$retour .= $this->insert($t, $z, $aCreer, $refRowset, $taillesRowset, $villes);
						} else {
							$retour .= "zone(".$z["id_zone"].") pleine de monstre(".$t["id_type_monstre"].") nbActuel(".$nbActuel.") max(".$nbCreation."). ";
						}
					}
				}
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - calculCreation - exit -");
		return $retour;
	}
	
	private function getEnvironnementsConcernes($creationMonstres) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - getEnvironnementsConcernes - enter -");
		$environnementIds = null;
		foreach($creationMonstres as $n) {
			$environnementIds[$n["id_fk_environnement_creation_monstres"]] = $n["id_fk_environnement_creation_monstres"];
		}
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - getEnvironnementsConcernes - exit -");
		return $environnementIds;
	}
	
	private function insert($typeMonstre, $zone, $aCreer, $refRowset, $taillesMonstre, $villes) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - insert - enter - idtype(".$typeMonstre["id_type_monstre"].") idzone(".$zone['id_zone'].") nbACreer(".$aCreer.")");
		$retour = "monstre(".$typeMonstre["id_type_monstre"].") idzone(".$zone['id_zone'].") aCreer(".$aCreer."). ";
		
		$referenceCourante = $this->recupereReferenceMonstre($refRowset, $typeMonstre["id_type_monstre"]);
		$creation = true;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - insert - idtypeGroupe(".$referenceCourante["id_fk_type_groupe_monstre"].")");
		
		if ($referenceCourante["id_fk_type_groupe_monstre"] > 1) { //1 => type Solitaire

			for ($i = 1; $i < $aCreer; $i++) {
				$nb_membres = Bral_Util_De::get_de_specifique($referenceCourante["nb_membres_min_type_groupe_monstre"], $referenceCourante["nb_membres_max_type_groupe_monstre"]);
				$i = $i + $nb_membres;
				
				$x_min_groupe = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
				$y_min_groupe = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);
			
				if ($referenceCourante["id_fk_type_groupe_monstre"] > 2) { //2 => nuée : tous sur la même case
					$x_max_groupe = $x_min_groupe + 4;
					$y_max_groupe = $y_min_groupe + 4;
				} else {
					$x_max_groupe = $x_min_groupe;
					$y_max_groupe = $y_min_groupe;
				}
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

					$this->creationCalcul($villes, $refRowset, $referenceCourante, $taillesMonstre, $x_min_groupe, $x_max_groupe, $y_min_groupe, $y_max_groupe, $id_groupe, $est_role_a, $est_role_b);
				}
			}
		} else {
			// insertion de solitaires
			for ($i = 1; $i < $aCreer; $i++) {
				$this->creationCalcul($villes, $refRowset, $referenceCourante, $taillesMonstre, $zone["x_min_zone"], $zone["x_max_zone"], $zone["y_min_zone"], $zone["y_max_zone"]);
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationMonstres - insert - exit -");
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

	private function creationCalcul($villes, $refMonstre, $referenceCourante, $taillesMonstre, $x_min, $x_max, $y_min, $y_max, $id_groupe_monstre = null, $est_role_a = false, $est_role_b = false) {
		$id_fk_taille_monstre = $this->creationCalculTaille($taillesMonstre);

		$referenceCourante = $this->recupereReferenceMonstre($refMonstre, $referenceCourante["id_fk_type_ref_monstre"], $id_fk_taille_monstre);

		$id_fk_type_monstre = $referenceCourante["id_fk_type_ref_monstre"];
		$id_type_groupe_monstre = $referenceCourante["id_type_groupe_monstre"];

		$niveau_monstre = Bral_Util_De::get_de_specifique($referenceCourante["niveau_min_ref_monstre"], $referenceCourante["niveau_max_ref_monstre"]);
		$x_monstre = Bral_Util_De::get_de_specifique($x_min, $x_max);
		$y_monstre = Bral_Util_De::get_de_specifique($y_min, $y_max);
		
		$enVille = 0;
		
		foreach($villes as $v) {
			// on ne créé pas de monstre en ville
			if ($v["x_min_ville"] <= $x_monstre && $v["x_max_ville"] >= $x_monstre &&
				$v["y_min_ville"] <= $y_monstre && $v["y_max_ville"] >= $y_monstre) {
				$de = Bral_Util_De::get_1d2();
				if ($de == 1) {
					$enVille = 20;
				} else {
					$enVille = -20;
				}
				break;
			}
		}
		
		$x_monstre = $x_monstre + $enVille;
		$y_monstre = $y_monstre + $enVille;
		
		$tab = Bral_Monstres_VieMonstre::getTabXYRayon($niveau_monstre, $villes, $x_monstre, $y_monstre);
		$x_monstre = $tab["x_direction"];
		$y_monstre = $tab["y_direction"];
		
		$palissadeTable = new Palissade();
		$surPalissade = true;
		while($surPalissade) {
			$nb = $palissadeTable->countCase($x_monstre, $y_monstre);
			if ($nb < 1) {
				$surPalissade = false;
			} else {
				$de = Bral_Util_De::get_1d2();
				if ($de == 1) {
					$x_monstre = $x_monstre+$de;
					$y_monstre = $y_monstre+$de;
				} else {
					$x_monstre = $x_monstre-$de;
					$y_monstre = $y_monstre-$de;
				}
			}
		}
		
		if ($x_monstre <= $this->config->game->x_min) {
			$x_monstre = $this->config->game->x_min + 1;
		}
		if ($x_monstre >= $this->config->game->x_max) {
			$x_monstre = $this->config->game->x_max - 1;
		}
		if ($y_monstre <= $this->config->game->y_min) {
			$y_monstre = $this->config->game->y_min + 1;
		}
		if ($y_monstre >= $this->config->game->y_max) {
			$y_monstre = $this->config->game->y_max - 1;
		}
		
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
		$armure_naturelle_monstre = floor(($force_base_monstre + $vigueur_base_monstre) / 5);

		//DLA
		$dla_monstre = Bral_Util_ConvertDate::get_time_from_minutes(720 - 10 * $niveau_sagesse);
		$date_fin_tour_monstre = Bral_Util_ConvertDate::get_date_add_time_to_date(date("Y-m-d H:i:s"), $dla_monstre);

		//PV
		$pv_restant_monstre = 20 + $niveau_vigueur * 4;

		//Vue
		$vue_monstre = $referenceCourante["vue_ref_monstre"];

		$data = array(
			"id_fk_type_monstre" => $id_fk_type_monstre,
			"id_fk_taille_monstre" => $id_fk_taille_monstre,
			"id_fk_groupe_monstre" => $id_groupe_monstre,
			"x_monstre" => $x_monstre,
			"y_monstre" => $y_monstre,
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
			"pa_monstre" => $this->config->game->pa_max,
		);

		$monstreTable = new Monstre();
		$id_monstre = $monstreTable->insert($data);

		// mise à jour des roles
		if (($est_role_a === true) || ($est_role_b === true)) {
			if ($est_role_a) {
				$data = array(
					"id_role_a_groupe_monstre" => $id_monstre,
					"x_direction_groupe_monstre" => $x_monstre,
					"y_direction_groupe_monstre" => $y_monstre,
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
}