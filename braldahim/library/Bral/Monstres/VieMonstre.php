<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_VieMonstre {
	private static $instance = null;
	private $monstre = null;
	private static $config = null;

	public static function getInstance() {
		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getInstance - enter");

		if (self::$instance == null) {
			Zend_Loader::loadClass("Crevasse");
			Zend_Loader::loadClass("Eau");
			Zend_Loader::loadClass("Zone");
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

		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA restants pour idm:" .$this->monstre["id_monstre"]." : ".$this->monstre["pa_monstre"]);
		if ($this->monstre["pa_monstre"] == 0) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Le monstre ".$this->monstre["id_monstre"]." n'a plus de PA");
		}

		if ($this->monstre["z_monstre"] < 0) {
			$modif = $this->deplacementDijkstraMonstre($x_destination, $y_destination);
		} else {
			$modif = $this->deplacementNormalMonstre($x_destination, $y_destination);
		}

		if ($modif === true) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") Modif true");
			$retour = true;
		} else if($modif === false) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") Modif false");
			$retour = false;
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") Modif null");
			$this->monstre["x_direction_monstre"] = $this->monstre["x_monstre"];
			$this->monstre["y_direction_monstre"] = $this->monstre["y_monstre"];
			$retour = null;
		}
		// mise à jour du monstre, quoi qu'il arrive
		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementMonstre - (idm:".$this->monstre["id_monstre"].") - exit (".$retour.")");
	}

	/**
	 * Déplacement du monstre vers une position.
	 *
	 * @param int $x_destination
	 * @param int $y_destination
	 * @return boolean : le monstre a bougé (true) ou non (false)
	 */
	private function deplacementNormalMonstre($x_destination, $y_destination) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementNormalMonstre ".$this->monstre["id_monstre"]."  - enter");

		$modif = false;

		$palissadeTable = new Palissade();
		$x_min = $this->monstre["x_monstre"] - 12;
		$x_max = $this->monstre["x_monstre"] + 12;
		$y_min = $this->monstre["y_monstre"] - 12;
		$y_max = $this->monstre["y_monstre"] + 12;

		$palissades = $palissadeTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"]);

		$crevasseTable = new Crevasse();
		$crevasses = $crevasseTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"]);

		$eauTable = new Eau();
		$eaux = $eauTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"], false);

		$zoneTable = new Zone();

		$this->tabValidation = null;
		$tabEaux = null;
		for ($j = 12; $j >= -12; $j--) {
			for ($i = -12; $i <= 12; $i++) {
				$x = $this->monstre["x_monstre"] + $i;
				$y = $this->monstre["y_monstre"] + $j;
				$this->tabValidation[$x][$y] = true;
				$tabEaux[$x][$y] = false;
			}
		}
		foreach($palissades as $p) {
			$this->tabValidation[$p["x_palissade"]][$p["y_palissade"]] = false;
		}

		foreach($eaux as $e) {
			$tabEaux[$e["x_eau"]][$e["y_eau"]] = true;
		}

		foreach($crevasses as $c) {
			$this->tabValidation[$c["x_crevasse"]][$c["y_crevasse"]] = false;
		}
			
		$pa_a_jouer = Bral_Util_De::get_de_specifique(0, $this->monstre["pa_monstre"]);
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") - nb pa a jouer=".$pa_a_jouer. " destination x=".$x_destination." y=".$y_destination);
		$nb_pa_joues = 0;

		while ((($x_destination != $this->monstre["x_monstre"]) || ($y_destination != $this->monstre["y_monstre"])) && ($nb_pa_joues < $pa_a_jouer)) {

			$coutPA = $this->calculCoutPADeplacement($zoneTable, $tabEaux); // on calcule le coût en PA du déplacement
			if ($coutPA > $this->monstre["pa_monstre"]) {
				// si le monstre n'a plus assez de PA, on sort
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas assez de PA pour se deplacer paRestant:".$this->monstre["pa_monstre"].". cout:".$coutPA);
				break;
			}

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

			if ($this->tabValidation[$x_monstre][$y_monstre] == true) {
				$this->monstre["x_monstre"] = $x_monstre;
				$this->monstre["y_monstre"] = $y_monstre;
			} elseif ($this->tabValidation[$this->monstre["x_monstre"] + $x_offset][$this->monstre["y_monstre"]] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"]  + $x_offset;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"];
			} elseif ($this->tabValidation[$this->monstre["x_monstre"]][$this->monstre["y_monstre"] + $y_offset] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"] ;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"] + $y_offset;
			} else {
				if ($this->tabValidation[$x_monstre][$y_monstre] == false) {
					Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas de deplacement, cause palissade");
					$modif = null;
				}
			}

			$nb_pa_joues = $nb_pa_joues + $coutPA;

			$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $coutPA;
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") nouvelle position x=".$this->monstre["x_monstre"]." y=".$this->monstre["y_monstre"].", pa restant=".$this->monstre["pa_monstre"]);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementNormalMonstre - (idm:".$this->monstre["id_monstre"].") - exit (".$modif.")");
		return $modif;
	}


	/**
	 * Déplacement du monstre vers une position.
	 *
	 * @param int $x_destination
	 * @param int $y_destination
	 * @return boolean : le monstre a bougé (true) ou non (false)
	 */
	private function deplacementDijkstraMonstre($x_destination, $y_destination) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementDijkstraMonstre ".$this->monstre["id_monstre"]."  - enter");

		$modif = false;

		$nbCases = 20; // 10 cases de déplacement destination max + 10 cases fuite

		$x_min = $this->monstre["x_monstre"] - $nbCases;
		$x_max = $this->monstre["x_monstre"] + $nbCases;
		$y_min = $this->monstre["y_monstre"] - $nbCases;
		$y_max = $this->monstre["y_monstre"] + $nbCases;

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($nbCases, $this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], null, true);

		$tabValide = null;
		$numeroDestination = null;
		$tabChemins = array();
		$numero = -1;
		for ($j = $y_max ; $j >= $y_min ; $j--) {
			for ($i = $x_min ; $i <= $x_max ; $i++) {
				$numero++;
				$tabValide[$i][$j] = true;
				if ($dijkstra->getDistance($numero) > 1) {
					$tabValide[$i][$j] = false;
				}
				if ($x_destination == $i && $y_destination == $j) {
					$numeroDestination = $numero;
				}
				$tabChemins[$numero] = array('x' => $i, 'y' => $j);
			}
		}

		$tabCheminValide = $dijkstra->getShortestPath($numeroDestination);

		$pa_a_jouer = Bral_Util_De::get_de_specifique(0, $this->monstre["pa_monstre"]);
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") - nb pa a jouer=".$pa_a_jouer. " destination x=".$x_destination." y=".$y_destination);
		$nb_pa_joues = 0;

		if ($tabCheminValide == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas de deplacement, destination impossible 1");
			$modif = null;
			$pa_a_jouer = 0;
		}

		if (array_key_exists($x_destination, $tabValide) && array_key_exists($y_destination, $tabValide[$x_destination])) {
			if ($tabValide[$x_destination][$y_destination] == false) {
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas de deplacement, destination impossible 2");
				$modif = null;
				$pa_a_jouer = 0;
			}
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas de deplacement, destination impossible 3");
			$modif = null;
			$pa_a_jouer = 0;
		}

		$eauTable = new Eau();
		$eaux = $eauTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"], false);

		$tabEaux = null;
		for ($j = 12; $j >= -12; $j--) {
			for ($i = -12; $i <= 12; $i++) {
				$x = $this->monstre["x_monstre"] + $i;
				$y = $this->monstre["y_monstre"] + $j;
				$tabEaux[$x][$y] = false;
			}
		}

		foreach($eaux as $e) {
			$tabEaux[$e["x_eau"]][$e["y_eau"]] = true;
		}

		$numeroPosition = 0;

		$zoneTable = new Zone();

		while ((($x_destination != $this->monstre["x_monstre"]) || ($y_destination != $this->monstre["y_monstre"])) && ($nb_pa_joues < $pa_a_jouer)) {

			$coutPA = $this->calculCoutPADeplacement($zoneTable, $tabEaux); // on calcule le coût en PA du déplacement
			if ($coutPA > $this->monstre["pa_monstre"]) {
				// si le monstre n'a plus assez de PA, on sort
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre ".$this->monstre["id_monstre"]."  pas assez de PA pour se deplacer paRestant:".$this->monstre["pa_monstre"].". cout:".$coutPA);
				break;
			}

			$numeroPosition++;
			$numeroCase = $tabCheminValide[$numeroPosition];

			$this->monstre["x_monstre"] = $tabChemins[$numeroCase]["x"];
			$this->monstre["y_monstre"] = $tabChemins[$numeroCase]["y"];
			$modif = true;

			$nb_pa_joues = $nb_pa_joues + $coutPA;

		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementDijkstraMonstre - (idm:".$this->monstre["id_monstre"].") - exit (".$modif.")");
		return $modif;
	}

	/**
	 * Calcul le coût de déplacement en PA du monstre.
	 * @param unknown_type $tabEaux
	 * @return coût en PA
	 */
	private function calculCoutPADeplacement($zoneTable, $tabEaux) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculCoutPADeplacement (idm:".$this->monstre["id_monstre"].") - enter");

		if ($tabEaux[$this->monstre["x_monstre"]][$this->monstre["y_monstre"]] == true) {
			$nbPa = 6;
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") 6 PA utilises pour deplacement sur eau");
		} else {

			$zone = $zoneTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);
			$case = $zone[0];

			switch($case["nom_systeme_environnement"]) {
				case "bosquet" :
				case "caverne" :
				case "gazon" :
				case "plaine" :
				case "mine" :
					$nbPa = 1;
					break;
				case "marais" :
				case "montagne" :
					$nbPa = 2;
					break;
				default:
					throw new Zend_Exception(get_class($this)."::environnement invalide :".$case["nom_systeme_environnement"]);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculCoutPADeplacement - (idm:".$this->monstre["id_monstre"].") nbPa:".$nbPa." - exit");
		return $nbPa;
	}

	public function calculFuite($view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuite (idm:".$this->monstre["id_monstre"].") - enter");
		$estFuite = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculFuite, monstre inconnu");
		}

		$this->calculTour();

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

	public function calculReperage($view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperage (idm:".$this->monstre["id_monstre"].") - enter");

		$reperageCible = null;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculReperage, monstre inconnu");
		}

		$this->calculTour();

		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findReperageByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		$foo = null;
		if ($competences != null) {
			foreach($competences as $c) {
				$actionReperage = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $foo, $view);
				$reperageCible = $actionReperage->action();
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperage - (idm:".$this->monstre["id_monstre"].") - exit");
		return $reperageCible;
	}

	public function attaqueCible(&$cible, $view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible (idm:".$this->monstre["id_monstre"].") cible(".$cible["id_braldun"].") - enter");
		$koCible = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

		$this->calculTour();

		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findAttaqueByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		if ($competences != null) {
			foreach($competences as $c) {
				$actionAttaque = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $cible, $view);
				$koCible = $actionAttaque->action();
				if ($koCible || $this->monstre["pv_restant_monstre"] <= 0) {
					break;
				}
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - (idm:".$this->monstre["id_monstre"].") - exit");
		return $koCible;
	}

	public function calculPostAll($view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPostAll (idm:".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculPostAll, monstre inconnu");
		}

		$this->calculTour();

		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findPostAllByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		$foo = null;
		if ($competences != null) {
			foreach($competences as $c) {
				$actionPostAll = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $foo, $view);
				$actionPostAll->action();
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPostAll - (idm:".$this->monstre["id_monstre"].") - exit");
		return;
	}
	
	public function calculDeplacement($view, $estFuite) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDeplacement (idm:".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculDeplacement, monstre inconnu");
		}

		$this->calculTour();
		$retour = false;

		$typeMonstreMCompetence = new TypeMonstreMCompetence();

		// Choix de l'action dans mcompetences
		$competences = $typeMonstreMCompetence->findDeplacementByIdTypeGroupe($this->monstre["id_fk_type_monstre"]);
		$foo = null;
		if ($competences != null) {
			foreach($competences as $c) {
				$actionDeplacement = Bral_Monstres_Competences_Factory::getAction($c, $this->monstre, $foo, $view);
				$actionDeplacement->setEstFuite($estFuite);
				$actionDeplacement->action();
				$retour = true;
			}
		}

		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDeplacement - (idm:".$this->monstre["id_monstre"].") - exit");
		return $retour;
	}

	public function setMonstre($m) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setMonstre - enter - (idm:".$m["id_monstre"].")");
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

			$this->calculDureeProchainTour();

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
			$this->monstre["force_bm_monstre"] = $this->monstre["force_bm_init_monstre"];
			$this->monstre["agilite_bm_monstre"] = $this->monstre["agilite_bm_init_monstre"];
			$this->monstre["agilite_malus_monstre"] = $this->monstre["agilite_malus_monstre"];
			$this->monstre["sagesse_bm_monstre"] = $this->monstre["sagesse_bm_init_monstre"];
			$this->monstre["vigueur_bm_monstre"] = $this->monstre["vigueur_bm_init_monstre"];
			$this->monstre["bm_attaque_monstre"] = $this->monstre["bm_init_attaque_monstre"];
			$this->monstre["bm_defense_monstre"] = $this->monstre["bm_init_defense_monstre"];
			$this->monstre["bm_degat_monstre"] = $this->monstre["bm_init_degat_monstre"];
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

	private function calculDureeProchainTour() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDureeProchainTour (idm:".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre["pv_restant_monstre"] < $this->monstre["pv_max_monstre"]) {
			$nbMin = floor(Bral_Util_ConvertDate::getMinuteFromHeure($this->monstre["duree_prochain_tour_monstre"]) / (4 * $this->monstre["pv_max_monstre"])) * ($this->monstre["pv_max_monstre"] - $this->monstre["pv_restant_monstre"]);
			$total = $nbMin + Bral_Util_ConvertDate::getMinuteFromHeure($this->monstre["duree_prochain_tour_monstre"]);
			$this->monstre["duree_prochain_tour_monstre"] = Bral_Util_ConvertDate::getHeureFromMinute($total);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDureeProchainTour (idm:".$this->monstre["id_monstre"].") minutesadd:$nbMin total:$total -> ".$this->monstre["duree_prochain_tour_monstre"]." exit");
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDureeProchainTour (idm:".$this->monstre["id_monstre"].") - exit");
	}

	private function calulRegeneration() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration (idm:".$this->monstre["id_monstre"].") - enter");

		if ($this->monstre["pv_restant_monstre"] < $this->monstre["pv_max_monstre"]) {
			$this->monstre["regeneration_monstre"] = floor($this->monstre["vigueur_base_monstre"] / 4) + 1;
			$jet = Bral_Util_Vie::calculRegenerationMonstre($this->monstre);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - jet de regeneration:".$jet);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration (idm:".$this->monstre["id_monstre"].") - exit");
	}

	private function updateMonstre() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (".$this->monstre["id_monstre"].") - enter");
		if ($this->monstre == null) {
			new Zend_Exception(get_class($this)." - miseAJourMonstre, monstre inconnu");
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") (PA:".$this->monstre["pa_monstre"].")");
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") (Date fin tour:".$this->monstre["date_fin_tour_monstre"].")");
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") (x:".$this->monstre["x_monstre"].", y:".$this->monstre["y_monstre"].", x_direction:".$this->monstre["x_direction_monstre"].", y_direction:".$this->monstre["y_direction_monstre"].")");

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
			'id_fk_braldun_cible_monstre' => $this->monstre["id_fk_braldun_cible_monstre"],
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
			'bm_attaque_monstre' => $this->monstre["bm_attaque_monstre"],
			'bm_defense_monstre' => $this->monstre["bm_defense_monstre"],
			'bm_degat_monstre' => $this->monstre["bm_degat_monstre"],
		);

		$where = "id_monstre=".$this->monstre["id_monstre"];
		$monstreTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre (idm:".$this->monstre["id_monstre"].") - exit");
	}

	/*
	 * Mort d'un monstre : mise à jour table monstre
	 * Drop Rune
	 */
	public function mortMonstreDb($id_monstre, $effetMotD, $effetMotH, $braldun, $view) {

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

		$idButin = null;
		// pas de butin pour les gibiers
		if ($monstre["id_fk_type_groupe_monstre"] != self::$config->game->groupe_monstre->type->gibier) {
			Zend_Loader::loadClass("Bral_Util_Butin");
			$idButin = Bral_Util_Butin::nouveau($braldun->id_braldun, $monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"]);
		}

		Zend_Loader::loadClass("Bral_Util_Rune");
		$tabGains["gainRune"] = Bral_Util_Rune::dropRune($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["niveau_monstre"], $braldun->niveau_braldun, $monstre["id_fk_type_groupe_monstre"], $effetMotD, $id_monstre, $idButin);
		$tabGains["gainCastars"] = $this->dropCastars($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["niveau_monstre"], $effetMotH, $braldun->niveau_braldun, $monstre["id_fk_type_groupe_monstre"], $idButin);

		$tabGains["finDonjon"] = null;
		$s = "";
		if ($tabGains["gainCastars"] > 1) {
			$s = "s";
		}
		if ($idButin != null) {
			$tabGains["butin"] = "Butin n°".$idButin." : ".$tabGains["gainCastars"]." castar".$s;

			if ($tabGains["gainRune"] != false) {
				$tabGains["butin"] = $tabGains["butin"] . " et rune n°".$tabGains["gainRune"];
			}
		}

		Zend_Loader::loadClass("TailleMonstre");
		if ($monstre["id_fk_taille_monstre"] == TailleMonstre::ID_TAILLE_BOSS) {
			Zend_Loader::loadClass("Bral_Util_Donjon");
			$tabGains["finDonjon"] = Bral_Util_Donjon::dropGainsEtUpdateDonjon($monstre["id_fk_donjon_monstre"], $monstre, $braldun->niveau_braldun, $effetMotD, $view);
		}

		return $tabGains;
	}

	private function dropCastars($x, $y, $z, $niveauMonstre, $effetMotH, $niveauBraldun, $idTypeGroupeMonstre, $idButin) {

		if ($idTypeGroupeMonstre == self::$config->game->groupe_monstre->type->gibier) {
			// pas de drop de castar pour les gibiers
			return;
		}

		$nbCastars = 15 * $niveauMonstre + Bral_Util_De::get_1d5();
		if ($effetMotH == true) {
			$nbCastars = $nbCastars * 2;
		}

		if ((10 + 2 * ($niveauMonstre - $niveauBraldun) + $niveauMonstre) <= 0) {
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
				"id_fk_butin_element" => $idButin,
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
		if (count($zone) != 1) { // dans le cas où les monstres sont créés sans zone de nid
			//throw new Zend_Exception(" Zone Nid Invalide idZoneNid:".$idZoneNid);
			$zone = null;
		} else {
			$zone = $zone[0];
		}

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

		if ($zone != null && $zone["est_ville_zone_nid"] == "oui") {

			if ($niveau <= 18) { // niveau <= 18, on limite le min à 54 cases, le max à 74 cases
				$rayonMin = 5 + $niveau * 3;
				$rayonMax = $niveau * 3 + 20;
			} else { // au delà, tous les types de monstres peuvent circuler
				$rayonMin = 50;
				$rayonMax = 100;
			}

			$xCentreVille = $zone["x_min_zone_nid"] + ($zone["x_max_zone_nid"] - $zone["x_min_zone_nid"]) / 2;
			$yCentreVille = $zone["y_min_zone_nid"] + ($zone["y_max_zone_nid"] - $zone["y_min_zone_nid"]) / 2;
			$xOk = true;
			$yOk = true;

			if ($tab["x_direction"] < $xCentreVille) { // à gauche de la ville
				if ($tab["x_direction"] > $xCentreVille - $rayonMin) { // à l'intérieur du rayon à gauche
					$xOk = false;
				} else if ($tab["x_direction"] < $xCentreVille - $rayonMax) { // à l'extérieur du rayon à gauche
					$xOk = false;
				}
			} else { // à droite de la ville
				if ($tab["x_direction"] < $xCentreVille + $rayonMin) { // à l'intérieur du rayon à droite
					$xOk = false;
				} else if ($tab["x_direction"] > $xCentreVille + $rayonMax) { // à l'extérieur du rayon à gauche
					$xOk = false;
				}
			}

			if ($tab["y_direction"] < $yCentreVille) { // au bas de la ville
				if ($tab["y_direction"] > $yCentreVille - $rayonMin) { // à l'intérieur du rayon en bas
					$yOk = false;
				} else if ($tab["y_direction"] < $yCentreVille - $rayonMax) { // à l'extérieur du rayon en bas
					$yOk = false;
				}
			} else { // en haut de la ville
				if ($tab["y_direction"] < $yCentreVille + $rayonMin) { // à l'intérieur du rayon en haut
					$yOk = false;
				} else if ($tab["y_direction"] > $yCentreVille + $rayonMax) { // à l'extérieur du rayon en haut
					$yOk = false;
				}
			}

			if ($xOk == false && $yOk == false) { // si x et y sont faux, on bouge qu'un des deux
				$de = Bral_Util_De::get_1D100();

				if ($de <= 50) {
					if ($tab["x_direction"] < $xCentreVille) { // à gauche de la ville
						if ($tab["x_direction"] > $xCentreVille - $rayonMin) { // à l'intérieur du rayon à gauche
							$tab["x_direction"] = $xCentreVille - $rayonMin - Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - A - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						} else if ($tab["x_direction"] < $xCentreVille - $rayonMax) { // à l'extérieur du rayon à gauche
							$tab["x_direction"] = $xCentreVille - $rayonMax - Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - B - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						}
					} else { // à droite de la ville
						if ($tab["x_direction"] < $xCentreVille + $rayonMin) { // à l'intérieur du rayon à droite
							$xOk = false;
							$tab["x_direction"] = $xCentreVille + $rayonMin + Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - C - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						} else if ($tab["x_direction"] > $xCentreVille + $rayonMax) { // à l'extérieur du rayon à gauche
							$xOk = false;
							$tab["x_direction"] = $xCentreVille + $rayonMax + Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - D - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						}
					}
				} else {
					if ($tab["y_direction"] < $yCentreVille) { // au bas de la ville
						if ($tab["y_direction"] > $yCentreVille - $rayonMin) { // à l'intérieur du rayon en bas
							$tab["y_direction"] = $yCentreVille - $rayonMin - Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - E - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						} else if ($tab["y_direction"] < $yCentreVille - $rayonMax) { // à l'extérieur du rayon en bas
							$tab["y_direction"] = $yCentreVille - $rayonMax - Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - F - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						}
					} else { // en haut de la ville
						if ($tab["y_direction"] < $yCentreVille + $rayonMin) { // à l'intérieur du rayon en haut
							$tab["y_direction"] = $yCentreVille + $rayonMin + Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - G - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						} else if ($tab["y_direction"] > $yCentreVille + $rayonMax) { // à l'extérieur du rayon en haut
							$tab["y_direction"] = $yCentreVille + $rayonMax + Bral_Util_De::get_de_specifique(0, 5);
							Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - H - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);
						}
					}
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
			$tab["x_direction"] = $config->game->x_min + 1;
		}
		if ($tab["x_direction"] >= $config->game->x_max) {
			$tab["x_direction"] = $config->game->x_max - 1;
		}
		if ($tab["y_direction"] <= $config->game->y_min) {
			$tab["y_direction"] = $config->game->y_min + 1;
		}
		if ($tab["y_direction"] >= $config->game->y_max) {
			$tab["y_direction"] = $config->game->y_max - 1;
		}

		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon - exit - (idm:".$idMonstre.") directionX=".$tab["x_direction"]." directionY=".$tab["y_direction"]);

		return $tab;

	}
}
