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
class Bral_Monstres_VieMonstre {
	private static $instance = null;
	private $monstre = null;
	private static $config = null;

	public static function getInstance() {
		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getInstance - enter");

		if (self::$instance == null) {
			Zend_Loader::loadClass("Crevasse");
			Zend_Loader::loadClass("Palissade");

			self::$config = Zend_Registry::get('config');
			self::$instance = new self();
			Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getInstance - nouvelle instance - exit");
			return self::$instance;
		} else {
			Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getInstance - instance existante - exit");
			return self::$instance;
		}
	}

	/**
	 * Constructeur privé. Utiliser getInstance().
	 */
	private function __construct() {}

	/**
	 * Déplacement du monstre vers une position.
	 *
	 * @param int $x_destination
	 * @param int $y_destination
	 * @return boolean : le monstre a bougé (true) ou non (false)
	 */
	public function deplacementMonstre($x_destination, $y_destination) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementMonstre ".$this->monstre["id_monstre"]."  - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::deplacementMonstre, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si le monstre est déjà dans la position
		if (($x_destination == $this->monstre["x_monstre"]) && ($y_destination == $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  en position");
			return false;
		}
		$modif = false;
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA restants pour idm:" .$this->monstre["id_monstre"]." : ".$this->monstre["pa_monstre"]);
		if ($this->monstre["pa_monstre"] == 0) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Le monstre ".$this->monstre["id_monstre"]." n'a plus de PA");
		}

		$palissadeTable = new Palissade();
		$x_min = $this->monstre["x_monstre"] - 12;
		$x_max = $this->monstre["x_monstre"] + 12;
		$y_min = $this->monstre["y_monstre"] - 12;
		$y_max = $this->monstre["y_monstre"] + 12;

		$palissades = $palissadeTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"]);

		$crevasseTable = new Crevasse();
		$crevasses = $crevasseTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"]);

		$this->tabValidationPalissadesCrevasses = null;
		for ($j = 12; $j >= -12; $j--) {
			for ($i = -12; $i <= 12; $i++) {
				$x = $this->monstre["x_monstre"] + $i;
				$y = $this->monstre["y_monstre"] + $j;
				$this->tabValidationPalissadesCrevasses[$x][$y] = true;
			}
		}
		foreach($palissades as $p) {
			$this->tabValidationPalissadesCrevasses[$p["x_palissade"]][$p["y_palissade"]] = false;
		}
		foreach($crevasses as $c) {
			$this->tabValidationPalissadesCrevasses[$c["x_crevasse"]][$c["y_crevasse"]] = false;
		}
			
