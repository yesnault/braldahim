<?php

class Bral_Monstres_VieMonstre {
	private static $instance = null;
	private $monstre = null;
	private static $config = null;

	public static function getInstance() {
		Bral_Util_Log::tech()->trace("Bral_Monstres_VieMonstre - getInstance - enter");
		
		if (self::$instance == null) {
			Zend_Loader::loadClass("Palissade");
			
			self::$config = Zend_Registry::get('config');
			self::$instance = new self();
			Bral_Util_Log::tech()->trace("Bral_Monstres_VieMonstre - getInstance - nouvelle instance - exit");
			return self::$instance;
		} else {
			Bral_Util_Log::tech()->trace("Bral_Monstres_VieMonstre - getInstance - instance existante - exit");
			return self::$instance;
		}
	}

	/**
	 * Constructeur privé. Utiliser getInstance().
	 */
	private function __construct() {}

	/**
	 * Déplacement du monstre une une position.
	 *
	 * @param int $x_destination
	 * @param int $y_destination
	 * @return boolean : le monstre a bougé (true) ou non (false)
	 */
	public function deplacementMonstre($x_destination, $y_destination) {
		Bral_Util_Log::tech()->trace(get_class($this)." - deplacementMonstre - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::deplacementMonstre, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si le monstre est déjà dans la position
		if (($x_destination == $this->monstre["x_monstre"]) && ($y_destination == $this->monstre["y_monstre"])) {
			Bral_Util_Log::tech()->debug(get_class($this)." - monstre en position");
			return false;
		}
		$modif = false;
		if ($this->monstre["pa_monstre"] == 0) {
			Bral_Util_Log::tech()->debug(get_class($this)." - Le monstre n'a plus de PA");
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
		Bral_Util_Log::tech()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") - nb pa a jouer=".$pa_a_jouer. " destination x=".$x_destination." y=".$y_destination);
		$nb_pa_joues = 0;
		while ((($x_destination != $this->monstre["x_monstre"]) || ($y_destination != $this->monstre["y_monstre"])) && ($nb_pa_joues < $pa_a_jouer)) {
			
			$x_monstre = $this->monstre["x_monstre"];
			$y_monstre = $this->monstre["y_monstre"];
			
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
			Bral_Util_Log::tech()->debug(get_class($this)." - monstre(".$this->monstre["id_monstre"].") nouvelle position x=".$this->monstre["x_monstre"]." y=".$this->monstre["y_monstre"].", pa restant=".$this->monstre["pa_monstre"]);
		}
		if ($modif === true) {
			$this->updateMonstre();
			$retour = true;
		} else {
			$retour = false;
		}
		Bral_Util_Log::tech()->trace(get_class($this)." - deplacementMonstre - exit (".$retour.")");
	}

	public function attaqueCible(&$cible) {
		Bral_Util_Log::tech()->trace(get_class($this)." - attaqueCible - enter");
		$mortCible = false;

		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si la cible est dans la vue du monstre
		if (($cible["x_hobbit"] > $this->monstre["x_monstre"] + $this->monstre["vue_monstre"])
		|| ($cible["x_hobbit"] < $this->monstre["x_monstre"] - $this->monstre["vue_monstre"])
		|| ($cible["y_hobbit"] > $this->monstre["y_monstre"] + $this->monstre["vue_monstre"])
		|| ($cible["y_hobbit"] < $this->monstre["y_monstre"] - $this->monstre["vue_monstre"])) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::tech()->debug(get_class($this)." - cible en dehors de la vue hx=".$cible["x_hobbit"] ." hy=".$cible["y_hobbit"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::tech()->trace(get_class($this)." - attaqueCible - exit");
			return null;
		} else if (($cible["x_hobbit"] != $this->monstre["x_monstre"]) || ($cible["y_hobbit"] != $this->monstre["y_monstre"])) {
			Bral_Util_Log::tech()->debug(get_class($this)." - cible sur une case differente");
			Bral_Util_Log::tech()->trace(get_class($this)." - attaqueCible - exit");
			return null;
		}

		$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - 4;

		$jetAttaquant = $this->calculJetAttaque();
		$jetCible = $this->calculJetCible($cible);

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::tech()->debug(get_class($this)." - Jets : attaque=".$jetAttaquant. " esquive=".$jetCible."");
		if ($jetAttaquant > $jetCible) {
			$critique = false;
			if ($jetAttaquant / 2 > $jetCible) {
				if (Bral_Util_Commun::getEffetMotX($hobbit->id_hobbit) == true) {
					$critique = false;
				} else {
					$critique = true;
				}
			}
			$this->calculDegat($critique);
			$jetDegat = $this->calculDegat();
			
			$jetDegat = Bral_Util_Commun::getEffetMotA($cible["id_hobbit"], $jetDegat);
			
			$pvPerdus = - $jetDegat;
			if ($pvPerdus > 0) {
				$pvPerdus = 0;
			}
			$cible["pv_restant_hobbit"] = $cible["pv_restant_hobbit"] - $pvPerdus;
			$nb_kills = $this->monstre["nb_kill_monstre"];
			$nb_morts = $cible["nb_mort_hobbit"];
			if ($cible["pv_restant_hobbit"]  <= 0) {
				Bral_Util_Log::tech()->notice("Bral_Monstres_VieMonstre - attaqueCible - Mort de la cible La cible (".$cible["id_hobbit"].") par Monstre id:".$this->monstre["nb_kill_monstre"]);
				$mortCible = true;
				$this->monstre["nb_kill_monstre"] = $this->monstre["nb_kill_monstre"] + 1;
				$cible["nb_mort_hobbit"] = $cible["nb_mort_hobbit"] + 1;
				$cible["est_mort_hobbit"] = "oui";
				$id_type_evenement = self::$config->game->evenements->type->kill;
				$id_type_evenement_cible = self::$config->game->evenements->type->mort;
				$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a tué le hobbit ".$cible["prenom_hobbit"] ." ". $cible["nom_hobbit"]." (".$cible["id_hobbit"].")";
				$this->majEvenements(null, $this->monstre["id_monstre"], $id_type_evenement, $details);
				$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus, $mortCible);
				$this->majEvenements($cible["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot);
			} else {
				Bral_Util_Log::tech()->notice("Bral_Monstres_VieMonstre - attaqueCible - Survie de la cible La cible (".$cible["id_hobbit"].") attaquee par Monstre id:".$this->monstre["nb_kill_monstre"]);
				$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - (floor($cible["niveau_hobbit"] / 10) + 1);
				$cible["est_mort_hobbit"] = "non";
				$id_type_evenement = self::$config->game->evenements->type->attaquer;
				$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ")";
				$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible, $jetDegat, $critique);
				$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot);

				$effetMotS = Bral_Util_Commun::getEffetMotS($hobbitAttaquant->id_hobbit);
				if ($effetMotS != null) {
					Bral_Util_Log::tech()->notice("Bral_Monstres_VieMonstre - attaqueCible - La cible (".$hobbitAttaquant->id_hobbit.") possede le mot S -> Riposte");
					Zend_Loader::loadClass("Bral_Util_Attaque");
					$hobbitTable = new Hobbit();
					$hobbitRowset = $hobbitTable->find($idHobbitCible);
					$hobbitAttaquant = $hobbitRowset->current();
					$jetAttaquant =  Bral_Util_Attaque::calculJetAttaqueNormale($hobbitAttaquant);
					$jetsDegat = Bral_Util_Attaque::calculDegatAttaqueNormale($hobbitAttaquant);
					$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($this->monstre);
					Bral_Util_Attaque::attaqueMonstre($hobbitAttaquant, $this->monstre["id_monstre"], $jetAttaquant, $jetCible, $jetsDegat);
				}
			}

			$this->updateCible($cible);
			$this->updateMonstre();
		} else if ($jetCible/2 < $jetAttaquant) {
			$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - (floor($cible["niveau_hobbit"] / 10) + 1);
			$this->updateCible($cible);
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ") qui a esquiv&eacute; l'attaque";
			$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible);
			$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot);
		} else {
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a attaqué le hobbit ".$cible["nom_hobbit"]." (".$cible["id_hobbit"] . ") qui a esquiv&eacute; l'attaque parfaitement";
			$detailsBot = $this->getDetailsBot($cible, $jetAttaquant, $jetCible);
			$this->majEvenements($cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot);
		}
		
