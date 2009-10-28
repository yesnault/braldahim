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
abstract class Bral_Monstres_Competences_Attaque {

	protected $monstre = null;
	protected $cible = null;
	protected $view = null;
	protected static $config = null;

	public function __construct($competence, &$monstre, $cible, $view) {
		$this->competence = $competence;
		$this->monstre = &$monstre;
		$this->cible = $cible;
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
		if (($this->cible["x_hobbit"] > $this->monstre["x_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["x_hobbit"] < $this->monstre["x_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["y_hobbit"] > $this->monstre["y_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["y_hobbit"] < $this->monstre["y_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible en dehors de la vue hx=".$this->cible["x_hobbit"] ." hy=".$this->cible["y_hobbit"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if (($this->cible["x_hobbit"] != $this->monstre["x_monstre"]) || ($this->cible["y_hobbit"] != $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre (".$this->monstre["id_monstre"].") cible (".$this->cible["id_hobbit"].") sur une case differente");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if ($this->monstre["pa_monstre"] < $this->competence["pa_utilisation_mcompetence"]) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") insuffisant nb=".$this->monstre["pa_monstre"]." requis=".$competence["pa_utilisation_mcompetence"]);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit false");
			return false; // cible non morte
		} else {
			return true;
		}
	}

	abstract function actionSpecifique();
	abstract function calculJetAttaque();
	abstract function calculDegat($estCritique);

	protected function updateCible() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - enter (id_hobbit=".$this->cible["id_hobbit"].")");

		Bral_Util_Attaque::calculStatutEngage(&$this->cible, true);

		// Mise a jour de la cible
		$hobbitTable = new Hobbit();
		$data = array(
			'pv_restant_hobbit' => $this->cible["pv_restant_hobbit"],
			'est_ko_hobbit' => $this->cible["est_ko_hobbit"],
			'nb_ko_hobbit' => $this->cible["nb_ko_hobbit"],
			'agilite_bm_hobbit' => $this->cible["agilite_bm_hobbit"],
			'est_engage_hobbit' => $this->cible["est_engage_hobbit"],
			'est_engage_next_dla_hobbit' => $this->cible["est_engage_next_dla_hobbit"],
			'date_fin_tour_hobbit' => $this->cible["date_fin_tour_hobbit"],
			'est_quete_hobbit' => $this->cible["est_quete_hobbit"],
			'bm_attaque_hobbit' => $this->cible["bm_attaque_hobbit"],
			'bm_defense_hobbit' => $this->cible["bm_defense_hobbit"],
		);
		$where = "id_hobbit=".$this->cible["id_hobbit"];
		$hobbitTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - exit");
	}

	protected function calculJetCible($cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - enter");
		$jetCible = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_hobbit"]);
		$jetCible = $jetCible + $this->cible["agilite_bm_hobbit"] + $this->cible["bm_defense_hobbit"] + $this->cible["agilite_bbdf_hobbit"];

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
				if (Bral_Util_Commun::getEffetMotX($this->cible["id_hobbit"]) == true) {
					$critique = false;
				} else {
					$critique = true;
				}
			}
			$jetDegat = $this->calculDegat($critique);
			$jetDegat = Bral_Util_Commun::getEffetMotA($this->cible["id_hobbit"], $jetDegat);