		$pa_a_jouer = Bral_Util_De::get_de_specifique(0, $this->monstre["pa_monstre"]);
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") - nb pa a jouer=".$pa_a_jouer. " destination x=".$x_destination." y=".$y_destination);
		$nb_pa_joues = 0;
		
		while ((($x_destination != $this->monstre["x_monstre"]) || ($y_destination != $this->monstre["y_monstre"])) && ($nb_pa_joues < $pa_a_jouer)) {
			$x_monstre = $this->monstre["x_monstre"];
			$y_monstre = $this->monstre["y_monstre"];
			$x_offset = 0;
			$y_offset = 0;
			
			$modif = true;

			if ($this->monstre["x_monstre"] < $x_destination) {
				$x_monstre = $this->monstre["x_monstre"] + 1;
				$x_offset = +1;
			} else if ($this->monstre["x_monstre"] > $x_destination) {
				$x_monstre = $this->monstre["x_monstre"] - 1;
				$x_offset = -1;
			}
			if ($this->monstre["y_monstre"] < $y_destination) {
				$y_monstre = $this->monstre["y_monstre"] + 1;
				$y_offset = +1;
			} else if ($this->monstre["y_monstre"] > $y_destination) {
				$y_monstre = $this->monstre["y_monstre"] - 1;
				$y_offset = -1;
			}

			if ($this->tabValidationPalissadesCrevasses[$x_monstre][$y_monstre] == true) {
				$this->monstre["x_monstre"] = $x_monstre;
				$this->monstre["y_monstre"] = $y_monstre;
			} elseif ($this->tabValidationPalissadesCrevasses[$this->monstre["x_monstre"] + $x_offset][$this->monstre["y_monstre"]] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"]  + $x_offset;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"];
			} elseif ($this->tabValidationPalissadesCrevasses[$this->monstre["x_monstre"]][$this->monstre["y_monstre"] + $y_offset] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"] ;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"] + $y_offset;
			} else {
				if ($this->tabValidationPalissadesCrevasses[$x_monstre][$y_monstre] == false) {
					Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas de deplacement, cause palissade");
				}
			}

			$nb_pa_joues = $nb_pa_joues + 1;
			$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - 1;
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") nouvelle position x=".$this->monstre["x_monstre"]." y=".$this->monstre["y_monstre"].", pa restant=".$this->monstre["pa_monstre"]);
		}
		if ($modif === true) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") Modif true, appel updateMonstre()");
			$this->updateMonstre();
			$retour = true;
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") Modif false, pas appel updateMonstre()");
			$retour = false;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementMonstre - exit (".$retour.")");
	}

	public function calculFuite($view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuite (idm:".$this->monstre["id_monstre"].") - enter");
		$estFuite = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculFuite, monstre inconnu");
		}

		$this->calculTour();

		Zend_Loader::loadClass("Bral_Monstres_Competences_Factory");
		Zend_Loader::loadClass("TypeMonstreMCompetence");
		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findFuiteByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		$foo = null;
		if ($competences != null) {
			foreach($competences as $c) {
				$actionAttaque = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $foo, $view);
				$estFuite = $actionAttaque->action();
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuite - (idm:".$this->monstre["id_monstre"].") - exit");
		return $estFuite;
	}

	public function attaqueCible(&$cible, $view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible (idm:".$this->monstre["id_monstre"].") - enter");
		$koCible = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

		$this->calculTour();

		Zend_Loader::loadClass("Bral_Monstres_Competences_Factory");
		Zend_Loader::loadClass("TypeMonstreMCompetence");
		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findAttaqueByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		if ($competences != null) {
			foreach($competences as $c) {
				$actionAttaque = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $cible, $view);
				$koCible = $actionAttaque->action();
				if ($koCible) {
					break;
				}
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - (idm:".$this->monstre["id_monstre"].") - exit");
		return $koCible;
	}

	public function setMonstre($m) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setMonstre - enter");
		if ($m == null) {
			throw new Zend_Exception("Bral_Monstres_VieMonstre::setMonstre, monstre invalide");
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setMonstre - exit (id=".$m["id_monstre"].")");
		$this->monstre = $m;
	}
	
	public function getMonstre() {
		return $this->monstre;
	}

	private function calculTour() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour (idm:".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculTour, monstre invalide");
		}

		$date_courante = date("Y-m-d H:i:s");
		if ($date_courante > $this->monstre["date_fin_tour_monstre"]) { // nouveau tour
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") nouveau tour");
			$this->monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($this->monstre["date_fin_tour_monstre"], $this->monstre["duree_prochain_tour_monstre"]);
			if ($this->monstre["date_fin_tour_monstre"]  < $date_courante) {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") date_fin_tour_monstre avant calcul:".$this->monstre["date_fin_tour_monstre"]. " duree prochain:".$this->monstre["duree_prochain_tour_monstre"]);
				$this->monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, $this->monstre["duree_prochain_tour_monstre"]);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") date_fin_tour_monstre calculee:".$this->monstre["date_fin_tour_monstre"]);
			}
			$this->monstre["duree_prochain_tour_monstre"] = $this->monstre["duree_base_tour_monstre"];
			$this->monstre["pa_monstre"] = self::$config->game->monstre->pa_max;

			$this->calulRegeneration();
			$this->monstre["regeneration_malus_monstre"] = 0;
			$this->monstre["vue_malus_monstre"] = 0;
			$this->monstre["force_bm_monstre"] = 0;
			$this->monstre["agilite_bm_monstre"] = - $this->monstre["agilite_malus_monstre"];
			$this->monstre["agilite_malus_monstre"] = 0;
			$this->monstre["sagesse_bm_monstre"] = 0;
			$this->monstre["vigueur_bm_monstre"] = 0;
			$this->monstre["bm_attaque_monstre"] = 0;
			$this->monstre["bm_defense_monstre"] = 0;
			$this->monstre["bm_degat_monstre"] = 0;
			$this->monstre["nb_dla_jouees_monstre"] = $this->monstre["nb_dla_jouees_monstre"] + 1;

			Zend_Loader::loadClass("Bral_Util_EffetsPotion");
			$effetsPotions = Bral_Util_EffetsPotion::calculPotionMonstre($this->monstre);
			if (count($effetsPotions) > 0) {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - (idm:".$this->monstre["id_monstre"].") des potions sur le monstre ont ete trouvee(s). Cf. log potion.log");
			} else {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - (idm:".$this->monstre["id_monstre"].") aucune potion sur le monstre n'a ete trouvee. Cf. log potion.log");
			}

			Zend_Loader::loadClass("Bral_Util_Effets");
			$effets = Bral_Util_Effets::calculEffetMonstre($this->monstre);
			if (count($effets) > 0) {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - (idm:".$this->monstre["id_monstre"].") des effets sur le monstre ont ete trouve(s).");
			} else {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - (idm:".$this->monstre["id_monstre"].") aucun effet sur le monstre n'a ete trouve.");
			}

			$this->updateMonstre();

		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") pas de nouveau tour");
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour (idm:".$this->monstre["id_monstre"].") - exit");
	}

	private function calulRegeneration() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration (idm:".$this->monstre["id_monstre"].") - enter");

		if ($this->monstre["pv_restant_monstre"] < $this->monstre["pv_max_monstre"]) {
			$this->monstre["regeneration_monstre"] = floor($this->monstre["vigueur_base_monstre"] / 4) + 1;
			$jet = Bral_Util_Vie::calculRegenerationMonstre($this->monstre);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - jet de regeneration:".$jet);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration (idm:".$this->monstre["id_monstre"].") - exit");
	}

	private function updateMonstre() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre == null) {
			new Zend_Exception(get_class($this)." - miseAJourMonstre, monstre inconnu");
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") (PA:".$this->monstre["pa_monstre"].")");
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["date_fin_tour_monstre"].") (Date fin tour:".$this->monstre["date_fin_tour_monstre"].")");

		$monstreTable = new Monstre();
		$data = array(
			'pa_monstre' => $this->monstre["pa_monstre"],
			'x_monstre' => $this->monstre["x_monstre"],
			'y_monstre' => $this->monstre["y_monstre"],
			'z_monstre' => $this->monstre["z_monstre"],
			'x_direction_monstre' => $this->monstre["x_direction_monstre"],
			'y_direction_monstre' => $this->monstre["y_direction_monstre"],
			'nb_kill_monstre' => $this->monstre["nb_kill_monstre"],
			'date_fin_tour_monstre' => $this->monstre["date_fin_tour_monstre"],
			'duree_prochain_tour_monstre' => $this->monstre["duree_prochain_tour_monstre"],
			'id_fk_hobbit_cible_monstre' => $this->monstre["id_fk_hobbit_cible_monstre"],
			'date_a_jouer_monstre' => null,
			'regeneration_monstre' => $this->monstre["regeneration_monstre"],
			'regeneration_malus_monstre' => $this->monstre["regeneration_malus_monstre"],
			'pv_restant_monstre' => $this->monstre["pv_restant_monstre"],
			'regeneration_malus_monstre' => $this->monstre["regeneration_malus_monstre"],
			'vue_malus_monstre' => $this->monstre["vue_malus_monstre"],
			'force_bm_monstre' => $this->monstre["force_bm_monstre"],
			'agilite_bm_monstre' => $this->monstre["agilite_bm_monstre"],
			'agilite_malus_monstre' => $this->monstre["agilite_malus_monstre"],
			'sagesse_bm_monstre' => $this->monstre["sagesse_bm_monstre"],
			'vigueur_bm_monstre' => $this->monstre["vigueur_bm_monstre"],
			'nb_dla_jouees_monstre' => $this->monstre["nb_dla_jouees_monstre"],
		);

		$where = "id_monstre=".$this->monstre["id_monstre"];
		$monstreTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") - exit");
	}

	/*
	 * Mort d'un monstre : mise à jour table monstre
	 * Drop Rune
	 */
	public function mortMonstreDb($id_monstre, $effetMotD, $effetMotH, $niveauHobbit, $view) {

		if ($id_monstre == null || (int)$id_monstre<=0 ) {
			throw new Zend_Exception(get_class($this)."::mortMonstreDb id_monstre inconnu:".$id_monstre);
		}

		Zend_Loader::loadClass("Monstre");

		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($id_monstre);
		$monstre = $monstreRowset;

		if ($monstre == null || $monstre["id_monstre"] == null || $monstre["id_monstre"] == "") {
			throw new Zend_Exception(get_class($this)."::mortMonstreDb monstre inconnu");
		}

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_1d20();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

		$data = array(
			"date_fin_cadavre_monstre" => $dateFin,
			"est_mort_monstre" => "oui",
			"id_fk_groupe_monstre" => null,
		);

		$where = "id_monstre=".$id_monstre;
		$monstreTable->update($data, $where);

		Zend_Loader::loadClass("Bral_Util_Rune");
		$tabGains["gainRune"] = Bral_Util_Rune::dropRune($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["niveau_monstre"], $niveauHobbit, $monstre["id_fk_type_groupe_monstre"], $effetMotD, $id_monstre);
		$tabGains["gainCastars"] = $this->dropCastars($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["niveau_monstre"], $effetMotH, $niveauHobbit, $monstre["id_fk_type_groupe_monstre"]);

		$tabGains["finDonjon"] = null;

		Zend_Loader::loadClass("TailleMonstre");
		if ($monstre["id_fk_taille_monstre"] == TailleMonstre::ID_TAILLE_BOSS) {
			Zend_Loader::loadClass("Bral_Util_Donjon");
			$tabGains["finDonjon"] = Bral_Util_Donjon::dropGainsEtUpdateDonjon($monstre["id_fk_donjon_monstre"], $monstre, $niveauHobbit, $effetMotD, $view);
		}

		return $tabGains;
	}

	private function dropCastars($x, $y, $z, $niveauMonstre, $effetMotH, $niveauHobbit, $idTypeGroupeMonstre) {

		if ($idTypeGroupeMonstre == self::$config->game->groupe_monstre->type->gibier) {
			// pas de drop de castar pour les gibiers
			return;
		}

		$nbCastars = 15 * $niveauMonstre + Bral_Util_De::get_1d5();
		if ($effetMotH == true) {
			$nbCastars = $nbCastars * 2;
		}

		if ((10 + 2 * ($niveauMonstre - $niveauHobbit) + $niveauMonstre) <= 0) {
			$nbCastars = $nbCastars / 2;
		} else {
			$nbCastars = $nbCastars + Bral_Util_De::get_2d10();
		}

		$nbCastars = round($nbCastars);

		Zend_Loader::loadClass("Element");
		$elementTable = new Element();
		$data = array(
				"quantite_castar_element" => $nbCastars,
				"x_element" => $x,
				"y_element" => $y,
				"z_element" => $z,
		);
		$elementTable->insertOrUpdate($data);

		return $nbCastars;
	}

	public static function getTabXYRayon($idZoneNid, $niveau, $directionX, $directionY, $xMin, $xMax, $yMin, $yMax, $idMonstre) {
		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - enter - (idm:".$idMonstre.") idZoneNid:".$idZoneNid." niveau=".$niveau." directionX=".$directionX." directionY=".$directionY. "  xMin:".$xMin." , xMax:".$xMax." , yMin:".$yMin.", yMax:".$yMax);

		$tab["x_direction"] = $directionX;
		$tab["y_direction"] = $directionY;

		Zend_Loader::loadClass("ZoneNid");
		$zoneNidTable = new ZoneNid();

		$zone = $zoneNidTable->findById($idZoneNid);
		if (count($zone) != 1) {
			throw new Zend_Exception(" Zone Nid Invalide idZoneNid:".$idZoneNid);
		}
		$zone = $zone[0];

		if ($tab["x_direction"] < $xMin) {
			$tab["x_direction"] = $xMin + Bral_Util_De::get_de_specifique(0, 5);
		}
		if ($tab["x_direction"] > $xMax) {
			$tab["x_direction"] = $xMax - Bral_Util_De::get_de_specifique(0, 5);
		}
		if ($tab["y_direction"] < $yMin) {
			$tab["y_direction"] = $yMin + Bral_Util_De::get_de_specifique(0, 5);
		}
		if ($tab["y_direction"] > $yMax) {
			$tab["y_direction"] = $yMax - Bral_Util_De::get_de_specifique(0, 5);
		}

		if ($zone["est_ville_zone_nid"] == "oui") {

			$rayonMin = $niveau * 3;
			$rayonMax = $niveau * 3 + 20;

			$xCentreVille = $zone["x_min_zone_nid"] + ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) / 2;
			$yCentreVille = $zone["y_min_zone_nid"] + ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]) / 2;

			if ($tab["x_direction"] < $xCentreVille) { // à gauche de la ville
				if ($tab["x_direction"] > $xCentreVille - $rayonMin) { // à l'intérieur du rayon à gauche
					$tab["x_direction"] = $xCentreVille - $rayonMin - Bral_Util_De::get_de_specifique(0, 5);
				} else if ($tab["x_direction"] < $xCentreVille - $rayonMax) { // à l'extérieur du rayon à gauche
					$tab["x_direction"] = $xCentreVille - $rayonMax - Bral_Util_De::get_de_specifique(0, 5);
				}
			} else { // à droite de la ville
				if ($tab["x_direction"] < $xCentreVille + $rayonMin) { // à l'intérieur du rayon à droite
					$tab["x_direction"] = $xCentreVille + $rayonMin + Bral_Util_De::get_de_specifique(0, 5);
				} else if ($tab["x_direction"] > $xCentreVille + $rayonMax) { // à l'extérieur du rayon à gauche
					$tab["x_direction"] = $xCentreVille + $rayonMax + Bral_Util_De::get_de_specifique(0, 5);
				}
			}

			if ($tab["y_direction"] < $yCentreVille) { // au bas de la ville
				if ($tab["y_direction"] > $yCentreVille - $rayonMin) { // à l'intérieur du rayon en bas
					$tab["y_direction"] = $yCentreVille - $rayonMin - Bral_Util_De::get_de_specifique(0, 5);
				} else if ($tab["y_direction"] < $yCentreVille - $rayonMax) { // à l'extérieur du rayon en bas
					$tab["y_direction"] = $yCentreVille - $rayonMax - Bral_Util_De::get_de_specifique(0, 5);
				}
			} else { // en haut de la ville
				if ($tab["y_direction"] < $yCentreVille + $rayonMin) { // à l'intérieur du rayon en haut
					$tab["y_direction"] = $yCentreVille + $rayonMin + Bral_Util_De::get_de_specifique(0, 5);
				} else if ($tab["y_direction"] > $yCentreVille + $rayonMax) { // à l'extérieur du rayon en haut
					$tab["y_direction"] = $yCentreVille + $rayonMax + Bral_Util_De::get_de_specifique(0, 5);
				}
			}
		}

		// si toutefois on sort du périmètre (parce que le périmètre à une largeur de moins de 5 cases), on re-contrôle
		if ($tab["x_direction"] < $xMin) {
			$tab["x_direction"] = $xMin;
		} else if ($tab["x_direction"] > $xMax) {
			$tab["x_direction"] = $xMax;
		}
		if ($tab["y_direction"] < $yMin) {
			$tab["y_direction"] = $yMin;
		} else if ($tab["y_direction"] > $yMax) {
			$tab["y_direction"] = $yMax;
		}

		$config = Zend_Registry::get('config');

		if ($tab["x_direction"] <= $config->game->x_min) {
			$tab["x_direction"] = -$config->game->x_min;
		}
		if ($tab["x_direction"] >= $config->game->x_max) {
			$tab["x_direction"] = -$config->game->x_max;
		}
		if ($tab["y_direction"] <= $config->game->y_min) {
			$tab["y_direction"] = -$config->game->y_min;
		}
		if ($tab["y_direction"] >= $config->game->y_max) {
			$tab["y_direction"] = -$config->game->y_max;
		}

		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - exit - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);

		return $tab;

	}
}