		Bral_Util_Log::tech()->trace(get_class($this)." - attaqueCible - exit (return=".$mortCible.")");
		return $mortCible;
	}

	public function setMonstre($m) {
		Bral_Util_Log::tech()->trace(get_class($this)." - setMonstre - enter");
		if ($m == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::setMonstre, monstre invalide");
		}
		Bral_Util_Log::tech()->trace(get_class($this)." - setMonstre - exit (id=".$m["id_monstre"].")");
		$this->monstre = $m;
	}

	private function calculTour() {
		Bral_Util_Log::tech()->trace(get_class($this)." - calculTour - enter");
		if ($this->monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculTour, monstre invalide");
		}

		$date_courante = date("Y-m-d H:i:s");
		if ($date_courante > $this->monstre["date_fin_tour_monstre"]) {
			Bral_Util_Log::tech()->trace(get_class($this)." - nouveau tour");
			$this->monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($this->monstre["date_fin_tour_monstre"], $this->monstre["duree_prochain_tour_monstre"]);
			$this->monstre["duree_prochain_tour_monstre"] = $this->monstre["duree_base_tour_monstre"];
			$this->monstre["pa_monstre"] = self::$config->game->monstre->pa_max;
			
			Zend_Loader::loadClass("Bral_Util_EffetsPotion");
			$monstreTable = new Monstre();
			$monstreRowset = $monstreTable->find($this->monstre["id_monstre"]);
			$monstre = $monstreRowset->current();
			$effetsPotions = Bral_Util_EffetsPotion::calculPotionMonstre($monstre);
			if (count($effetsPotions) > 0) {
				Bral_Util_Log::tech()->trace(get_class($this)." - calculTour - des potions sur le monstre ont ete trouvee(s). Cf. log potion.log");
			} else {
				Bral_Util_Log::tech()->trace(get_class($this)." - calculTour - aucune potion sur le monstre n'a ete trouvee. Cf. log potion.log");
			}
		
			$this->updateMonstre();
		}
		Bral_Util_Log::tech()->trace(get_class($this)." - calculTour - exit");
	}

	private function calculJetCible($cible) {
		Bral_Util_Log::tech()->trace(get_class($this)." - calculJetCible - enter");
		$jetCible = 0;
		for ($i=1; $i<= self::$config->game->base_agilite + $cible["agilite_base_hobbit"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $cible["agilite_bm_hobbit"] + $cible["bm_defense_hobbit"] + $cible["agilite_bbdf_hobbit"];
		Bral_Util_Log::tech()->trace(get_class($this)." - calculJetCible - exit (jet=".$jetCible.")");
		return $jetCible;
	}

	private function calculJetAttaque() {
		Bral_Util_Log::tech()->trace(get_class($this)." - calculJetAttaque - enter");
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->monstre["agilite_base_monstre"]; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $this->monstre["agilite_bm_monstre"];
		Bral_Util_Log::tech()->trace(get_class($this)." - calculJetAttaque - exit (jet=".$jetAttaquant.")");
		return $jetAttaquant;
	}

	private function calculDegat($estCritique) {
		Bral_Util_Log::tech()->trace(get_class($this)." - calculDegat - enter (critique=".$estCritique.")");
		$jetDegat = 0;
		$coefCritique = 1;
		if ($estCritique === true) {
			$coefCritique = 1.5;
		}
		for ($i=1; $i <= (self::$config->game->base_force + $this->monstre["force_base_monstre"])  * $coefCritique; $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d6();
		}
		$jetDegat = $jetDegat + $this->monstre["force_bm_monstre"];
		Bral_Util_Log::tech()->trace(get_class($this)." - calculDegat - exit (jet=$jetDegat)");
		return $jetDegat;
	}

	private function updateCible(&$cible) {
		Bral_Util_Log::tech()->trace(get_class($this)." - updateCible - enter (id_hobbit=".$cible["id_hobbit"].")");
		// Mise a jour de la cible
		$hobbitTable = new Hobbit();
		$data = array(
			'pv_restant_hobbit' => $cible["pv_restant_hobbit"],
			'est_mort_hobbit' => $cible["est_mort_hobbit"],
			'nb_mort_hobbit' => $cible["nb_mort_hobbit"],
			'agilite_bm_hobbit' => $cible["agilite_bm_hobbit"],
		);
		$where = "id_hobbit=".$cible["id_hobbit"];
		$hobbitTable->update($data, $where);
		Bral_Util_Log::tech()->trace(get_class($this)." - updateCible - exit");
	}

	private function updateMonstre() {
		Bral_Util_Log::tech()->trace(get_class($this)." - updateMonstre - enter");
		if ($this->monstre == null) {
			new Zend_Exception(get_class($this)." - miseAJourMonstre, monstre inconnu");
		}

		$monstreTable = new Monstre();
		$data = array(
			'pa_monstre' => $this->monstre["pa_monstre"],
			'x_monstre' => $this->monstre["x_monstre"],
			'y_monstre' => $this->monstre["y_monstre"],
			'nb_kill_monstre' => $this->monstre["nb_kill_monstre"],
			'date_fin_tour_monstre' => $this->monstre["date_fin_tour_monstre"],
			'duree_prochain_tour_monstre' => $this->monstre["duree_prochain_tour_monstre"]
		);
		$where = "id_monstre=".$this->monstre["id_monstre"];
		$monstreTable->update($data, $where);
		Bral_Util_Log::tech()->trace(get_class($this)." - updateMonstre - exit");
	}

	/*
	 * Mise à jour des évènements du monstre.
	 */
	public function majEvenements($id_hobbit, $id_monstre, $id_type_evenement, $details, $detailsBot = "") {
		Bral_Util_Log::tech()->trace(get_class($this)." - majEvenements - enter");
		Zend_Loader::loadClass('Evenement');
		$evenementTable = new Evenement();
		$data = array(
			'id_fk_hobbit_evenement' => $id_hobbit,
			'id_fk_monstre_evenement' => $id_monstre,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $id_type_evenement,
			'details_evenement' => $details,
			'details_bot_evenement' => $detailsBot,
		);
		$evenementTable->insert($data);
		Bral_Util_Log::tech()->trace(get_class($this)." - majEvenements - exit");
	}
	
	/*
	 * Mort d'un monstre : suppression de la table des monstre
	 * et ajout dans la table cadavre
	 * Drop Rune
	 */
	public function mortMonstreDb($id_monstre, $effetMotD = null, $effetMotH = null) {
	
		if ($id_monstre == null || (int)$id_monstre<=0 ) {
			throw new Zend_Exception(get_class($this)."::mortMonstreDb id_monstre inconnu:".$id_monstre);
		}
		
		Zend_Loader::loadClass("Cadavre");
		Zend_Loader::loadClass("Monstre");
		
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($id_monstre);
		$monstre = $monstreRowset;
		
		if ($monstre == null || $monstre["id_monstre"] == null || $monstre["id_monstre"] == "") {
			throw new Zend_Exception(get_class($this)."::mortMonstreDb monstre inconnu");
		}
		
		$data = array(
			"id_cadavre" => $monstre["id_monstre"],
			"id_fk_type_monstre_cadavre"  => $monstre["id_fk_type_monstre"],
			"id_fk_taille_cadavre" => $monstre["id_fk_taille_monstre"],
			"x_cadavre" => $monstre["x_monstre"],
			"y_cadavre" => $monstre["y_monstre"],
		);
		
		$cadavreTable = new Cadavre();
		$cadavreTable->insert($data);
		
		$where = "id_monstre=".$id_monstre;
		$monstreTable->delete($where);
		
		$this->dropRune($monstre["x_monstre"], $monstre["y_monstre"], $monstre["niveau_monstre"]);
		$this->dropCastars($monstre["x_monstre"], $monstre["y_monstre"], $monstre["niveau_monstre"]);
	}
	
	private function dropRune($x, $y, $niveau, $effetMotD = 0) {
		Zend_Loader::loadClass("ElementRune");
		Zend_Loader::loadClass("TypeRune");
		
		$tirage = Bral_Util_De::get_1d100();
		
		Bral_Util_Log::tech()->trace(get_class($this)." - dropRune - tirage=".$tirage. " niveau_monstre=".$niveau. " effetMotD=".$effetMotD);
		
		if ($tirage >= 1 && $tirage <= 1 + ($niveau/4) + $effetMotD) {
			$niveau = 'a';
		} else if ($tirage >= 2 && $tirage <= 6 + ($niveau/4) + $effetMotD) {
			$niveau = 'b';
		} else if ($tirage >= 7 && $tirage <= 21 - ($niveau/4) + $effetMotD) {
			$niveau = 'c';
		} else if ($tirage >= 22 && $tirage <= 90 - ($niveau/4) + $effetMotD) {
			$niveau = 'd';
		} else {
			return;
		}
		
		Bral_Util_Log::tech()->trace(get_class($this)."  - dropRune - niveau retenu=".$niveau);
		
		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveau);
		
		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			return; // rien à faire, doit jamais arriver
		}
		
		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);
		
		$typeRune = $typeRuneRowset[$numeroRune];
		
		$elementRuneTable = new ElementRune();
		$data = array(
			"x_element_rune"  => $x,
			"y_element_rune" => $y,
			"id_fk_type_element_rune" => $typeRune["id_type_rune"],
		);
		
		$elementRuneTable = new ElementRune();
		$elementRuneTable->insert($data);
	}
	
	private function dropCastars($x, $y, $niveau, $effetMotH = null) {
		Zend_Loader::loadClass("Castar");
		
		$nbCastars = 10*$niveau + Bral_Util_De::get_1d5();
		
		if ($effetMotH == true) { 
			$nbCastars = $nbCastars * 2;
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
		Bral_Util_Log::tech()->trace(get_class($this)."  - getDetailsBot - enter");
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
			
			$retour .= "
Vous avez perdu ".$pvPerdus. " PV (".$cible["pv_restant_hobbit"]." PV restant(s)) ";
			
			if ($mortCible) {
			$retour .= "
Vous avez été tué";
			}
		} else if ($jetCible/2 < $jetAttaquant) { // esquive
			$retour .= "
Vous avez esquivé l'attaque";
		} else { // esquive parfaite
			$retour .= "
Vous avez equivé parfaitement l'attaque";
		}
		
		
		Bral_Util_Log::tech()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}
