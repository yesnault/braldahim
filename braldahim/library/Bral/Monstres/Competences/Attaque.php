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
abstract class Bral_Monstres_Competences_Attaque {

	protected $monstre = null;
	protected $cible = null;
	protected $view = null;
	protected static $config = null;

	public function __construct($competence, &$monstre, &$cible, $view) {
		$this->competence = $competence;
		$this->monstre = &$monstre;
		$this->cible = &$cible; // $this->cible est une référence de $cible
		self::$config = Zend_Registry::get('config');
		$this->view = $view;
	}

	public function action() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - action - (idm:".$this->monstre["id_monstre"].") - enter");

		$retourVerificationCible = $this->verificationCible();
		if ($retourVerificationCible !== true) { // si d'action sur la cible possible
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - action - (idm:".$this->monstre["id_monstre"].") action non possible - exit");
			return $retourVerificationCible;
		}

		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") avant action nb=".$this->monstre["pa_monstre"]);
		$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $this->competence["pa_utilisation_mcompetence"];

		$koCible = $this->actionSpecifique();

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - action - (idm:".$this->monstre["id_monstre"].") - exit");
		return $koCible;
	}

	protected function verificationCible() {
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - verificationCible - PA Monstre (".$this->monstre["id_monstre"].") avant action nb=".$this->monstre["pa_monstre"]);

		// on regarde si la cible est dans la vue du monstre
		if (($this->cible["x_braldun"] > $this->monstre["x_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["x_braldun"] < $this->monstre["x_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["y_braldun"] > $this->monstre["y_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["y_braldun"] < $this->monstre["y_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible en dehors de la vue hx=".$this->cible["x_braldun"] ." hy=".$this->cible["y_braldun"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if (($this->cible["x_braldun"] != $this->monstre["x_monstre"]) || ($this->cible["y_braldun"] != $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre (".$this->monstre["id_monstre"].") cible (".$this->cible["id_braldun"].") sur une case differente");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if ($this->monstre["pa_monstre"] < $this->competence["pa_utilisation_mcompetence"]) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") insuffisant nb=".$this->monstre["pa_monstre"]." requis=".$this->competence["pa_utilisation_mcompetence"]);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit false");
			return false; // cible non morte
		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible (".$this->cible["id_braldun"].") - exit true");
			return true;
		}
	}

	abstract function actionSpecifique();
	abstract function calculJetAttaque();
	abstract function calculDegat($estCritique);

	protected function updateCible() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - enter (id_braldun=".$this->cible["id_braldun"].")");

		Bral_Util_Attaque::calculStatutEngage(&$this->cible, true);

		// Mise a jour de la cible
		$braldunTable = new Braldun();
		$data = array(
			'pv_restant_braldun' => $this->cible["pv_restant_braldun"],
			'est_ko_braldun' => $this->cible["est_ko_braldun"],
			'nb_ko_braldun' => $this->cible["nb_ko_braldun"],
			'agilite_bm_braldun' => $this->cible["agilite_bm_braldun"],
			'est_engage_braldun' => $this->cible["est_engage_braldun"],
			'est_engage_next_dla_braldun' => $this->cible["est_engage_next_dla_braldun"],
			'date_fin_tour_braldun' => $this->cible["date_fin_tour_braldun"],
			'est_quete_braldun' => $this->cible["est_quete_braldun"],
			'bm_attaque_braldun' => $this->cible["bm_attaque_braldun"],
			'bm_defense_braldun' => $this->cible["bm_defense_braldun"],
		);
		$where = "id_braldun=".$this->cible["id_braldun"];
		$braldunTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - exit");
	}

	protected function calculJetCible($cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - enter");
		$jetCible = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_braldun"]);
		$jetCible = $jetCible + $this->cible["agilite_bm_braldun"] + $this->cible["bm_defense_braldun"] + $this->cible["agilite_bbdf_braldun"];

		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - exit (jet=".$jetCible.")");
		return $jetCible;
	}

	public function attaque() {

		$koCible = null;

		$jetAttaquant = $this->calculJetAttaque();
		$jetCible = $this->calculJetCible($this->cible);

		if ($this->competence["nom_systeme_mcompetence"] == "charger") {
			$verbe = "chargé";
		} else {
			$verbe = "attaqué";
		}

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - idm (".$this->monstre["id_monstre"]." ) Jets : attaque=".$jetAttaquant. " esquiveCible=".$jetCible."");
		if ($jetAttaquant > $jetCible) {
			$critique = false;
			if ($jetAttaquant / 2 > $jetCible) {
				if (Bral_Util_Commun::getEffetMotX($this->cible["id_braldun"]) == true) {
					$critique = false;
				} else {
					$critique = true;
				}
			}
			$jetDegat = $this->calculDegat($critique);
			$jetDegat = Bral_Util_Commun::getEffetMotA($this->cible["id_braldun"], $jetDegat);

			$armureTotale = $this->cible["armure_naturelle_braldun"] + $this->cible["armure_equipement_braldun"] + $this->cible["armure_bm_braldun"];
			if ($armureTotale < 0) {
				$armureTotale = 0;
			}
			$pvPerdus = $jetDegat - $armureTotale;
			if ($pvPerdus <= 0) {
				$pvPerdus = 1; // on perd 1 pv quoi qu'il arrive
			}
			$this->cible["pv_restant_braldun"] = $this->cible["pv_restant_braldun"] - $pvPerdus;
			if ($this->cible["pv_restant_braldun"]  <= 0) {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - Ko de la cible La cible (".$this->cible["id_braldun"].") par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus);
				$koCible = true;
				$details = $this->initKo();
				$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus, $koCible);
				$id_type_evenement_cible = self::$config->game->evenements->type->ko;
				Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_braldun"], null, $id_type_evenement_cible, $details, $detailsBot, $this->cible["niveau_braldun"], $this->view, $this->cible["nb_dla_jouees_braldun"], $this->monstre["nb_dla_jouees_monstre"], Bral_Util_Evenement::ATTAQUE_REUSSIE);
				$this->updateCible();
			} else {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - Survie de la cible La cible (".$this->cible["id_braldun"].") attaquee par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus. " pv_restant_braldun=".$this->cible["pv_restant_braldun"]);
				if ($critique == true) { // En cas de frappe critique : malus en BNS ATT : -2D3. Malus en BNS DEF : -2D6.
					$this->cible["bm_attaque_braldun"] = $this->cible["bm_attaque_braldun"] - Bral_Util_De::get_2d3();
					$this->cible["bm_defense_braldun"] = $this->cible["bm_defense_braldun"] - Bral_Util_De::get_2d6();
				} else { //  En cas de frappe : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
					$this->cible["bm_attaque_braldun"] = $this->cible["bm_attaque_braldun"] - Bral_Util_De::get_1d3();
					$this->cible["bm_defense_braldun"] = $this->cible["bm_defense_braldun"] - Bral_Util_De::get_1d6();
				}

				Zend_Loader::loadClass("Bral_Util_Equipement");
				$pieceCibleAbimee = Bral_Util_Equipement::usureAttaquePiece($this->cible["id_braldun"]);

				$this->cible["est_ko_braldun"] = "non";
				$id_type_evenement = self::$config->game->evenements->type->attaquer;
				$details = "[m".$this->monstre["id_monstre"]."] a $verbe [b".$this->cible["id_braldun"]."]";
				$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus, false, $pieceCibleAbimee);

				// mise a jour de l'événement avant la riposte
				Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_braldun"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_braldun"], $this->view, $this->cible["nb_dla_jouees_braldun"], $this->monstre["nb_dla_jouees_monstre"], Bral_Util_Evenement::ATTAQUE_REUSSIE);

				$effetMotS = Bral_Util_Commun::getEffetMotS($this->cible["id_braldun"]);
				$this->updateCible();
				if ($effetMotS != null) {

					$peutRiposter = Bral_Util_Attaque::verificationNbRiposte($this->cible["nb_dla_jouees_braldun"], $this->cible["id_braldun"]);
					if ($peutRiposter == true) {
						Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - ".$this->cible["id_braldun"]." peut riposter");
						$detailsBot .= PHP_EOL."Le braldun ".$this->cible["prenom_braldun"]." ".$this->cible["nom_braldun"]." (".$this->cible["id_braldun"] . ") a riposté.";
						$detailsBot .= PHP_EOL."Consultez vos événements pour plus de détails.";

						Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - La cible (".$this->cible["id_braldun"].") possede le mot S -> Riposte");
						$braldunTable = new Braldun();
						$braldunRowset = $braldunTable->find($this->cible["id_braldun"]);
						$braldunAttaquant = $braldunRowset->current();
						$jetAttaquant =  Bral_Util_Attaque::calculJetAttaqueNormale($braldunAttaquant);
						$jetsDegat = Bral_Util_Attaque::calculDegatAttaqueNormale($braldunAttaquant);
						$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($this->monstre);
						Bral_Util_Attaque::attaqueMonstre($braldunAttaquant, $this->monstre, $jetAttaquant, $jetCible, $jetsDegat, $this->view, false, false, true);
					} else {
						Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - ".$this->cible["id_braldun"]." ne peut pas riposter");
					}
				}
			}

		} else if ($jetCible/2 < $jetAttaquant) {
			// En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
			$this->cible["bm_attaque_braldun"] = $this->cible["bm_attaque_braldun"] - Bral_Util_De::get_1d3();
			$this->cible["bm_defense_braldun"] = $this->cible["bm_defense_braldun"] - Bral_Util_De::get_1d6();

			$this->updateCible();
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = "[m".$this->monstre["id_monstre"]."] a $verbe [b".$this->cible["id_braldun"]."] qui a esquivé l'attaque";
			$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible);
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_braldun"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_braldun"], $this->view, $this->cible["nb_dla_jouees_braldun"], $this->monstre["nb_dla_jouees_monstre"], Bral_Util_Evenement::ATTAQUE_ESQUIVEE);
		} else {
			// En cas d'esquive parfaite : Aucun malus appliqué.
			Bral_Util_Attaque::calculStatutEngage(&$this->cible, true);
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = "[m".$this->monstre["id_monstre"]."] a $verbe [b".$this->cible["id_braldun"]."] qui a esquivé l'attaque parfaitement";
			$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible);
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_braldun"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_braldun"], $this->view, $this->cible["nb_dla_jouees_braldun"], $this->monstre["nb_dla_jouees_monstre"], Bral_Util_Evenement::ATTAQUE_ESQUIVEE);
		}

		return $koCible;
	}

	protected function initKo() {
		$this->monstre["nb_kill_monstre"] = $this->monstre["nb_kill_monstre"] + 1;
		if ($this->cible["id_braldun"] == $this->monstre["id_fk_braldun_cible_monstre"]) { // utile dans Souffledefeu par exemple
			$this->monstre["id_fk_braldun_cible_monstre"] = null;
		}
		$this->cible["nb_ko_braldun"] = $this->cible["nb_ko_braldun"] + 1;
		$this->cible["est_ko_braldun"] = "oui";
		$this->cible["date_fin_tour_braldun"] = date("Y-m-d H:i:s");
		$id_type_evenement = self::$config->game->evenements->type->kobraldun;
		$details = "[m".$this->monstre["id_monstre"]."] a mis KO [b".$this->cible["id_braldun"]."]";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $id_type_evenement, $details, $this->monstre["niveau_monstre"], "", $this->view, $this->cible["nb_dla_jouees_braldun"], $this->monstre["nb_dla_jouees_monstre"], Bral_Util_Evenement::ATTAQUE_REUSSIE);

		return $details;
	}

	protected function getDetailsBotAttaque($cible, $jetAttaquant, $jetCible, $jetDegat = 0, $critique = false, $pvPerdus = 0, $koCible = false, $pieceCibleAbimee = null) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBotAttaque - enter");
		$retour = "";

		$retour .= "Vous avez été ";
		if ($this->competence["nom_systeme_mcompetence"] == "charger") {
			$retour .= "chargé";
		} else {
			$retour .= "attaqué";
		}
		$retour .= " par ".$this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].")";

		$retour .= PHP_EOL."Jet d'attaque : ".$jetAttaquant;
		$retour .= PHP_EOL."Jet de défense : ".$jetCible;
		$retour .= PHP_EOL."Jet de dégâts : ".$jetDegat;

		if ($jetAttaquant > $jetCible) {
			if ($critique) {
				$retour .= PHP_EOL."Vous avez été touché par une attaque critique";
			} else {
				$retour .= PHP_EOL."Vous avez été touché";
			}

			if ($this->cible["armure_naturelle_braldun"] > 0) {
				$retour .= PHP_EOL."Votre armure naturelle vous a protégé.";
			} else {
				$retour .= PHP_EOL."Votre armure naturelle ne vous a pas protégé (ARM NAT:".$this->cible["armure_naturelle_braldun"].")";
			}

			if ($this->cible["armure_equipement_braldun"] > 0) {
				$retour .= PHP_EOL."Votre équipement vous a protégé.";
			} else {
				$retour .= PHP_EOL."Aucun équipement ne vous a protégé (ARM EQU:".$this->cible["armure_equipement_braldun"].")";
			}

			$totalArmure = $this->cible["armure_equipement_braldun"] + $this->cible["armure_naturelle_braldun"] + $this->cible["armure_bm_braldun"];
			if ($totalArmure < 0) {
				$totalArmure = 0;
			}

			$retour .= PHP_EOL."Au total, votre armure vous a protégé en réduisant les dégâts de ".$totalArmure.".";
				
			if ($pieceCibleAbimee != null) {
				$retour .= PHP_EOL."Une pièce d'équipement a été abimée par le coup : ".$pieceCibleAbimee.".";
			}
				
			$retour .= PHP_EOL."Vous avez perdu ".$pvPerdus. " PV ";
			$retour .= PHP_EOL."Il vous reste ".$this->cible["pv_restant_braldun"]." PV ";

			if ($koCible) {
				$retour .= PHP_EOL."Vous avez été mis KO";
			}
		} else if ($jetCible/2 < $jetAttaquant) { // esquive
			$retour .= PHP_EOL."Vous avez esquivé l'attaque";
		} else { // esquive parfaite
			$retour .= PHP_EOL."Vous avez esquivé parfaitement l'attaque";
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBotAttaque - exit");
		return $retour;
	}

}