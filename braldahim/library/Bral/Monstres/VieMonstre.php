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
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementMonstre - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::deplacementMonstre, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si le monstre est déjà dans la position
		if (($x_destination == $this->monstre["x_monstre"]) && ($y_destination == $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre en position");
			return false;
		}
		$modif = false;
		if ($this->monstre["pa_monstre"] == 0) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Le monstre n'a plus de PA");
		}
		
		$palissadeTable = new Palissade();
		$x_min = $this->monstre["x_monstre"] - $this->monstre["vue_monstre"];
		$x_max = $this->monstre["x_monstre"] + $this->monstre["vue_monstre"];
		$y_min = $this->monstre["y_monstre"] - $this->monstre["vue_monstre"];
		$y_max = $this->monstre["y_monstre"] + $this->monstre["vue_monstre"];
		
		$palissades = $palissadeTable->selectVue($x_min, $y_min, $x_max, $y_max);
		
		$this->tabValidationPalissade = null;
		for ($j = 12; $j >= -12; $j--) {
			for ($i = -12; $i <= 12; $i++) {
				$x = $this->monstre["x_monstre"] + $i;
			 	$y = $this->monstre["y_monstre"] + $j;
			 	$this->tabValidationPalissade[$x][$y] = true;
			}
		}
		foreach($palissades as $p) {
			$this->tabValidationPalissade[$p["x_palissade"]][$p["y_palissade"]] = false;
		}
			
		$pa_a_jouer = Bral_Util_De::get_de_specifique(0, $this->monstre["pa_monstre"]);
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") - nb pa a jouer=".$pa_a_jouer. " destination x=".$x_destination." y=".$y_destination);
		$nb_pa_joues = 0;
		while ((($x_destination != $this->monstre["x_monstre"]) || ($y_destination != $this->monstre["y_monstre"])) && ($nb_pa_joues < $pa_a_jouer)) {
			
			$x_monstre = $this->monstre["x_monstre"];
			$y_monstre = $this->monstre["y_monstre"];
			$x_offset = 0;
			$y_offset = 0;
			
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
			
			if ($this->tabValidationPalissade[$x_monstre][$y_monstre] == true) {
				$this->monstre["x_monstre"] = $x_monstre;
				$this->monstre["y_monstre"] = $y_monstre;
				$modif = true;
			} elseif ($this->tabValidationPalissade[$this->monstre["x_monstre"] + $x_offset][$this->monstre["y_monstre"]] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"]  + $x_offset;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"];
				$modif = true;
			} elseif ($this->tabValidationPalissade[$this->monstre["x_monstre"]][$this->monstre["y_monstre"] + $y_offset] == true) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"] ;
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"] + $y_offset;
				$modif = true;
			}
				
			$nb_pa_joues = $nb_pa_joues + 1;
			$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - 1;
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") nouvelle position x=".$this->monstre["x_monstre"]." y=".$this->monstre["y_monstre"].", pa restant=".$this->monstre["pa_monstre"]);
		}
		if ($modif === true) {
			$this->updateMonstre();
			$retour = true;
		} else {
			$retour = false;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementMonstre - exit (".$retour.")");
	}

	public function attaque($view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaque - enter");
		
		$this->calculTour();
		
		if ($this->monstre["id_fk_hobbit_cible_monstre"] != null) {
			$hobbitTable = new Hobbit();
			$cibleDuMonstre = $hobbitTable->findById($m["id_fk_hobbit_cible_monstre"]);
			$cibleDuMonstre = $cibleDuMonstre->toArray();
			$vieMonstre->attaqueCible($cibleDuMonstre, $this->view);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaque - exit (cible)");
		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaque - exit (pas de cible)");
			return null; // pas de cible
		}
	}
	
	public function attaqueCible(&$cible, $view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - enter");
		$mortCible = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

		$this->calculTour();
		
		// on regarde si la cible est dans la vue du monstre
		if (($cible["x_hobbit"] > $this->monstre["x_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($cible["x_hobbit"] < $this->monstre["x_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($cible["y_hobbit"] > $this->monstre["y_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($cible["y_hobbit"] < $this->monstre["y_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible en dehors de la vue hx=".$cible["x_hobbit"] ." hy=".$cible["y_hobbit"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - exit");
			return null; // pas de cible
		} else if (($cible["x_hobbit"] != $this->monstre["x_monstre"]) || ($cible["y_hobbit"] != $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible (".$cible["id_hobbit"].") sur une case differente");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - exit");
			return null; // pas de cible
		} else if ($this->monstre["pa_monstre"] < 4) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") insuffisant nb=".$this->monstre["pa_monstre"]);
			return false; // cible non morte
		}

		$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - 4;

		$jetAttaquant = $this->calculJetAttaque();
		$jetCible = $this->calculJetCible($cible);

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - Jets : attaque=".$jetAttaquant. " esquive=".$jetCible."");
		if ($jetAttaquant > $jetCible) {
			$critique = false;
			if ($jetAttaquant / 2 > $jetCible) {
				if (Bral_Util_Commun::getEffetMotX($cible["id_hobbit"]) == true) {
					$critique = false;
				} else {
					$critique = true;
				}
			}
			$jetDegat = $this->calculDegat($critique);
			$jetDegat = Bral_Util_Commun::getEffetMotA($cible["id_hobbit"], $jetDegat);
			
			$pvPerdus = $jetDegat - $cible["armure_naturelle_hobbit"] - $cible["armure_equipement_hobbit"];
			if ($pvPerdus < 0) {
				$pvPerdus = 1; // on perd 1 pv quoi qu'il arrive
			}
			$cible["pv_restant_hobbit"] = $cible["pv_restant_hobbit"] - $pvPerdus;
			if ($cible["pv_restant_hobbit"]  <= 0) {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible - Mort de la cible La cible (".$cible["id_hobbit"].") par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus);
				$mortCible = true;
				$this->monstre["nb_kill_monstre"] = $this->monstre["nb_kill_monstre"] + 1;
				$this->monstre["id_fk_hobbit_cible_monstre"] = null;
				$cible["nb_mort_hobbit"] = $cible["nb_mort_hobbit"] + 1;
				$cible["est_mort_hobbit"] = "oui";
				$cible["date_fin_tour_hobbit"] = date("Y-m-d H:i:s");
				$id_type_evenement = self::$config->game->evenements->type->killhobbit;
				$id_type_evenement_cible = self::$config->game->evenements->type->mort;
				$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a tué le hobbit ".$cible["prenom_hobbit"] ." ". $cible["nom_hobbit"]." (".$cible["id_hobbit"].")";
				$this->majEvenements(null, $this->monstre["id_monstre"], $id_type_evenement, $details, $this->monstre["niveau_monstre"], "", $view);
				$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus, $mortCible);
				$this->majEvenements($cible["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot, $cible["niveau_hobbit"], $view);
				$this->updateCible($cible);
			} else {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible - Survie de la cible La cible (".$cible["id_hobbit"].") attaquee par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus. " pv_restant_hobbit=".$cible["pv_restant_hobbit"]);
				$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - (floor($cible["niveau_hobbit"] / 10) + 1);
				$cible["est_mort_hobbit"] = "non";
				$id_type_evenement = self::$config->game->evenements->type->attaquer;
				$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["prenom_hobbit"]." ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ")";
				$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus);

				$effetMotS = Bral_Util_Commun::getEffetMotS($cible["id_hobbit"]);
				$this->updateCible($cible);
				if ($effetMotS != null) {
					$detailsBot .= " 
Le hobbit ".$cible["prenom_hobbit"]." ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ") a riposté.
Consultez vos événements pour plus de détails.";
						
					// mise a jour de l'événement avant la riposte
					$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $cible["niveau_hobbit"], $view);
					
					Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible - La cible (".$cible["id_hobbit"].") possede le mot S -> Riposte");
					$hobbitTable = new Hobbit();
					$hobbitRowset = $hobbitTable->find($cible["id_hobbit"]);
					$hobbitAttaquant = $hobbitRowset->current();
					$jetAttaquant =  Bral_Util_Attaque::calculJetAttaqueNormale($hobbitAttaquant);
					$jetsDegat = Bral_Util_Attaque::calculDegatAttaqueNormale($hobbitAttaquant);
					$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($this->monstre);
					Bral_Util_Attaque::attaqueMonstre($hobbitAttaquant, $this->monstre, $jetAttaquant, $jetCible, $jetsDegat, false, false, true);
				
				} else { // si pas de riposte, mise a jour de l'événement
					$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $cible["niveau_hobbit"], $view);
				}
				
			}

		} else if ($jetCible/2 < $jetAttaquant) {
			$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - (floor($cible["niveau_hobbit"] / 10) + 1);
			$this->updateCible($cible);
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["prenom_hobbit"]." ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ") qui a esquivé l'attaque";
			$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible);
			$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $cible["niveau_hobbit"], $view);
		} else {
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["prenom_hobbit"]." ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ") qui a esquivé l'attaque parfaitement";
			$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible);
			$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $cible["niveau_hobbit"], $view);
		}
		$this->updateMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueCible - exit (return=".$mortCible.")");
		return $mortCible;
	}

	public function setMonstre($m) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setMonstre - enter");
		if ($m == null) {
			throw new Zend_Exception("Bral_Monstres_VieMonstre::setMonstre, monstre invalide");
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setMonstre - exit (id=".$m["id_monstre"].")");
		$this->monstre = $m;
	}

	private function calculTour() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculTour, monstre invalide");
		}

		$date_courante = date("Y-m-d H:i:s");
		if ($date_courante > $this->monstre["date_fin_tour_monstre"]) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - nouveau tour");
			$this->monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($this->monstre["date_fin_tour_monstre"], $this->monstre["duree_prochain_tour_monstre"]);
			if ($this->monstre["date_fin_tour_monstre"]  < $date_courante) {
				$this->monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, $this->monstre["duree_prochain_tour_monstre"]);
			}
			$this->monstre["duree_prochain_tour_monstre"] = $this->monstre["duree_base_tour_monstre"];
			$this->monstre["pa_monstre"] = self::$config->game->monstre->pa_max;
			
			Zend_Loader::loadClass("Bral_Util_EffetsPotion");
			$monstreTable = new Monstre();
			$monstreRowset = $monstreTable->find($this->monstre["id_monstre"]);
			$monstre = $monstreRowset->current();
			$effetsPotions = Bral_Util_EffetsPotion::calculPotionMonstre($monstre);
			if (count($effetsPotions) > 0) {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - des potions sur le monstre ont ete trouvee(s). Cf. log potion.log");
			} else {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - aucune potion sur le monstre n'a ete trouvee. Cf. log potion.log");
			}
			$this->calulRegeneration();
			$this->monstre["regeneration_malus_monstre"] = 0;
			$this->monstre["vue_malus_monstre"] = 0;
			$this->monstre["force_bm_monstre"] = 0;
			$this->monstre["agilite_bm_monstre"] = 0;
			$this->monstre["agilite_malus_monstre"] = 0;
			$this->monstre["sagesse_bm_monstre"] = 0;
			$this->monstre["vigueur_bm_monstre"] = 0;
			$this->updateMonstre();
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculTour - exit");
	}
	
	private function calulRegeneration() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration - enter");
		
		if ($this->monstre["pv_restant_monstre"] < $this->monstre["pv_max_monstre"]) {
			$this->monstre["regeneration_monstre"] = floor($this->monstre["vigueur_base_monstre"] / 4) + 1;
			$jet = Bral_Util_Vie::calculRegenerationMonstre($this->monstre);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - jet de regeneration:".$jet);
		}
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calulRegeneration - exit");
	}

	private function calculJetCible($cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - enter");
		$jetCible = 0;
		for ($i=1; $i<= self::$config->game->base_agilite + $cible["agilite_base_hobbit"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $cible["agilite_bm_hobbit"] + $cible["bm_defense_hobbit"] + $cible["agilite_bbdf_hobbit"];
		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - exit (jet=".$jetCible.")");
		return $jetCible;
	}

	private function calculJetAttaque() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - enter");
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->monstre["agilite_base_monstre"]; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $this->monstre["agilite_bm_monstre"];
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - exit (jet=".$jetAttaquant.")");
		return $jetAttaquant;
	}

	private function calculDegat($estCritique) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDegat - enter (critique=".$estCritique.")");
		$jetDegat = 0;
		$coefCritique = 1;
		if ($estCritique === true) {
			$coefCritique = 1.5;
		}
		for ($i=1; $i <= (self::$config->game->base_force + $this->monstre["force_base_monstre"])  * $coefCritique; $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d6();
		}
		$jetDegat = $jetDegat + $this->monstre["force_bm_monstre"];
		if ($jetDegat < 0) {
			$jetDegat = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDegat - exit (jet=$jetDegat)");
		return $jetDegat;
	}

	private function updateCible(&$cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - enter (id_hobbit=".$cible["id_hobbit"].")");
		
		Bral_Util_Attaque::calculStatutEngage(&$cible);
		
		// Mise a jour de la cible
		$hobbitTable = new Hobbit();
		$data = array(
			'pv_restant_hobbit' => $cible["pv_restant_hobbit"],
			'est_mort_hobbit' => $cible["est_mort_hobbit"],
			'nb_mort_hobbit' => $cible["nb_mort_hobbit"],
			'agilite_bm_hobbit' => $cible["agilite_bm_hobbit"],
			'est_engage_hobbit' => $cible["est_engage_hobbit"],
			'est_engage_next_dla_hobbit' => $cible["est_engage_next_dla_hobbit"],
			'date_fin_tour_hobbit' => $cible["date_fin_tour_hobbit"],
		);
		$where = "id_hobbit=".$cible["id_hobbit"];
		$hobbitTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - exit");
	}

	private function updateMonstre() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre - enter");
		if ($this->monstre == null) {
			new Zend_Exception(get_class($this)." - miseAJourMonstre, monstre inconnu");
		}

		$monstreTable = new Monstre();
		$data = array(
			'pa_monstre' => $this->monstre["pa_monstre"],
			'x_monstre' => $this->monstre["x_monstre"],
			'y_monstre' => $this->monstre["y_monstre"],
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
		);
		$where = "id_monstre=".$this->monstre["id_monstre"];
		$monstreTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateMonstre - exit");
	}

	/*
	 * Mise à jour des événements du monstre.
	 */
	public function majEvenements($id_hobbit, $id_monstre, $id_type_evenement, $details, $detailsBot, $niveau, $view) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majEvenements - enter");
		Bral_Util_Evenement::majEvenementsFromVieMonstre($id_hobbit, $id_monstre, $id_type_evenement, $details, $detailsBot, $niveau, $view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majEvenements - exit");
	}
	
	/*
	 * Mort d'un monstre : mise à jour table monstre
	 * Drop Rune
	 */
	public function mortMonstreDb($id_monstre, $effetMotD, $effetMotH, $niveauHobbit) {
	
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
		);
		
		$where = "id_monstre=".$id_monstre;
		$monstreTable->update($data, $where);
		
		self::dropRune($monstre["x_monstre"], $monstre["y_monstre"], $monstre["niveau_monstre"], $niveauHobbit);
		$this->dropCastars($monstre["x_monstre"], $monstre["y_monstre"], $monstre["niveau_monstre"], $effetMotH, $niveauHobbit);
	}
	
	public static function dropRune($x, $y, $niveauTue, $niveauHobbit, $effetMotD = 0) {
		Zend_Loader::loadClass("ElementRune");
		Zend_Loader::loadClass("TypeRune");
		
		//Si 10+2*(Niv tué - Niveau attaquant)+Niveau tué <= 0 alors pas de drop de rune
		if ((10 + 2 * ($niveauTue - $niveauHobbit) + $niveauTue) <= 0) {
			Bral_Util_Log::viemonstres()->debug(" - dropRune - pas de drop de rune : niveauTue=".$niveauTue." niveauHobbit=".$niveauHobbit);
			return;
		}
		
		$tirage = Bral_Util_De::get_1d100();
		
		Bral_Util_Log::viemonstres()->debug(" - dropRune - tirage=".$tirage. " niveauTue=".$niveauTue. " effetMotD=".$effetMotD);
		
		if ($tirage >= 1 && $tirage <= 1 + ($niveauTue/4) + $effetMotD) {
			$niveauRune = 'a';
		} else if ($tirage >= 2 && $tirage <= 20 + ($niveauTue/4) + $effetMotD) {
			$niveauRune = 'b';
		} else if ($tirage >= 21 && $tirage <= 30 - ($niveauTue/4) + $effetMotD) {
			$niveauRune = 'c';
		} else { //if ($tirage >= 31 && $tirage <= 100 - ($niveau/4) + $effetMotD) {
			$niveauRune = 'd';
		}
		
		Bral_Util_Log::viemonstres()->debug(" - dropRune - niveau retenu=".$niveauRune);
		
		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);
		
		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			return; // rien à faire, doit jamais arriver
		}
		
		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);
		
		$typeRune = $typeRuneRowset[$numeroRune];
		
		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		
		$elementRuneTable = new ElementRune();
		$data = array(
			"x_element_rune"  => $x,
			"y_element_rune" => $y,
			"id_fk_type_element_rune" => $typeRune["id_type_rune"],
			"date_depot_element_rune" => $dateCreation,
			"date_fin_element_rune" => $dateFin,
		);
		
		$elementRuneTable = new ElementRune();
		$elementRuneTable->insert($data);
		
		Zend_Loader::loadClass("StatsRunes");
		$statsRunes = new StatsRunes();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRunes["id_fk_type_rune_stats_runes"] = $typeRune["id_type_rune"];
		$dataRunes["mois_stats_runes"] = date("Y-m-d", $moisEnCours);
		$dataRunes["nb_rune_stats_runes"] = 1;
		$statsRunes->insertOrUpdate($dataRunes);
	}
	
	private function dropCastars($x, $y, $niveauMonstre, $effetMotH, $niveauHobbit) {
		Zend_Loader::loadClass("Castar");
		
		$nbCastars = 10 * $niveauMonstre + Bral_Util_De::get_1d5();
		if ($effetMotH == true) { 
			$nbCastars = $nbCastars * 2;
		}
		
		if ((10 + 2 * ($niveauMonstre - $niveauHobbit) + $niveauMonstre) <= 0) {
			$nbCastars = $nbCastars / 2;
		}
		
		$castarTable = new Castar();
		$data = array(
			"x_castar"  => $x,
			"y_castar" => $y,
			"nb_castar" => $nbCastars,
		);
		
		$castarTable = new Castar();
		$castarTable->insertOrUpdate($data);
	}
	
	private function getDetailsBot($cible, $jetAttaquant, $jetCible, $jetDegat = 0, $critique = false, $pvPerdus = 0, $mortCible = false) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";

		$retour .= "Vous avez été attaqué par ".$this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].")";
		
		$retour .= "
