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
class Bral_Util_Attaque {

	public static function attaqueBraldun(&$braldunAttaquant, &$braldunCible, $jetAttaquant, $jetCible, $jetsDegat, $view, $degatCase, $effetMotSPossible = true, $tir = false, $enregistreEvenementDansAttaque = false) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - degatCase=".$degatCase);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - effetMotSPossible=".$effetMotSPossible);

		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["attaqueReussie"] = false;
		$retourAttaque["penetrationArmure"] = 0;
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		$retourAttaque["critique"]  = false;
		$retourAttaque["ballonLache"]  = false;
		$retourAttaque["effetMotD"] = false;
		$retourAttaque["effetMotE"] = false;
		$retourAttaque["effetMotG"] = false;
		$retourAttaque["effetMotH"] = false;
		$retourAttaque["effetMotI"] = false;
		$retourAttaque["effetMotJ"] = false;
		$retourAttaque["effetMotL"] = false;
		$retourAttaque["effetMotQ"] = false;
		$retourAttaque["effetMotS"] = false;
		$retourAttaque["idMatchSoule"] = null;
		$retourAttaque["idTypeGroupeMonstre"] = null;
		$retourAttaque["etape"] = false;
		$retourAttaque["gains"] = null;

		$retourAttaque["attaquantDeltaPointsGredin"] = null;
		$retourAttaque["attaquantDeltaPointsRedresseur"] = null;
		$retourAttaque["cibleDeltaPointsGredin"] = null;
		$retourAttaque["cibleDeltaPointsRedresseur"] = null;
		$retourAttaque["nouvelleDistinction"] = null;
		$retourAttaque["contratModifie"] = false;

		$cible = array('nom_cible' => $braldunCible->prenom_braldun ." ". $braldunCible->nom_braldun,
			'id_cible' => $braldunCible->id_braldun, 
			'x_cible' => $braldunCible->x_braldun, 
			'y_cible' => $braldunCible->y_braldun,
			'niveau_cible' => $braldunCible->niveau_braldun,
			'armure_naturelle_braldun' => $braldunCible->armure_naturelle_braldun,
			'armure_equipement_braldun' => $braldunCible->armure_equipement_braldun,
			'armure_bm_braldun' => $braldunCible->armure_bm_braldun,
			'est_ko_braldun' => $braldunCible->est_ko_braldun,
			'est_engage_braldun' => $braldunCible->est_engage_braldun,
			'est_engage_next_dla_braldun' => $braldunCible->est_engage_next_dla_braldun,
			'date_fin_tour_braldun' => $braldunCible->date_fin_tour_braldun,
			'type_cible' => "braldun",
		);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - attaqueBraldun - jetAttaquant".$retourAttaque["jetAttaquant"]. " jetCible=".$retourAttaque["jetCible"]);

		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) { // attaque reussie
			self::calculAttaqueBraldunReussie($detailsBot, $retourAttaque, $braldunAttaquant, $braldunCible, $jetsDegat, $view, $config, $degatCase, $effetMotSPossible, $tir, $enregistreEvenementDansAttaque);
		} else if ($retourAttaque["jetCible"] / 2 <= $retourAttaque["jetAttaquant"]) { // esquive normale
			self::calculAttaqueBraldunEsquivee($detailsBot, $retourAttaque, $braldunAttaquant, $braldunCible, $view, $config, $effetMotSPossible, $enregistreEvenementDansAttaque);
		} else { // esquive parfaite
			self::calculAttaqueBraldunParfaitementEsquivee($detailsBot, $retourAttaque, $braldunAttaquant, $braldunCible, $view, $config, $effetMotSPossible, $enregistreEvenementDansAttaque);
		}

		self::calculAttaqueBraldunRiposte($detailsBot, $retourAttaque, $braldunAttaquant, $braldunCible, $view, $config, $effetMotSPossible, $degatCase);

		if ($tir == false) { //pour un tir l'attaquant n'est pas engagé
			self::calculStatutEngage(&$braldunAttaquant, true);
		}
		self::calculStatutEngage(&$braldunCible, true);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueBraldun - exit -");
		return $retourAttaque;
	}

	private static function calculPointsAttaque(&$braldunAttaquant, &$braldunCible, &$retourAttaque) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculPointsAttaque - enter -");

		if ($braldunAttaquant->est_soule_braldun == "oui") {
			return;
		}

		if ($braldunCible->points_gredin_braldun <= 0) { // cible sans points de gredin
			$braldunAttaquant->points_gredin_braldun = $braldunAttaquant->points_gredin_braldun + 1;
			$retourAttaque["attaquantDeltaPointsGredin"] = 1;
			if ($braldunAttaquant->points_redresseur_braldun > 0) { // s'il est redresseur
				$braldunAttaquant->points_redresseur_braldun = $braldunAttaquant->points_redresseur_braldun - 3;
				$retourAttaque["attaquantDeltaPointsRedresseur"] = -3;
			}
		} elseif ($braldunAttaquant->points_gredin_braldun <= 0 && $braldunCible->points_gredin_braldun > 0) { // redresseur et cible gredin
			$braldunAttaquant->points_redresseur_braldun = $braldunAttaquant->points_redresseur_braldun + 1;
			$retourAttaque["attaquantDeltaPointsRedresseur"] = 1;
		}

		if ($braldunAttaquant->points_redresseur_braldun < 0) {
			$braldunAttaquant->points_redresseur_braldun = 0;
		}

		if ($braldunAttaquant->points_gredin_braldun < 0) {
			$braldunAttaquant->points_gredin_braldun = 0;
		}

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculPointsAttaque - exit -");
	}

	private static function calculPointsKo(&$braldunAttaquant, &$braldunCible, &$retourAttaque) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculPointsKo - enter -");

		if ($braldunAttaquant->est_soule_braldun == "oui") {
			return;
		}

		if ($braldunAttaquant->niveau_braldun <= $braldunCible->niveau_braldun) {
			Zend_Loader::loadClass("Bral_Util_Distinction");
			$retourAttaque["nouvelleDistinction"] = Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldunAttaquant->id_braldun, $braldunAttaquant->niveau_braldun, Bral_Util_Distinction::ID_TYPE_KO_NIVEAU_SUPERIEUR_OU_EGAL);
		}

		if ($braldunCible->points_redresseur_braldun > 0) {
			$braldunAttaquant->nb_ko_redresseurs_suite_braldun = $braldunAttaquant->nb_ko_redresseurs_suite_braldun + 1;
			$braldunAttaquant->nb_ko_redresseur_braldun = $braldunAttaquant->nb_ko_redresseur_braldun + 1;
		}

		if ($braldunCible->points_gredin_braldun > 0) {
			$braldunAttaquant->nb_ko_gredins_suite_braldun = $braldunAttaquant->nb_ko_gredins_suite_braldun + 1;
			$braldunAttaquant->points_gredin_braldun = $braldunAttaquant->points_gredin_braldun + 1;
		}

		if ($braldunCible->points_gredin_braldun == 0 && $braldunCible->points_redresseur_braldun == 0) {
			$braldunAttaquant->nb_ko_neutre_braldun = $braldunAttaquant->nb_ko_neutre_braldun;
		}

		if ($braldunCible->points_gredin_braldun <= 0) { // cible sans points de gredin
			$braldunCible->points_redresseur_braldun = $braldunCible->points_redresseur_braldun - 3;
			$braldunAttaquant->points_gredin_braldun = $braldunAttaquant->points_gredin_braldun + 3;
			$retourAttaque["cibleDeltaPointsRedresseur"] = -3;
			$retourAttaque["attaquantDeltaPointsGredin"] = -3;
			if ($braldunAttaquant->points_redresseur_braldun > 0) { // s'il est redresseur
				$braldunAttaquant->points_redresseur_braldun = $braldunAttaquant->points_redresseur_braldun - 3;
				$retourAttaque["attaquantDeltaPointsRedresseur"] = -3;
			}
		} elseif ($braldunAttaquant->points_gredin_braldun <= 0 && $braldunCible->points_gredin_braldun > 0) { // redresseur et cible gredin
			$delta = $braldunAttaquant->points_redresseur_braldun + 1 + floor($braldunCible->points_gredin_braldun / 10);
			$braldunAttaquant->points_redresseur_braldun = $delta;
			$braldunCible->points_gredin_braldun = $braldunCible->points_gredin_braldun - 3;
			$retourAttaque["attaquantDeltaPointsRedresseur"] = $delta;
			$retourAttaque["cibleDeltaPointsGredin"] = -3;
		}

		if ($braldunAttaquant->points_redresseur_braldun < 0) {
			$braldunAttaquant->points_redresseur_braldun = 0;
		}

		if ($braldunAttaquant->points_gredin_braldun < 0) {
			$braldunAttaquant->points_gredin_braldun = 0;
		}

		if ($braldunCible->points_redresseur_braldun < 0) {
			$braldunCible->points_redresseur_braldun = 0;
		}

		if ($braldunCible->points_gredin_braldun < 0) {
			$braldunCible->points_gredin_braldun = 0;
		}

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculPointsKo - exit -");
	}

	private static function calculAttaqueBraldunReussie(&$detailsBot, &$retourAttaque, &$braldunAttaquant, &$braldunCible, $jetsDegat, $view, $config, $degatCase, $effetMotSPossible, $tir, $enregistreEvenementDansAttaque) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunReussie - enter -");

		$retourAttaque["attaqueReussie"] = true;

		if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque critique");
			if (Bral_Util_Commun::getEffetMotX($braldunCible->id_braldun) == true) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotX true, pas de critique");
				$retourAttaque["critique"]  = false;
			} else {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotX false, critique");
				$retourAttaque["critique"]  = true;
			}
		}

		if ($retourAttaque["critique"] == true) {
			$retourAttaque["jetDegat"] = $jetsDegat["critique"];
		} else {
			$retourAttaque["jetDegat"] = $jetsDegat["noncritique"];
		}

		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - jetDegat avant effetMotA=".$retourAttaque["jetDegat"]);
		$retourAttaque["jetDegat"] = Bral_Util_Commun::getEffetMotA($braldunCible->id_braldun, $retourAttaque["jetDegat"]);
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - jetDegat apres effetMotA=".$retourAttaque["jetDegat"]);

		if (!$degatCase) {
			$effetMotE = Bral_Util_Commun::getEffetMotE($braldunAttaquant->id_braldun);
			if ($effetMotE != null && $effetMotSPossible == true) {
				$retourAttaque["effetMotE"] = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE) {
					$gainPv = $effetMotE;
				}
				$retourAttaque["effetMotEPoints"] = $gainPv;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE True effetMotE=".$effetMotE." gainPv=".$gainPv);

				$braldunAttaquant->pv_restant_braldun = $braldunAttaquant->pv_restant_braldun + $gainPv;
				if ($braldunAttaquant->pv_restant_braldun > $braldunAttaquant->pv_max_braldun + $braldunAttaquant->pv_max_bm_braldun) {
					$braldunAttaquant->pv_restant_braldun = $braldunAttaquant->pv_max_braldun + $braldunAttaquant->pv_max_bm_braldun;
				}
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE braldunAttaquant->pv_restant_braldun=".$braldunAttaquant->pv_restant_braldun. " braldunAttaquant->pv_max_braldun=".($braldunAttaquant->pv_max_braldun + $braldunAttaquant->pv_max_bm_braldun));
			}
		}

		$effetMotG = Bral_Util_Commun::getEffetMotG($braldunAttaquant->id_braldun);
		if ($effetMotG != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotG"] = true;
			$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotG True (degats ajoutes=".$effetMotG."), jetDegat apres MotG =".$retourAttaque["jetDegat"]);
		}

		$effetMotI = Bral_Util_Commun::getEffetMotI($braldunAttaquant->id_braldun);
		if ($effetMotI != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotI"] = true;
			$braldunCible->regeneration_bm_braldun = $braldunCible->regeneration_bm_braldun + $effetMotI;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotI True (regeneration ajoutee=".$effetMotI."), braldunCible->regeneration_bm_braldun=".$braldunCible->regeneration_bm_braldun);
		}

		$effetMotJ = Bral_Util_Commun::getEffetMotJ($braldunAttaquant->id_braldun);
		if ($effetMotJ != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotJ"] = true;
			$braldunCible->vue_malus_braldun = $braldunCible->vue_malus_braldun + $effetMotJ;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotJ True (vue malus ajoutee=".$effetMotJ."), braldunCible->vue_malus_braldun=".$braldunCible->vue_malus_braldun);
			$braldunCible->vue_bm_braldun = $braldunCible->vue_bm_braldun + $braldunCible->vue_malus_braldun;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - braldunCible->vue_bm_braldun=".$braldunCible->vue_bm_braldun);
		}

		$effetMotQ = Bral_Util_Commun::getEffetMotQ($braldunAttaquant->id_braldun);
		if ($effetMotQ != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotQ"]= true;
			$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun + $effetMotQ;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotQ True (defense malus=".$effetMotQ."), braldunCible->bm_defense_braldun=".$braldunCible->bm_defense_braldun);
			$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun + $braldunCible->bm_defense_braldun;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - braldunCible->bm_defense_braldun=".$braldunCible->bm_defense_braldun);
		}

		// pour le tir
		if ($tir == true) {
			$penetrationArmure = floor(($braldunAttaquant->agilite_bm_braldun + $braldunAttaquant->agilite_bbdf_braldun + $braldunAttaquant->sagesse_bm_braldun + $braldunAttaquant->sagesse_bbdf_braldun)/2);
			if ($penetrationArmure < 0) {
				$penetrationArmure = 0;
			}
			$retourAttaque["penetrationArmure"] = $penetrationArmure;
			$braldunCible->armure_equipement_braldun = $braldunCible->armure_equipement_braldun - $penetrationArmure;
			if ($braldunCible->armure_equipement_braldun < 0) {
				$braldunCible->armure_equipement_braldun = 0;
			}
			$retourAttaque["cible"]["armure_equipement_braldun"] = $braldunCible->armure_equipement_braldun;
		}

		$armureTotale = $braldunCible->armure_naturelle_braldun + $braldunCible->armure_equipement_braldun + $braldunCible->armure_bm_braldun;
		if ($armureTotale < 0) {
			$armureTotale = 0;
		}
		$retourAttaque["jetDegatReel"] = $retourAttaque["jetDegat"] - $armureTotale;

		$retourAttaque["arm_nat_cible"] = $braldunCible->armure_naturelle_braldun;
		$retourAttaque["arm_eqpt_cible"] = $braldunCible->armure_equipement_braldun;
		$retourAttaque["arm_totale_cible"] = $armureTotale;

		//le jet de degat est au moins égal à 1
		if ($retourAttaque["jetDegatReel"] <= 0 ) {
			$retourAttaque["jetDegatReel"] = 1;
		}

		$pvTotalAvecDegat = $braldunCible->pv_restant_braldun - $retourAttaque["jetDegatReel"];

		if ($pvTotalAvecDegat < $braldunCible->pv_restant_braldun) {
			$braldunCible->pv_restant_braldun = $pvTotalAvecDegat;
		}

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$pieceCibleAbimee = Bral_Util_Equipement::usureAttaquePiece($braldunCible->id_braldun);

		if ($braldunCible->pv_restant_braldun <= 0) { // mort du braldun
			$braldunCible->pv_restant_braldun = 0;

			if ($braldunAttaquant->est_soule_braldun == "non") {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - KO du braldun !");
				$braldunCible->est_ko_braldun = "oui";
				$braldunCible->nb_ko_braldun = $braldunCible->nb_ko_braldun + 1;
				$braldunAttaquant->nb_braldun_ko_braldun = $braldunAttaquant->nb_braldun_ko_braldun + 1;
			} else {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Plaquage du braldun !");
				$braldunCible->est_ko_braldun = "oui";
				$braldunCible->nb_plaque_braldun = $braldunCible->nb_plaque_braldun + 1;
				$braldunAttaquant->nb_braldun_plaquage_braldun = $braldunAttaquant->nb_braldun_plaquage_braldun + 1;

				Zend_Loader::loadClass("Bral_Util_Soule");
				$retourAttaque["ballonLache"] = Bral_Util_Soule::calcuLacheBallon($braldunCible, true);
				Bral_Util_Soule::majPlaquage($braldunAttaquant, $braldunCible);
			}

			$braldunCible->date_fin_tour_braldun = date("Y-m-d H:i:s");

			$effetH = Bral_Util_Commun::getEffetMotH($braldunAttaquant->id_braldun);
			if ($effetH == true && $effetMotSPossible == true) {
				$retourAttaque["effetMotH"] = true;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotH True");
			}

			if (Bral_Util_Commun::getEffetMotL($braldunAttaquant->id_braldun) == true && $effetMotSPossible == true) {
				$braldunAttaquant->pa_braldun = $braldunAttaquant->pa_braldun + 3;
				$retourAttaque["effetMotL"] = true;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True braldunAttaquant->pa_braldun=".$braldunAttaquant->pa_braldun);
			}

			$retourAttaque["mort"] = true;
			if ($braldunAttaquant->est_soule_braldun == "non") {
				$nbCastars = Bral_Util_Commun::dropBraldunCastars($braldunCible, $effetH);
				$braldunCible->castars_braldun = $braldunCible->castars_braldun - $nbCastars;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - nbCastars=".$nbCastars);
				$retourAttaque["gains"]["gainCastars"] = $nbCastars;
				if ($braldunCible->castars_braldun < 0) {
					$braldunCible->castars_braldun = 0;
				}
			}
			self::calculPointsKo($braldunAttaquant, $braldunCible, $retourAttaque);
			Zend_Loader::loadClass("Bral_Util_Contrat");
			$retourAttaque["contratModifie"] = Bral_Util_Contrat::action($braldunAttaquant, $braldunCible);

			if ($retourAttaque["contratModifie"] != null) {
				Zend_Loader::loadClass("Bral_Util_Distinction");
				$retourAttaque["nouvelleDistinction"] = Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldunAttaquant->id_braldun, $braldunAttaquant->niveau_braldun, Bral_Util_Distinction::ID_TYPE_KO_1_WANTED);
			}
		} else {

			if ($retourAttaque["critique"] == true) { // En cas de frappe : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
				$braldunCible->bm_attaque_braldun = $braldunCible->bm_attaque_braldun - Bral_Util_De::get_1d3();
				$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun - Bral_Util_De::get_1d6();
			} else { //En cas de frappe critique : malus en BNS ATT : -2D3. Malus en BNS DEF : -2D6.
				$braldunCible->bm_attaque_braldun = $braldunCible->bm_attaque_braldun - Bral_Util_De::get_2d3();
				$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun - Bral_Util_De::get_2d6();
			}

			$braldunCible->est_ko_braldun = "non";
			$retourAttaque["mort"] = false;
			$retourAttaque["fragilisee"] = true;

			if ($retourAttaque["critique"] == true) {
				Zend_Loader::loadClass("Bral_Util_Soule");
				$retourAttaque["ballonLache"] = Bral_Util_Soule::calcuLacheBallon($braldunCible, false);
			}

			self::calculPointsAttaque($braldunAttaquant, $braldunCible, $retourAttaque);
		}
		$data = array(
				'castars_braldun' => $braldunCible->castars_braldun,
				'pv_restant_braldun' => $braldunCible->pv_restant_braldun,
				'est_ko_braldun' => $braldunCible->est_ko_braldun,
				'nb_ko_braldun' => $braldunCible->nb_ko_braldun,
				'date_fin_tour_braldun' => $braldunCible->date_fin_tour_braldun,
				'regeneration_bm_braldun' => $braldunCible->regeneration_bm_braldun,
				'vue_bm_braldun' => $braldunCible->vue_bm_braldun,
				'vue_malus_braldun' => $braldunCible->vue_malus_braldun,
				'agilite_bm_braldun' => $braldunCible->agilite_bm_braldun,
				'agilite_malus_braldun' => $braldunCible->agilite_malus_braldun,
				'nb_plaque_braldun' => $braldunCible->nb_plaque_braldun,
				'bm_attaque_braldun' => $braldunCible->bm_attaque_braldun,
				'bm_defense_braldun' => $braldunCible->bm_defense_braldun,
				'points_gredin_braldun' => $braldunCible->points_gredin_braldun,
				'points_redresseur_braldun' => $braldunCible->points_redresseur_braldun,
		);
		$where = "id_braldun=".$braldunCible->id_braldun;
		$braldunTable = new Braldun();
		$braldunTable->update($data, $where);

		if ($braldunAttaquant->est_soule_braldun == "non") {
			$details = "[b".$braldunAttaquant->id_braldun."]";
			$retourAttaque["idMatchSoule"]  = null;
			if ($retourAttaque["mort"] == true) {
				$retourAttaque["typeEvenement"] = $config->game->evenements->type->kobraldun;
				$idTypeEvenementCible = $config->game->evenements->type->ko;
				$details .=" a mis KO ";
			} else {
				$retourAttaque["typeEvenement"] = $config->game->evenements->type->attaquer;
				$idTypeEvenementCible = $retourAttaque["typeEvenement"];
				if ($tir) {
					$details .=" a tiré sur ";
				} else {
					$details .=" a attaqué ";
				}
			}
		} else { // soule
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->soule;
			$idTypeEvenementCible = $retourAttaque["typeEvenement"];
			$details = "[b".$braldunAttaquant->id_braldun."]";
			$retourAttaque["idMatchSoule"]  = $braldunAttaquant->id_fk_soule_match_braldun;
			if ($retourAttaque["mort"] == true) {
				$details .=" a plaqué ";
			} elseif ($tir) {
				$details .=" a tiré sur ";
			} else {
				$details .=" a attaqué ";
			}
		}

		$details .= " [b".$retourAttaque["cible"]["id_cible"]."]";

		if ($retourAttaque["ballonLache"] == true) {
			$details .= ". Le ballon est tombé à terre !";
		}

		$detailsBot .= self::getDetailsBot($retourAttaque, $braldunAttaquant, $retourAttaque["cible"], "braldun", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"] , $retourAttaque["jetDegat"], $retourAttaque["ballonLache"], $retourAttaque["critique"], $retourAttaque["mort"], $pieceCibleAbimee);
		if ($effetMotSPossible == false) {
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $retourAttaque["typeEvenement"], $details, $detailsBot, $braldunAttaquant->niveau_braldun, null, null, null, null, Bral_Util_Evenement::RIPOSTE); // uniquement en cas de riposte
		}

		if ($retourAttaque["mort"] == false) {
			Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $idTypeEvenementCible, $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "braldun", true, $view);
			//				Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot);  // fait dans competence.php avec le détail du résulat
		} else {
			Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $idTypeEvenementCible, $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "braldun", true, $view);
			//				Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot);
		}

		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mise a jour du braldun ".$braldunCible->id_braldun." pv_restant_braldun=".$braldunCible->pv_restant_braldun);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunReussie - exit -");
	}

	private static function calculAttaqueBraldunEsquivee(&$detailsBot, &$retourAttaque, &$braldunAttaquant, &$braldunCible, $view, $config, $effetMotSPossible, $enregistreEvenementDansAttaque) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunEsquivee - enter -");
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque esquivee malus sur ajoute a agilite_bm_braldun=".$braldunCible->niveau_braldun);

		$braldunCible->bm_attaque_braldun = $braldunCible->bm_attaque_braldun - Bral_Util_De::get_1d3();
		$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun - Bral_Util_De::get_1d6();

		//En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
		$data = array(
			'bm_attaque_braldun' => $braldunCible->bm_attaque_braldun,
			'bm_defense_braldun' => $braldunCible->bm_defense_braldun,
		);

		$where = "id_braldun=".$braldunCible->id_braldun;
		$braldunTable = new Braldun();
		$braldunTable->update($data, $where);
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = true;

		if ($braldunAttaquant->est_soule_braldun == "non") {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->attaquer;
			$retourAttaque["idMatchSoule"]  = null;
		} else { // soule
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->soule;
			$retourAttaque["idMatchSoule"]  = $braldunAttaquant->id_fk_soule_match_braldun;
		}
		$details = "[b".$braldunAttaquant->id_braldun."] a attaqué [b".$retourAttaque["cible"]["id_cible"]."]";
		$details .= " qui a esquivé l'attaque";
		$detailsBot .= self::getDetailsBot($retourAttaque, $braldunAttaquant, $retourAttaque["cible"], "braldun", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
		if ($effetMotSPossible == false) {
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $retourAttaque["typeEvenement"], $details, $detailsBot, $braldunAttaquant->niveau_braldun, null, null, null, null, Bral_Util_Evenement::RIPOSTE); // uniquement en cas de riposte
		}
		Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $retourAttaque["typeEvenement"], $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "braldun", true, $view);

		if ($enregistreEvenementDansAttaque) {
			$idTypeEvenement = $config->game->evenements->type->attaquer;
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun); // fait dans competence.php avec le détail du résulat sinon
		}

		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunEsquivee - exit -");
	}

	private static function calculAttaqueBraldunParfaitementEsquivee(&$detailsBot, &$retourAttaque, &$braldunAttaquant, &$braldunCible, $view, $config, $effetMotSPossible, $enregistreEvenementDansAttaque) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunParfaitementEsquivee - enter -");

		if ($braldunAttaquant->est_soule_braldun == "non") {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->attaquer;
			$retourAttaque["idMatchSoule"]  = null;
		} else {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->soule;
			$retourAttaque["idMatchSoule"]  = $braldunAttaquant->id_fk_soule_match_braldun;
		}
		$details = "[b".$braldunAttaquant->id_braldun."] a attaqué [b".$retourAttaque["cible"]["id_cible"]."]";
		$detailsBot .= self::getDetailsBot($retourAttaque, $braldunAttaquant, $retourAttaque["cible"], "braldun", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
		$details .= " qui a esquivé parfaitement l'attaque";
		if ($effetMotSPossible == false) {
			$detailsBot .= " Riposte de ".$braldunAttaquant->prenom_braldun ." ". $braldunAttaquant->nom_braldun ." (".$braldunAttaquant->id_braldun.")".PHP_EOL;
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $retourAttaque["typeEvenement"], $details, $detailsBot, $braldunAttaquant->niveau_braldun, null, null, null, null,  Bral_Util_Evenement::RIPOSTE); // uniquement en cas de riposte
		}
		Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $retourAttaque["typeEvenement"], $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "braldun", true, $view);

		if ($enregistreEvenementDansAttaque) {
			$idTypeEvenement = $config->game->evenements->type->attaquer;
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun); // fait dans competence.php avec le détail du résulat sinon
		}
		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunParfaitementEsquivee - exit -");
	}

	private static function calculAttaqueBraldunRiposte(&$detailsBot, &$retourAttaque, &$braldunAttaquant, &$braldunCible, $view, $config, $effetMotSPossible, $degatCase) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunRiposte - enter -");

		$peutRiposter = self::verificationNbRiposte($braldunAttaquant->nb_dla_jouees_braldun, $braldunAttaquant->id_braldun);
		if ($peutRiposter != true) {
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunRiposte - ".$braldunAttaquant->id_braldun." ne peut pas riposter");
			return;
		}
			
		if ($effetMotSPossible == true && $retourAttaque["mort"] == false) {
			$effetMotS = Bral_Util_Commun::getEffetMotS($braldunCible->id_braldun);
			if ($effetMotS != null) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Debut !");
				$retourAttaque["effetMotS"] = true;
				$jetAttaquantRiposte = Bral_Util_Attaque::calculJetAttaqueNormale($braldunCible);
				$jetCibleRiposte = Bral_Util_Attaque::calculJetCibleBraldun($braldunAttaquant);
				$jetsDegatRiposte = Bral_Util_Attaque::calculDegatAttaqueNormale($braldunCible);
				$retourAttaque["retourAttaqueEffetMotS"] = self::attaqueBraldun($braldunCible, $braldunAttaquant, $jetAttaquantRiposte, $jetCibleRiposte, $jetsDegatRiposte, $view, $degatCase, false);
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Fin !");
					
				$detailsBot .= PHP_EOL."Le braldun ".$retourAttaque["cible"]["prenom_braldun"]." ".$retourAttaque["cible"]["nom_braldun"]." (".$retourAttaque["cible"]["id_braldun"] . ") a riposté.";
				$detailsBot .= PHP_EOL."Consultez vos événements pour plus de détails.";
			}

			if ($degatCase) {
				$details .= " (compétence spéciale utilisée) ";
				Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun, "braldun", true, $view, $retourAttaque["idMatchSoule"]);
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueBraldunRiposte - exit -");
	}

	public static function attaqueMonstre(&$braldunAttaquant, &$monstre, $jetAttaquant, $jetCible, $jetsDegat, $view, $degatCase, $tir=false, $riposte = false, $enregistreEvenementDansAttaque = false) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - degatSurCase=".$degatCase);

		if ($riposte) {
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - riposte:true");
		}

		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["jetDegat"] = 0;
		$retourAttaque["jetDegatReel"] = 0;
		$retourAttaque["penetrationArmure"] = 0;
		$retourAttaque["attaqueReussie"] = false;

		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		$retourAttaque["critique"] = false;

		$retourAttaque["effetMotD"] = false;
		$retourAttaque["effetMotE"] = false;
		$retourAttaque["effetMotG"] = false;
		$retourAttaque["effetMotH"] = false;
		$retourAttaque["effetMotI"] = false;
		$retourAttaque["effetMotJ"] = false;
		$retourAttaque["effetMotL"] = false;
		$retourAttaque["effetMotQ"] = false;
		$retourAttaque["effetMotS"] = false;
		$retourAttaque["ballonLache"] = false;
		$retourAttaque["idTypeGroupeMonstre"] = $monstre["id_fk_type_groupe_monstre"];
		$retourAttaque["etape"] = false;
		$retourAttaque["gains"] = null;

		$retourAttaque["attaqueReussie"] = false;

		$retourAttaque["attaquantDeltaPointsGredin"] = null;
		$retourAttaque["attaquantDeltaPointsRedresseur"] = null;
		$retourAttaque["cibleDeltaPointsGredin"] = null;
		$retourAttaque["cibleDeltaPointsRedresseur"] = null;
		$retourAttaque["nouvelleDistinction"] = null;
		$retourAttaque["contratModifie"] = false;

		if ($monstre["genre_type_monstre"] == 'feminin') {
			$m_taille = $monstre["nom_taille_f_monstre"];
		} else {
			$m_taille = $monstre["nom_taille_m_monstre"];
		}

		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille,
			'id_cible' => $monstre["id_monstre"], 
			'niveau_cible' => $monstre["niveau_monstre"], 
			'x_cible' => $monstre["x_monstre"], 
			'y_cible' => $monstre["y_monstre"],
			'type_cible' => "monstre",
		);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetAttaquant=".$retourAttaque["jetAttaquant"]. " jetCible=".$retourAttaque["jetCible"]);
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
			$retourAttaque["attaqueReussie"] = true;

			if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque critique");
				$retourAttaque["critique"]  = true;
			}

			if ($retourAttaque["critique"] == true) {
				$retourAttaque["jetDegat"] = $jetsDegat["critique"];
			} else {
				$retourAttaque["jetDegat"] = $jetsDegat["noncritique"];
			}

			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetDegat=".$retourAttaque["jetDegat"]);

			// si c'est un gibier = pas d'effet de mot
			if ($monstre["id_fk_type_groupe_monstre"] != $config->game->groupe_monstre->type->gibier) {
				if (!$degatCase) {
					$effetMotE = Bral_Util_Commun::getEffetMotE($braldunAttaquant->id_braldun);
					if ($effetMotE != null) {
						$retourAttaque["effetMotE"] = true;
						$gainPv = ($retourAttaque["jetDegat"] / 2);
						if ($gainPv > $effetMotE) {
							$gainPv = $effetMotE;
						}
						$retourAttaque["effetMotEPoints"] = $gainPv;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE True effetMotE=".$effetMotE." gainPv=".$gainPv);

						$braldunAttaquant->pv_restant_braldun = $braldunAttaquant->pv_restant_braldun + $gainPv;
						if ($braldunAttaquant->pv_restant_braldun > $braldunAttaquant->pv_max_braldun + $braldunAttaquant->pv_max_bm_braldun) {
							$braldunAttaquant->pv_restant_braldun = $braldunAttaquant->pv_max_braldun + $braldunAttaquant->pv_max_bm_braldun;
						}
					}
				}

					
				$effetMotG = Bral_Util_Commun::getEffetMotG($braldunAttaquant->id_braldun);
				if ($effetMotG != null) {
					$retourAttaque["effetMotG"] = true;
					$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotG True (degats ajoutes=".$effetMotG."), jetDegat apres MotG =".$retourAttaque["jetDegat"]);
				}

				$effetMotI = Bral_Util_Commun::getEffetMotI($braldunAttaquant->id_braldun);
				if ($effetMotI != null) {
					$retourAttaque["effetMotI"] = true;
					$monstre["regeneration_malus_monstre"] = $monstre["regeneration_malus_monstre"] + $effetMotI;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotI True (regeneration ajoutee=".$effetMotI."), monstre->regeneration_malus_monstre=".$monstre["regeneration_malus_monstre"]);
				}

				$effetMotJ = Bral_Util_Commun::getEffetMotJ($braldunAttaquant->id_braldun);
				if ($effetMotJ != null) {
					$retourAttaque["effetMotJ"] = true;
					$monstre["vue_malus_monstre"] = $monstre["vue_malus_monstre"] + $effetMotJ;
				}

				$effetMotQ = Bral_Util_Commun::getEffetMotQ($braldunAttaquant->id_braldun);
				if ($effetMotQ != null) {
					$retourAttaque["effetMotQ"] = true;
					$monstre["agilite_malus_monstre"] = $monstre["agilite_malus_monstre"] + $effetMotQ;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotQ True (agilite malus=".$effetMotQ."), monstre->agilite_malus_monstre=".$monstre["agilite_malus_monstre"]);
					$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
				}
			}

			$armureNaturelle = $monstre["armure_naturelle_monstre"];
			if ($tir == true) {
				$penetrationArmure = floor(($braldunAttaquant->agilite_bm_braldun + $braldunAttaquant->agilite_bbdf_braldun + $braldunAttaquant->sagesse_bm_braldun + $braldunAttaquant->sagesse_bbdf_braldun)/2);
				if ($penetrationArmure < 0) {
					$penetrationArmure = 0;
				}
				$retourAttaque["penetrationArmure"] = $penetrationArmure;
				$armureNaturelle = $armureNaturelle - $penetrationArmure;
				if ($armureNaturelle < 0) {
					$armureNaturelle = 0;
				}
			}

			//on enlève l'armure naturelle du monstre
			$retourAttaque["jetDegatReel"] = $retourAttaque["jetDegat"] - $armureNaturelle;
			//le jet de degat est au moins égal à 1
			if ($retourAttaque["jetDegatReel"] <= 0 ) {
				$retourAttaque["jetDegatReel"] = 1;
			}

			$retourAttaque["arm_nat_cible"] = $armureNaturelle;
			$retourAttaque["arm_eqpt_cible"] = 0;
			$retourAttaque["arm_totale_cible"] = $monstre["armure_naturelle_monstre"];

			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre avant degat=".$monstre["pv_restant_monstre"]);
			$monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegatReel"];
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre apres degat=".$monstre["pv_restant_monstre"]);

			if ($monstre["pv_restant_monstre"] <= 0) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mort du monstre !");
				$effetD = null;
				$effetH = null;

				// si c'est un gibier, on n'incrémente pas de compteur, pas d'effet de mot non plus
				if ($monstre["id_fk_type_groupe_monstre"] != $config->game->groupe_monstre->type->gibier) {

					$braldunAttaquant->nb_monstre_kill_braldun = $braldunAttaquant->nb_monstre_kill_braldun + 1;

					$effetD = Bral_Util_Commun::getEffetMotD($braldunAttaquant->id_braldun);
					if ($effetD != 0) {
						$retourAttaque["effetMotD"]= true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetD=".$effetD);
					}

					$effetH = Bral_Util_Commun::getEffetMotH($braldunAttaquant->id_braldun);
					if ($effetH == true) {
						$retourAttaque["effetMotH"] = true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetH=".$effetH);
					}

					if (Bral_Util_Commun::getEffetMotL($braldunAttaquant->id_braldun) == true) {
						$braldunAttaquant->pa_braldun = $braldunAttaquant->pa_braldun + 3;
						$retourAttaque["effetMotL"] = true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True braldunAttaquant->pa_braldun=".$braldunAttaquant->pa_braldun);
					}
				}

				Zend_Loader::loadClass("Bral_Util_Quete");
				$retourAttaque["etape"] = Bral_Util_Quete::etapeTuer($braldunAttaquant, $monstre["id_fk_taille_monstre"], $monstre["id_fk_type_monstre"], $monstre["niveau_monstre"]);

				$retourAttaque["mort"] = true;
				$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
				$retourAttaque["gains"] = $vieMonstre->mortMonstreDb($cible["id_cible"], $effetD, $effetH, $braldunAttaquant->niveau_braldun, $view);
			} else {
				if ($retourAttaque["critique"] == false) { // En cas de frappe : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
					$monstre["bm_attaque_monstre"] = $monstre["bm_attaque_monstre"] - Bral_Util_De::get_1d3();
					$monstre["bm_defense_monstre"] = $monstre["bm_defense_monstre"] - Bral_Util_De::get_1d6();
				} else { // En cas de frappe critique : malus en BNS ATT : -2D3. Malus en BNS DEF : -2D6.
					$monstre["bm_attaque_monstre"] = $monstre["bm_attaque_monstre"] - Bral_Util_De::get_2d3();
					$monstre["bm_defense_monstre"] = $monstre["bm_defense_monstre"] - Bral_Util_De::get_2d6();
				}

				$retourAttaque["fragilisee"] = true;
				$retourAttaque["mort"] = false;
				$data = array(
					'pv_restant_monstre' => $monstre["pv_restant_monstre"],
					'regeneration_malus_monstre' => $monstre["regeneration_malus_monstre"],
					'vue_malus_monstre' => $monstre["vue_malus_monstre"],
					'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
					'agilite_malus_monstre' => $monstre["agilite_malus_monstre"],
					'bm_attaque_monstre' => $monstre["bm_attaque_monstre"],
					'bm_defense_monstre' => $monstre["bm_defense_monstre"],
				);
				$where = "id_monstre=".$cible["id_cible"];
				$monstreTable = new Monstre();
				$monstreTable->update($data, $where);

				// malus sur la durée du tour
			}
		} else if ($retourAttaque["jetCible"] / 2 <= $retourAttaque["jetAttaquant"]) { // esquive normale
			// En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
			$monstre["bm_attaque_monstre"] = $monstre["bm_attaque_monstre"] - Bral_Util_De::get_1d3();
			$monstre["bm_defense_monstre"] = $monstre["bm_defense_monstre"] - Bral_Util_De::get_1d6();

			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque esquivee malus sur ajoute a bm_attaque_monstre et bm_defense_monstre");

			$retourAttaque["mort"] = false;
			$data = array(
				'bm_attaque_monstre' => $monstre["bm_attaque_monstre"],
				'bm_defense_monstre' => $monstre["bm_defense_monstre"],
			);
			$where = "id_monstre=".$cible["id_cible"];
			$monstreTable = new Monstre();
			$monstreTable->update($data, $where);
			$retourAttaque["fragilisee"] = true;
		}

		$detailsBot = self::getDetailsBot($retourAttaque, $braldunAttaquant, $cible, "monstre", $retourAttaque["jetAttaquant"], $retourAttaque["jetCible"], $retourAttaque["jetDegat"], $retourAttaque["ballonLache"], $retourAttaque["critique"], $retourAttaque["mort"]) ;

		$libelleMonstreGibier = "monstre";
		if ($monstre["id_fk_type_groupe_monstre"] == $config->game->groupe_monstre->type->gibier) {
			$libelleMonstreGibier = "gibier";
		}
			
		if ($retourAttaque["mort"] === true) {

			if ($monstre["id_fk_type_groupe_monstre"] == $config->game->groupe_monstre->type->gibier) {
				$idTypeEvenement = $config->game->evenements->type->killgibier;
			} else {
				$idTypeEvenement = $config->game->evenements->type->killmonstre;
			}

			$details = "[b".$braldunAttaquant->id_braldun."] a tué le ".$libelleMonstreGibier." [m".$cible["id_cible"]."]";
			if ($enregistreEvenementDansAttaque) {
				Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun); // fait dans competence.php avec le détail du résulat sinon
			}
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $idTypeEvenement, $details, "", $cible["niveau_cible"], "monstre");
		} else {
			$idTypeEvenement = $config->game->evenements->type->attaquer;
			if ($tir) {
				$verbe =" a tiré sur ";
			} else {
				$verbe =" a attaqué ";
			}
			$details = " [b".$braldunAttaquant->id_braldun."] ".$verbe." le ".$libelleMonstreGibier." [m".$cible["id_cible"]."]";

			if ($retourAttaque["jetAttaquant"] * 2 < $retourAttaque["jetCible"]) { // esquive parfaite
				$details .= " qui a esquivé parfaitement";
			} else if ($retourAttaque["jetAttaquant"] <= $retourAttaque["jetCible"]) { // esquive
				$details .= " qui a esquivé ";
			} else { // attaque reussie
				$details .= "";
			}

			if ($enregistreEvenementDansAttaque) {
				Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun);
			}
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $idTypeEvenement, $details, "", $cible["niveau_cible"], "monstre");
		}

		if ($degatCase || $riposte) {
			$details .= " (compétence spéciale utilisée) ";
			$actionEvenement = null;
			if ($riposte) {
				$detailsBot .= " Riposte de ".$braldunAttaquant->prenom_braldun ." ". $braldunAttaquant->nom_braldun ." (".$braldunAttaquant->id_braldun.")".PHP_EOL;
				$actionEvenement = Bral_Util_Evenement::RIPOSTE;
			}
			Bral_Util_Evenement::majEvenements($braldunAttaquant->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldunAttaquant->niveau_braldun, "braldun", null, null, null, $actionEvenement, $braldunAttaquant->nb_dla_jouees_braldun);
		}

		if ($tir==false) {
			//pour un tir l'attaquant n'est pas engagé
			self::calculStatutEngage(&$braldunAttaquant, true);
		}

		$retourAttaque["details"] = $details;
		$retourAttaque["typeEvenement"] = $idTypeEvenement;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - exit -");
		return $retourAttaque;
	}

	public static function calculJetCibleBraldun($braldunCible) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleBraldun - enter -");
		$config = Zend_Registry::get('config');
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleBraldun - config->game->base_agilite=".$config->game->base_agilite." braldunCible->agilite_base_braldun=".$braldunCible->agilite_base_braldun);

		$jetCible = Bral_Util_De::getLanceDe6($config->game->base_agilite + $braldunCible->agilite_base_braldun);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleBraldun - jetCible=".$jetCible);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleBraldun - braldunCible->agilite_bm_braldun=".$braldunCible->agilite_bm_braldun);
		$jetCible = $jetCible + $braldunCible->agilite_bm_braldun + $braldunCible->agilite_bbdf_braldun + $braldunCible->bm_defense_braldun;
		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - calculJetCibleBraldun - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleBraldun - exit -");
		return $jetCible;
	}

	public static function calculJetCibleMonstre($monstre) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - enter -");
		$config = Zend_Registry::get('config');
		$jetCible = 0;
		if ($monstre["id_fk_type_groupe_monstre"] != $config->game->groupe_monstre->type->gibier) {
			$jetCible = Bral_Util_De::getLanceDe6($config->game->base_agilite + $monstre["agilite_base_monstre"]);
			$jetCible = $jetCible + $monstre["agilite_bm_monstre"] + $monstre["bm_defense_monstre"];
			if ($jetCible < 0) {
				$jetCible = 0;
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - exit -");
		return $jetCible;
	}

	public static function calculJetAttaqueNormale($braldun) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetAttaquant = Bral_Util_De::getLanceDe6($config->game->base_agilite + $braldun->agilite_base_braldun);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant=".$jetAttaquant);
		$jetAttaquant = $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun;
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant + agilite_bm_braldun + bm_attaque_braldun =".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		return $jetAttaquant;
	}

	public static function calculDegatAttaqueNormale($braldun) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;

		$jetDegat["critique"] = Bral_Util_De::getLanceDe6(($config->game->base_force + $braldun->force_base_braldun) * $coefCritique);
		$jetDegat["noncritique"] = Bral_Util_De::getLanceDe6($config->game->base_force + $braldun->force_base_braldun);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - critique=".$jetDegat["critique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - noncritique=".$jetDegat["noncritique"]);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - braldun->force_bm_braldun=".$braldun->force_bm_braldun);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - braldun->force_bbdf_braldun=".$braldun->force_bbdf_braldun);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - braldun->bm_degat_braldun=".$braldun->bm_degat_braldun);

		$jetDegat["critique"] = floor($jetDegat["critique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun);
		$jetDegat["noncritique"] = floor($jetDegat["noncritique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - critique=".$jetDegat["critique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - noncritique=".$jetDegat["noncritique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - exit -");
		return $jetDegat;
	}

	public static function calculDegatCase($config, $braldun, $degats, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCase - enter -");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$retour["braldunMorts"] = null;
		$retour["braldunTouches"] = null;
		$retour["monstreMorts"] = null;
		$retour["monstreTouches"] = null;
		$retour["n_cible"] = 0;

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($braldun->x_braldun, $braldun->y_braldun);
		if ($estRegionPvp) {
			self::calculDegatCaseBraldun($config, $braldun, $degats, $retour, $view);
		}
		self::calculDegatCaseMonstre($config, $braldun, $degats, $retour, $view);
		$retour["n_cible"] = count($retour["braldunTouches"]) + count($retour["monstreTouches"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCase - exit -");
		return $retour;
	}

	public static function calculDegatCaseBraldun($config, $braldun, $degats, &$retour, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseBraldun - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun, $braldun->id_braldun, false);

		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;

		$i = 0;
		foreach($bralduns as $h) {
			$braldunRowset = $braldunTable->find($h["id_braldun"]);
			$braldunCible = $braldunRowset->current();
			$retour["braldunTouches"][$i]["braldun"] = $h;
			$retour["braldunTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueBraldun($braldun, $braldunCible, $jetAttaquant, $jetCible, $jetsDegat, $view, true);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseBraldun - exit -");
		return $retour;
	}

	public static function calculDegatCaseMonstre($config, $braldun, $degats, &$retour, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;

		$i = 0;
		foreach($monstres as $m) {
			$retour["monstreTouches"][$i]["monstre"] = $m;
			$retour["monstreTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueMonstre($braldun, $m, $jetAttaquant, $jetCible, $jetsDegat, $view, true);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - exit -");
		return $retour;
	}

	public static function calculSoinCase($config, $braldun, $soins) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - enter -");
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun, $braldun->id_braldun, false);
		$retour["braldunTouches"] = null;
		$i = 0;
		foreach($bralduns as $h) {
			$retour["braldunTouches"][$i]["braldun"] = $h;
			$retour["braldunTouches"][$i]["retourAttaque"] = null;
			$i++;
			if ($h["pv_max_braldun"] >  $h["pv_restant_braldun"]) {
				$h["pv_restant_braldun"] = $h["pv_restant_braldun"] + $soins;
				if ($h["pv_restant_braldun"] > $h["pv_max_braldun"]) {
					$h["pv_restant_braldun"] = $h["pv_max_braldun"];
				}
				$data = array("pv_restant_braldun" => $h["pv_restant_braldun"]);
					
				$where = "id_braldun = ".$h["id_braldun"];
				$braldunTable->update($data, $where);
					
				$idTypeEvenement = $config->game->evenements->type->effet;
				$details = " [b".$braldun->id_braldun."] a soigné [b".$h["id_braldun"]."]";
				$detailsBot = $soins." PV soigné";
				if ($soins > 1) {
					$detailsBot = $detailsBot . "s";
				}
				Bral_Util_Evenement::majEvenements($braldun->id_braldun, $idTypeEvenement, $details, $detailsBot, $braldun->niveau_braldun);
				Bral_Util_Evenement::majEvenements($h["id_braldun"], $idTypeEvenement, $details, $detailsBot, $h["niveau_braldun"]);
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - exit -");
		return $retour;
	}

	private static function getDetailsBot($retourAttaque, $braldunAttaquant, $cible, $typeCible, $jetAttaquant, $jetCible, $jetDegat = 0, $ballonLache = false, $critique = false, $mortCible = false, $pieceCibleAbimee = null) {
		$retour = "";
		$retour .= $braldunAttaquant->prenom_braldun ." ". $braldunAttaquant->nom_braldun ." (".$braldunAttaquant->id_braldun.")";

		if ($mortCible) {
			if ($typeCible == "monstre") {
				$retour .= " a tué";
			} else {
				$retour .= " a mis KO";
			}
		} else {
			$retour .= " a attaqué";
		}
		$retour .= " ".$cible["nom_cible"]." (".$cible["id_cible"] . ")";

		if ($jetAttaquant <= $jetCible) {
			if ($jetCible > $jetAttaquant * 2) {
				$retour .= " qui a esquivé parfaitement";
			} else {
				$retour .= " qui a esquivé";
			}
		}

		$retour .= PHP_EOL."Jet d'attaque : ".$jetAttaquant;
		$retour .= PHP_EOL."Jet de défense : ".$jetCible;

		if ($jetAttaquant > $jetCible) {
			$retour .= PHP_EOL."Jet de dégâts : ".$jetDegat;

			if ($critique) {
				$retour .= PHP_EOL."La cible a été touchée par une attaque critique";
			} else {
				$retour .= PHP_EOL."La cible a été touchée";
			}

			if (array_key_exists('armure_naturelle_braldun', $cible) && array_key_exists('armure_equipement_braldun', $cible) && array_key_exists('armure_bm_braldun', $cible)) {
				if ($cible["armure_naturelle_braldun"] > 0) {
					$retour .= PHP_EOL."L'armure naturelle l'a protégé.";
				} else {
					$retour .= PHP_EOL."L'armure naturelle ne l'a pas protégé (ARM NAT:".$cible["armure_naturelle_braldun"].")";
				}
					
				if ($cible["armure_equipement_braldun"] > 0) {
					$retour .= PHP_EOL."L'équipement l'a protégé.";
				} else {
					$retour .= PHP_EOL."Aucun équipement ne l'a protégé (ARM EQU:".$cible["armure_equipement_braldun"].")";
				}

				$totalArmure = $cible["armure_equipement_braldun"] + $cible["armure_naturelle_braldun"] + $cible["armure_bm_braldun"];
				if ($totalArmure < 0) {
					$totalArmure = 0;
				}
					
				$retour .= PHP_EOL."Au total, votre armure vous a protégé en réduisant les dégâts de ".$totalArmure.".";
			}

			if ($pieceCibleAbimee != null) {
				$retour .= PHP_EOL."Une pièce d'équipement a été abimée par le coup : ".$pieceCibleAbimee.".";
			}

			if ($mortCible) {
				if ($typeCible == "monstre") {
					$retour .= PHP_EOL."La cible a été tuée";
				} else {
					$retour .= PHP_EOL."La cible a été mise KO";
				}
			}
		} else if ($jetCible > $jetAttaquant * 2) { // esquive
			$retour .= PHP_EOL."La cible a esquivé parfaitement l'attaque";
		} else { // esquive parfaite
			$retour .= PHP_EOL."La cible a esquivé l'attaque";
		}

		if ($ballonLache) {
			$retour .= PHP_EOL."Le ballon de soule est tombé à terre !".PHP_EOL;
		}

		if ($retourAttaque["attaquantDeltaPointsGredin"] != null) {
			$retour .=  PHP_EOL."Influence les points de Gredin de l'attaquant: ".$retourAttaque["attaquantDeltaPointsGredin"];
		}

		if ($retourAttaque["attaquantDeltaPointsRedresseur"] != null) {
			$retour .= PHP_EOL."Influence sur les points de Redreseur de Torts de l'attaquant: ".$retourAttaque["attaquantDeltaPointsRedresseur"];
		}

		if ($retourAttaque["cibleDeltaPointsGredin"] != null) {
			$retour .= PHP_EOL."Influence sur vos points de Gredin : ".$retourAttaque["cibleDeltaPointsGredin"];
		}

		if ($retourAttaque["cibleDeltaPointsRedresseur"] != null) {
			$retour .= PHP_EOL."Influence sur vos points de Redresseur de Torts : ".$retourAttaque["cibleDeltaPointsRedresseur"];
		}

		return $retour;
	}

	public static function estRegionPvp($x, $y) {
		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findByCase($x, $y);
		unset($regionTable);

		if ($region["est_pvp_region"] == "oui") {
			return  true;
		} else {
			return false;
		}
	}

	public static function calculStatutEngage(&$braldun, $updateDbAFaire = false) {
		$est_engage_braldun = 'non';
		$est_engage_next_dla_braldun = 'non';

		$c = "stdClass";

		if ($braldun instanceof $c) {
			$est_ko_braldun = $braldun->est_ko_braldun;
			$est_engage_braldun = $braldun->est_engage_braldun;
			$est_engage_next_dla_braldun = $braldun->est_engage_next_dla_braldun;
			$date_fin_tour_braldun = $braldun->date_fin_tour_braldun;
		} else {
			$est_ko_braldun = $braldun["est_ko_braldun"];
			$est_engage_braldun = $braldun["est_engage_braldun"];
			$est_engage_next_dla_braldun = $braldun["est_engage_next_dla_braldun"];
			$date_fin_tour_braldun = $braldun["date_fin_tour_braldun"];
		}

		if ($est_ko_braldun == 'non') {
			$est_engage_braldun = 'oui';
			$date_courante = date("Y-m-d H:i:s");
			// si le braldun n'a pas encore activé ce tour
			if ($date_fin_tour_braldun < $date_courante) {
				$est_engage_next_dla_braldun = 'oui';
			}
		}

		if ($braldun instanceof $c) {
			$braldun->est_engage_braldun = $est_engage_braldun;
			$braldun->est_engage_next_dla_braldun = $est_engage_next_dla_braldun;
			$idBraldun = $braldun->id_braldun;
		} else {
			$braldun["est_engage_braldun"] = $est_engage_braldun;
			$braldun["est_engage_next_dla_braldun"] = $est_engage_next_dla_braldun;
			$idBraldun = $braldun["id_braldun"];
		}

		if ($updateDbAFaire) {
			self::updateDbStatutEngage($idBraldun, $est_engage_braldun, $est_engage_next_dla_braldun);
		}
	}

	private static function updateDbStatutEngage($idBraldun, $est_engage_braldun, $est_engage_next_dla_braldun) {
		$data = array(
			'est_engage_braldun' => $est_engage_braldun,
			'est_engage_next_dla_braldun' => $est_engage_next_dla_braldun,
		);
		$where = "id_braldun=".$idBraldun;
		$braldunTable = new Braldun();
		$braldunTable->update($data, $where);
	}

	public static function verificationNbRiposte($numTour, $idBraldun) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - verificationNbRiposte - enter -");
		$evenementTable = new Evenement();
		$nbRiposte = $evenementTable->countByIdBraldunTourCourant($numTour, $idBraldun, Bral_Util_Evenement::RIPOSTE);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - verificationNbRiposte - nbRiposte:".$nbRiposte);
		if ($nbRiposte >= 1) {
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - verificationNbRiposte - exit false");
			return false;
		} else {
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - verificationNbRiposte - exit true");
			return true;
		}
	}
}