			$pvPerdus = $jetDegat - $this->cible["armure_naturelle_hobbit"] - $this->cible["armure_equipement_hobbit"];
			if ($pvPerdus <= 0) {
				$pvPerdus = 1; // on perd 1 pv quoi qu'il arrive
			}
			$this->cible["pv_restant_hobbit"] = $this->cible["pv_restant_hobbit"] - $pvPerdus;
			if ($this->cible["pv_restant_hobbit"]  <= 0) {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - Ko de la cible La cible (".$this->cible["id_hobbit"].") par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus);
				$koCible = true;
				$this->monstre["nb_kill_monstre"] = $this->monstre["nb_kill_monstre"] + 1;
				$this->monstre["id_fk_hobbit_cible_monstre"] = null;
				$this->cible["nb_ko_hobbit"] = $this->cible["nb_ko_hobbit"] + 1;
				$this->cible["est_ko_hobbit"] = "oui";
				$this->cible["date_fin_tour_hobbit"] = date("Y-m-d H:i:s");
				$id_type_evenement = self::$config->game->evenements->type->kohobbit;
				$id_type_evenement_cible = self::$config->game->evenements->type->ko;
				$details = "[m".$this->monstre["id_monstre"]."] a mis KO le hobbit [h".$this->cible["id_hobbit"]."]";
				Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $id_type_evenement, $details, $this->monstre["niveau_monstre"], "", $this->view);
				$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus, $koCible);
				Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
				$this->updateCible();
			} else {
				Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - Survie de la cible La cible (".$this->cible["id_hobbit"].") attaquee par Monstre id:".$this->monstre["id_monstre"]. " pvPerdus=".$pvPerdus. " pv_restant_hobbit=".$this->cible["pv_restant_hobbit"]);
				if ($critique == true) { // En cas de frappe critique : malus en BNS ATT : -2D3. Malus en BNS DEF : -2D6.
					$this->cible["bm_attaque_hobbit"] = $this->cible["bm_attaque_hobbit"] - Bral_Util_De::get_2d3();
					$this->cible["bm_defense_hobbit"] = $this->cible["bm_defense_hobbit"] - Bral_Util_De::get_2d6();
				} else { //  En cas de frappe : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
					$this->cible["bm_attaque_hobbit"] = $this->cible["bm_attaque_hobbit"] - Bral_Util_De::get_1d3();
					$this->cible["bm_defense_hobbit"] = $this->cible["bm_defense_hobbit"] - Bral_Util_De::get_1d6();
				}

				$this->cible["est_ko_hobbit"] = "non";
				$id_type_evenement = self::$config->game->evenements->type->attaquer;
				$details = "[m".$this->monstre["id_monstre"]."] a $verbe le hobbit [h".$this->cible["id_hobbit"]."]";
				$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible, $jetDegat, $critique, $pvPerdus);

				$effetMotS = Bral_Util_Commun::getEffetMotS($this->cible["id_hobbit"]);
				$this->updateCible();
				if ($effetMotS != null) {
					$detailsBot .= PHP_EOL."Le hobbit ".$this->cible["prenom_hobbit"]." ".$this->cible["nom_hobbit"]." (".$this->cible["id_hobbit"] . ") a riposté.";
					$detailsBot .= PHP_EOL."Consultez vos événements pour plus de détails.";

					// mise a jour de l'événement avant la riposte
					Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);

					Bral_Util_Log::viemonstres()->notice("Bral_Monstres_VieMonstre - attaqueCible (idm:".$this->monstre["id_monstre"].") - La cible (".$this->cible["id_hobbit"].") possede le mot S -> Riposte");
					$hobbitTable = new Hobbit();
					$hobbitRowset = $hobbitTable->find($this->cible["id_hobbit"]);
					$hobbitAttaquant = $hobbitRowset->current();
					$jetAttaquant =  Bral_Util_Attaque::calculJetAttaqueNormale($hobbitAttaquant);
					$jetsDegat = Bral_Util_Attaque::calculDegatAttaqueNormale($hobbitAttaquant);
					$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($this->monstre);
					Bral_Util_Attaque::attaqueMonstre($hobbitAttaquant, $this->monstre, $jetAttaquant, $jetCible, $jetsDegat, false, false, true);

				} else { // si pas de riposte, mise a jour de l'événement
					Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
				}
			}

		} else if ($jetCible/2 < $jetAttaquant) {
			// En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
			$this->cible["bm_attaque_hobbit"] = $this->cible["bm_attaque_hobbit"] - Bral_Util_De::get_1d3();
			$this->cible["bm_defense_hobbit"] = $this->cible["bm_defense_hobbit"] - Bral_Util_De::get_1d6();

			$this->updateCible();
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = "[m".$this->monstre["id_monstre"]."] a $verbe le hobbit [h".$this->cible["id_hobbit"]."] qui a esquivé l'attaque";
			$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible);
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
		} else {
			// En cas d'esquive parfaite : Aucun malus appliqué.
			Bral_Util_Attaque::calculStatutEngage(&$this->cible, true);
			$id_type_evenement = self::$config->game->evenements->type->attaquer;
			$details = "[m".$this->monstre["id_monstre"]."] a $verbe le hobbit [h".$this->cible["id_hobbit"]."] qui a esquivé l'attaque parfaitement";
			$detailsBot = $this->getDetailsBotAttaque($this->cible, $jetAttaquant, $jetCible);
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], $this->monstre["id_monstre"], $id_type_evenement, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
		}

		return $koCible;
	}

	protected function getDetailsBotAttaque($cible, $jetAttaquant, $jetCible, $jetDegat = 0, $critique = false, $pvPerdus = 0, $koCible = false) {
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

			if ($this->cible["armure_naturelle_hobbit"] > 0) {
				$retour .= PHP_EOL."Votre armure naturelle vous a protégé en réduisant les dégâts de ";
				$retour .= $this->cible["armure_naturelle_hobbit"].".";
			} else {
				$retour .= PHP_EOL."Votre armure naturelle ne vous a pas protégé (ARM NAT:".$this->cible["armure_naturelle_hobbit"].")";
			}

			if ($this->cible["armure_equipement_hobbit"] > 0) {
				$retour .= PHP_EOL."Votre équipement vous a protégé en réduisant les dégâts de ";
				$retour .= $this->cible["armure_equipement_hobbit"].".";
			} else {
				$retour .= PHP_EOL."Aucun équipement ne vous a protégé (ARM EQU:".$this->cible["armure_equipement_hobbit"].")";
			}

			$retour .= PHP_EOL."Vous avez perdu ".$pvPerdus. " PV ";
			$retour .= PHP_EOL."Il vous reste ".$this->cible["pv_restant_hobbit"]." PV ";

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