Jet d'attaque : ".$jetAttaquant;
		$retour .= "
Jet de défense : ".$jetCible;
		$retour .= "
Jet de dégâts : ".$jetDegat;
		
		if ($jetAttaquant > $jetCible) {
			if ($critique) {
				$retour .= "
Vous avez été touché par une attaque critique";
			} else {
				$retour .= "
Vous avez été touché";
			}
			
			if ($cible["armure_naturelle_hobbit"] > 0) {
				$retour .= "
Votre armure naturelle vous a protégé en réduisant les dégâts de ";
				$retour .= $cible["armure_naturelle_hobbit"].".";
			} else {
				$retour .= "
Votre armure naturelle ne vous a pas protégé (ARM NAT:".$cible["armure_naturelle_hobbit"].")"; 	
			}
			
			if ($cible["armure_equipement_hobbit"] > 0) {
				$retour .= "
Votre équipement vous a protégé en réduisant les dégâts de ";
				$retour .= $cible["armure_equipement_hobbit"].".";
			} else {
				$retour .= "
Aucun équipement ne vous a protégé (ARM EQU:".$cible["armure_equipement_hobbit"].")"; 	
			}
			
			$retour .= "
Vous avez perdu ".$pvPerdus. " PV ";
			$retour .= "
Il vous reste ".$cible["pv_restant_hobbit"]." PV ";
			
			if ($mortCible) {
			$retour .= "
Vous avez été tué";
			}
		} else if ($jetCible/2 < $jetAttaquant) { // esquive
			$retour .= "
Vous avez esquivé l'attaque";
		} else { // esquive parfaite
			$retour .= "
Vous avez esquivé parfaitement l'attaque";
		}
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
	
	public static function getTabXYRayon($niveau, $villes, $directionX, $directionY, $offsetX = null, $offsetY = null) {
		$tab["x_direction"] = $directionX;
		$tab["y_direction"] = $directionY;
		
		$rayonMin = $niveau * 4;
		
		if ($offsetX == null || $offsetY == null) {
			$offsetX = $rayonMin;
			$offsetY = $rayonMin;
		}
		
		foreach($villes as $v) {
			// vérification rayon
			$estPasse = false;
			if ($v["x_min_ville"] - $rayonMin <= $directionX && $v["x_max_ville"] + $rayonMin >= $directionX
			&& $v["y_min_ville"] - $rayonMin <= $directionY && $v["y_max_ville"] + $rayonMin >= $directionY) {
				
				Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon - monstre en ville, niveau $niveau xmin:".$v["x_min_ville"] ." xmax:".$v["x_max_ville"] ." ymin:".$v["y_min_ville"] ." ymax:".$v["y_max_ville"]. " directionX:".$directionX. " directionY:".$directionY. " offsetX:".$offsetX. " offsetY:".$offsetY);
				
				if ($v["x_min_ville"] - $rayonMin <= $directionX && $v["x_max_ville"] + $rayonMin >= $directionX) {
					if ($directionX <= $v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2) { // centre x de la ville
						Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix A offsetX=$offsetX");
						$directionX = $directionX - $offsetX;
						if ($v["x_min_ville"] - $rayonMin <= $directionX && $v["x_max_ville"] + $rayonMin >= $directionX) {
							Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix A2");
							$directionX = $v["x_min_ville"] - $rayonMin;
						}
					} else if ($directionX >= $v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2) {
						Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix B offsetX=$offsetX");
						$directionX = $directionX + $offsetX;
						if ($v["x_min_ville"] - $rayonMin <= $directionX && $v["x_max_ville"] + $rayonMin >= $directionX) {
							Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix B2");
							$directionX = $v["x_min_ville"] + $rayonMin;
						}
					}
				}
			
				if ($v["y_min_ville"] - $rayonMin <= $directionY && $v["y_max_ville"] + $rayonMin >= $directionY) {
					if ($directionY <= $v["y_min_ville"] + ($v["y_max_ville"] - $v["y_min_ville"]) / 2) { // centre y de la ville
						Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon choix C offsetY=$offsetY");
						$directionY = $directionY - $offsetY;
						if ($v["y_min_ville"] - $rayonMin <= $directionY && $v["y_max_ville"] + $rayonMin >= $directionY) {
							Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix C2");
							$directionY = $v["y_min_ville"] - $rayonMin;
						}
					} else if ($directionY >= $v["y_min_ville"] + ($v["y_max_ville"] - $v["y_min_ville"]) / 2) {
						Bral_Util_Log::viemonstres()->trace("Bral_Monstres_VieMonstre - getTabXYRayon choix D offsetY=$offsetY");
						$directionY = $directionY + $offsetY;
						if ($v["y_min_ville"] - $rayonMin <= $directionY && $v["y_max_ville"] + $rayonMin >= $directionY) {
							Bral_Util_Log::viemonstres()->debug("Bral_Monstres_VieMonstre - getTabXYRayon choix D2");
							$directionY = $v["y_min_ville"] + $rayonMin;
						}
					}
				}
				$estPasse = true;
			}
			if ($estPasse) {
				break;
			}
		}
        $tab["x_direction"] = $directionX;
		$tab["y_direction"] = $directionY;
		$tab["est_traite"] = $estPasse;
		return $tab;
	}
}
