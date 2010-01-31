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

	public static function attaqueHobbit(&$hobbitAttaquant, &$hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $view, $degatCase, $effetMotSPossible = true, $tir=false) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - degatCase=".$degatCase);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - effetMotSPossible=".$effetMotSPossible);

		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["attaqueReussie"] = false;
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
		$retourAttaque["etape"] = false;
		$retourAttaque["gains"] = null;

		$cible = array('nom_cible' => $hobbitCible->prenom_hobbit ." ". $hobbitCible->nom_hobbit,
			'id_cible' => $hobbitCible->id_hobbit, 
			'x_cible' => $hobbitCible->x_hobbit, 
			'y_cible' => $hobbitCible->y_hobbit,
			'niveau_cible' => $hobbitCible->niveau_hobbit,
			'armure_naturelle_hobbit' => $hobbitCible->armure_naturelle_hobbit,
			'armure_equipement_hobbit' => $hobbitCible->armure_equipement_hobbit,
			'est_ko_hobbit' => $hobbitCible->est_ko_hobbit,
			'est_engage_hobbit' => $hobbitCible->est_engage_hobbit,
			'est_engage_next_dla_hobbit' => $hobbitCible->est_engage_next_dla_hobbit,
			'date_fin_tour_hobbit' => $hobbitCible->date_fin_tour_hobbit,
			'type_cible' => "hobbit",
		);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - attaqueHobbit - jetAttaquant".$retourAttaque["jetAttaquant"]. " jetCible=".$retourAttaque["jetCible"]);

		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) { // attaque reussie
			self::calculAttaqueHobbitReussie($detailsBot, $retourAttaque, $hobbitAttaquant, $hobbitCible, $jetsDegat, $view, $config, $degatCase, $effetMotSPossible, $tir);
		} else if ($retourAttaque["jetCible"] / 2 <= $retourAttaque["jetAttaquant"]) { // esquive normale
			self::calculAttaqueHobbitEsquivee($detailsBot, $retourAttaque, $hobbitAttaquant, $hobbitCible, $view, $config, $effetMotSPossible);
		} else { // esquive parfaite
			self::calculAttaqueHobbitParfaitementEsquivee($detailsBot, $retourAttaque, $hobbitAttaquant, $hobbitCible, $view, $config, $effetMotSPossible);
		}

		self::calculAttaqueHobbitRiposte($detailsBot, $retourAttaque, $hobbitAttaquant, $hobbitCible, $view, $config, $effetMotSPossible, $degatCase);

		if ($tir == false) { //pour un tir l'attaquant n'est pas engagé
			self::calculStatutEngage(&$hobbitAttaquant, true);
		}
		self::calculStatutEngage(&$hobbitCible, true);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - exit -");
		return $retourAttaque;
	}

	private static function calculAttaqueHobbitReussie(&$detailsBot, &$retourAttaque, &$hobbitAttaquant, &$hobbitCible, $jetsDegat, $view, $config, $degatCase, $effetMotSPossible, $tir) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitReussie - enter -");

		$retourAttaque["attaqueReussie"] = true;

		if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque critique");
			if (Bral_Util_Commun::getEffetMotX($hobbitCible->id_hobbit) == true) {
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
		$retourAttaque["jetDegat"] = Bral_Util_Commun::getEffetMotA($hobbitCible->id_hobbit, $retourAttaque["jetDegat"]);
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - jetDegat apres effetMotA=".$retourAttaque["jetDegat"]);

		if (!$degatCase) {
			$effetMotE = Bral_Util_Commun::getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null && $effetMotSPossible == true) {
				$retourAttaque["effetMotE"] = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				$retourAttaque["effetMotEPoints"] = $gainPv;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE True effetMotE=".$effetMotE." gainPv=".$gainPv);

				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit + $gainPv;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit + $hobbitAttaquant->pv_max_bm_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit + $hobbitAttaquant->pv_max_bm_hobbit;
				}
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE hobbitAttaquant->pv_restant_hobbit=".$hobbitAttaquant->pv_restant_hobbit. " hobbitAttaquant->pv_max_hobbit=".($hobbitAttaquant->pv_max_hobbit + $hobbitAttaquant->pv_max_bm_hobbit));
			}
		}

		$effetMotG = Bral_Util_Commun::getEffetMotG($hobbitAttaquant->id_hobbit);
		if ($effetMotG != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotG"] = true;
			$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotG True (degats ajoutes=".$effetMotG."), jetDegat apres MotG =".$retourAttaque["jetDegat"]);
		}

		$effetMotI = Bral_Util_Commun::getEffetMotI($hobbitAttaquant->id_hobbit);
		if ($effetMotI != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotI"] = true;
			$hobbitCible->regeneration_malus_hobbit = $hobbitCible->regeneration_malus_hobbit + $effetMotI;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotI True (regeneration ajoutee=".$effetMotI."), hobbitCible->regeneration_malus_hobbit=".$hobbitCible->regeneration_malus_hobbit);
		}

		$effetMotJ = Bral_Util_Commun::getEffetMotJ($hobbitAttaquant->id_hobbit);
		if ($effetMotJ != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotJ"] = true;
			$hobbitCible->vue_malus_hobbit = $hobbitCible->vue_malus_hobbit + $effetMotJ;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotJ True (vue malus ajoutee=".$effetMotJ."), hobbitCible->vue_malus_hobbit=".$hobbitCible->vue_malus_hobbit);
			$hobbitCible->vue_bm_hobbit = $hobbitCible->vue_bm_hobbit + $hobbitCible->vue_malus_hobbit;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - hobbitCible->vue_bm_hobbit=".$hobbitCible->vue_bm_hobbit);
		}

		$effetMotQ = Bral_Util_Commun::getEffetMotQ($hobbitAttaquant->id_hobbit);
		if ($effetMotQ != null && $effetMotSPossible == true) {
			$retourAttaque["effetMotQ"]= true;
			$hobbitCible->agilite_malus_hobbit = $hobbitCible->agilite_malus_hobbit + $effetMotQ;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotQ True (agilite malus=".$effetMotQ."), hobbitCible->agilite_malus_hobbit=".$hobbitCible->agilite_malus_hobbit);
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $hobbitCible->agilite_malus_hobbit;
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - hobbitCible->agilite_bm_hobbit=".$hobbitCible->agilite_bm_hobbit);
		}

		// pour le tir
		if ($tir == true) {
			$penetrationArmure = floor(($hobbitCible->agilite_bm_hobbit + $hobbitCible->agilite_bbdf_hobbit + $hobbitCible->sagesse_bm_hobbit + $hobbitCible->sagesse_bbdf_hobbit)/2);
			if ($penetrationArmure < 0) {
				$penetrationArmure = 0;
			}
			$hobbitCible->armure_equipement_hobbit = $hobbitCible->armure_equipement_hobbit - $penetrationArmure;
			if ($hobbitCible->armure_equipement_hobbit < 0) {
				$hobbitCible->armure_equipement_hobbit = 0;
			}
			$retourAttaque["cible"]["armure_equipement_hobbit"] = $hobbitCible->armure_equipement_hobbit;
		}

		$retourAttaque["jetDegatReel"] = $retourAttaque["jetDegat"] - $hobbitCible->armure_naturelle_hobbit - $hobbitCible->armure_equipement_hobbit;

		$retourAttaque["arm_nat_cible"] = $hobbitCible->armure_naturelle_hobbit;
		$retourAttaque["arm_eqpt_cible"] = $hobbitCible->armure_equipement_hobbit;

		//le jet de degat est au moins égal à 1
		if ($retourAttaque["jetDegatReel"] <= 0 ) {
			$retourAttaque["jetDegatReel"] = 1;
		}

		$pvTotalAvecDegat = $hobbitCible->pv_restant_hobbit - $retourAttaque["jetDegatReel"];

		if ($pvTotalAvecDegat < $hobbitCible->pv_restant_hobbit) {
			$hobbitCible->pv_restant_hobbit = $pvTotalAvecDegat;
		}
		if ($hobbitCible->pv_restant_hobbit <= 0) { // mort du hobbit
			$hobbitCible->pv_restant_hobbit = 0;

			if ($hobbitAttaquant->est_soule_hobbit == "non") {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - KO du hobbit !");
				$hobbitCible->est_ko_hobbit = "oui";
				$hobbitCible->nb_ko_hobbit = $hobbitCible->nb_ko_hobbit + 1;
				$hobbitAttaquant->nb_hobbit_ko_hobbit = $hobbitAttaquant->nb_hobbit_ko_hobbit + 1;
			} else {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Plaquage du hobbit !");
				$hobbitCible->est_ko_hobbit = "oui";
				$hobbitCible->nb_plaque_hobbit = $hobbitCible->nb_plaque_hobbit + 1;
				$hobbitAttaquant->nb_hobbit_plaquage_hobbit = $hobbitAttaquant->nb_hobbit_plaquage_hobbit + 1;

				Zend_Loader::loadClass("Bral_Util_Soule");
				$retourAttaque["ballonLache"] = Bral_Util_Soule::calcuLacheBallon($hobbitCible, true);
				Bral_Util_Soule::majPlaquage($hobbitAttaquant, $hobbitCible);
			}

			$hobbitCible->date_fin_tour_hobbit = date("Y-m-d H:i:s");

			$effetH = Bral_Util_Commun::getEffetMotH($hobbitAttaquant->id_hobbit);
			if ($effetH == true && $effetMotSPossible == true) {
				$retourAttaque["effetMotH"] = true;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotH True");
			}

			if (Bral_Util_Commun::getEffetMotL($hobbitAttaquant->id_hobbit) == true && $effetMotSPossible == true) {
				$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 3;
				$retourAttaque["effetMotL"] = true;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True hobbitAttaquant->pa_hobbit=".$hobbitAttaquant->pa_hobbit);
			}

			$retourAttaque["mort"] = true;
			if ($hobbitAttaquant->est_soule_hobbit == "non") {
				$nbCastars = Bral_Util_Commun::dropHobbitCastars($hobbitCible, $effetH);
				$hobbitCible->castars_hobbit = $hobbitCible->castars_hobbit - $nbCastars;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - nbCastars=".$nbCastars);
				$retourAttaque["gains"]["gainCastars"] = $nbCastars;
				if ($hobbitCible->castars_hobbit < 0) {
					$hobbitCible->castars_hobbit = 0;
				}
			}
		} else {

			if ($retourAttaque["critique"] == true) { // En cas de frappe : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
				$hobbitCible->bm_attaque_hobbit = $hobbitCible->bm_attaque_hobbit - Bral_Util_De::get_1d3();
				$hobbitCible->bm_defense_hobbit = $hobbitCible->bm_defense_hobbit - Bral_Util_De::get_1d6();
			} else { //En cas de frappe critique : malus en BNS ATT : -2D3. Malus en BNS DEF : -2D6.
				$hobbitCible->bm_attaque_hobbit = $hobbitCible->bm_attaque_hobbit - Bral_Util_De::get_2d3();
				$hobbitCible->bm_defense_hobbit = $hobbitCible->bm_defense_hobbit - Bral_Util_De::get_2d6();
			}

			//En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.

			$hobbitCible->est_ko_hobbit = "non";
			$retourAttaque["mort"] = false;
			$retourAttaque["fragilisee"] = true;

			if ($retourAttaque["critique"] == true) {
				Zend_Loader::loadClass("Bral_Util_Soule");
				$retourAttaque["ballonLache"] = Bral_Util_Soule::calcuLacheBallon($hobbitCible, false);
			}
		}
		$data = array(
				'castars_hobbit' => $hobbitCible->castars_hobbit,
				'pv_restant_hobbit' => $hobbitCible->pv_restant_hobbit,
				'est_ko_hobbit' => $hobbitCible->est_ko_hobbit,
				'nb_ko_hobbit' => $hobbitCible->nb_ko_hobbit,
				'date_fin_tour_hobbit' => $hobbitCible->date_fin_tour_hobbit,
				'regeneration_malus_hobbit' => $hobbitCible->regeneration_malus_hobbit,
				'vue_bm_hobbit' => $hobbitCible->vue_bm_hobbit,
				'vue_malus_hobbit' => $hobbitCible->vue_malus_hobbit,
				'agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit,
				'agilite_malus_hobbit' => $hobbitCible->agilite_malus_hobbit,
				'nb_plaque_hobbit' => $hobbitCible->nb_plaque_hobbit,
				'bm_attaque_hobbit' => $hobbitCible->bm_attaque_hobbit,
				'bm_defense_hobbit' => $hobbitCible->bm_defense_hobbit,
		);
		$where = "id_hobbit=".$hobbitCible->id_hobbit;
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);

		if ($hobbitAttaquant->est_soule_hobbit == "non") {
			$details = "[h".$hobbitAttaquant->id_hobbit."]";
			$retourAttaque["idMatchSoule"]  = null;
			if ($retourAttaque["mort"] == true) {
				$retourAttaque["typeEvenement"] = $config->game->evenements->type->kohobbit;
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
			$details = "[h".$hobbitAttaquant->id_hobbit."]";
			$retourAttaque["idMatchSoule"]  = $hobbitAttaquant->id_fk_soule_match_hobbit;
			if ($retourAttaque["mort"] == true) {
				$details .=" a plaqué ";
			} elseif ($tir) {
				$details .=" a tiré sur ";
			} else {
				$details .=" a attaqué ";
			}
		}

		$details .= " le hobbit [h".$retourAttaque["cible"]["id_cible"]."]";

		if ($retourAttaque["ballonLache"] == true) {
			$details .= ". Le ballon est tombé à terre !";
		}

		$detailsBot .= self::getDetailsBot($hobbitAttaquant, $retourAttaque["cible"], "hobbit", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"] , $retourAttaque["jetDegat"], $retourAttaque["ballonLache"], $retourAttaque["critique"], $retourAttaque["mort"], $retourAttaque["idMatchSoule"]);
		if ($effetMotSPossible == false) {
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $retourAttaque["typeEvenement"], $details, $detailsBot, $hobbitAttaquant->niveau_hobbit); // uniquement en cas de riposte
		}

		if ($retourAttaque["mort"] == false) {
			Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $idTypeEvenementCible, $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "hobbit", true, $view);
			//				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot);  // fait dans competence.php avec le détail du résulat
		} else {
			Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $idTypeEvenementCible, $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "hobbit", true, $view);
			//				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot);
		}

		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mise a jour du hobbit ".$hobbitCible->id_hobbit." pv_restant_hobbit=".$hobbitCible->pv_restant_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitReussie - exit -");
	}

	private static function calculAttaqueHobbitEsquivee(&$detailsBot, &$retourAttaque, &$hobbitAttaquant, &$hobbitCible, $view, $config, $effetMotSPossible) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitEsquivee - enter -");

		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque esquivee malus sur ajoute a agilite_bm_hobbit=".$hobbitCible->niveau_hobbit);

		$hobbitCible->bm_attaque_hobbit = $hobbitCible->bm_attaque_hobbit - Bral_Util_De::get_1d3();
		$hobbitCible->bm_defense_hobbit = $hobbitCible->bm_defense_hobbit - Bral_Util_De::get_1d6();

		//En cas d'esquive : malus en BNS ATT : -1D3. Malus en BNS DEF : -1D6.
		$data = array(
			'bm_attaque_hobbit' => $hobbitCible->bm_attaque_hobbit,
			'bm_defense_hobbit' => $hobbitCible->bm_defense_hobbit,
		);

		$where = "id_hobbit=".$hobbitCible->id_hobbit;
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = true;

		if ($hobbitAttaquant->est_soule_hobbit == "non") {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->attaquer;
			$retourAttaque["idMatchSoule"]  = null;
		} else { // soule
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->soule;
			$retourAttaque["idMatchSoule"]  = $hobbitAttaquant->id_fk_soule_match_hobbit;
		}
		$details = "[h".$hobbitAttaquant->id_hobbit."] a attaqué le hobbit [h".$retourAttaque["cible"]["id_cible"]."]";
		$details .= " qui a esquivé l'attaque";
		$detailsBot .= self::getDetailsBot($hobbitAttaquant, $retourAttaque["cible"], "hobbit", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
		if ($effetMotSPossible == false) {
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $retourAttaque["typeEvenement"], $details, $detailsBot, $hobbitAttaquant->niveau_hobbit); // uniquement en cas de riposte
		}
		Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $retourAttaque["typeEvenement"], $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "hobbit", true, $view);
		//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot); // fait dans competence.php avec le détail du résulat

		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitEsquivee - exit -");
	}

	private static function calculAttaqueHobbitParfaitementEsquivee(&$detailsBot, &$retourAttaque, &$hobbitAttaquant, &$hobbitCible, $view, $config, $effetMotSPossible) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitParfaitementEsquivee - enter -");

		if ($hobbitAttaquant->est_soule_hobbit == "non") {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->attaquer;
			$retourAttaque["idMatchSoule"]  = null;
		} else {
			$retourAttaque["typeEvenement"] = $config->game->evenements->type->soule;
			$retourAttaque["idMatchSoule"]  = $hobbitAttaquant->id_fk_soule_match_hobbit;
		}
		$details = "[h".$hobbitAttaquant->id_hobbit."] a attaqué le hobbit [h".$retourAttaque["cible"]["id_cible"]."]";
		$detailsBot .= self::getDetailsBot($hobbitAttaquant, $retourAttaque["cible"], "hobbit", $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
		$details .= " qui a esquivé parfaitement l'attaque";
		if ($effetMotSPossible == false) {
			$detailsBot .= " Riposte de ".$hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.")".PHP_EOL;
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $retourAttaque["typeEvenement"], $details, $detailsBot, $hobbitAttaquant->niveau_hobbit); // uniquement en cas de riposte
		}
		Bral_Util_Evenement::majEvenements($retourAttaque["cible"]["id_cible"], $retourAttaque["typeEvenement"], $details, $detailsBot, $retourAttaque["cible"]["niveau_cible"], "hobbit", true, $view);
		//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot); // fait dans competence.php avec le détail du résulat

		$retourAttaque["details"] = $details;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitParfaitementEsquivee - exit -");
	}

	private static function calculAttaqueHobbitRiposte(&$detailsBot, &$retourAttaque, &$hobbitAttaquant, &$hobbitCible, $view, $config, $effetMotSPossible, $degatCase) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitRiposte - enter -");

		if ($effetMotSPossible == true && $retourAttaque["mort"] == false) {
			$effetMotS = Bral_Util_Commun::getEffetMotS($hobbitCible->id_hobbit);
			if ($effetMotS != null) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Debut !");
				$retourAttaque["effetMotS"] = true;
				$jetAttaquantRiposte = Bral_Util_Attaque::calculJetAttaqueNormale($hobbitCible);
				$jetCibleRiposte = Bral_Util_Attaque::calculJetCibleHobbit($hobbitAttaquant);
				$jetsDegatRiposte = Bral_Util_Attaque::calculDegatAttaqueNormale($hobbitCible);
				$retourAttaque["retourAttaqueEffetMotS"] = self::attaqueHobbit($hobbitCible, $hobbitAttaquant, $jetAttaquantRiposte, $jetCibleRiposte, $jetsDegatRiposte, $view, $degatCase, false);
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Fin !");
					
				$detailsBot .= PHP_EOL."Le hobbit ".$retourAttaque["cible"]["prenom_hobbit"]." ".$retourAttaque["cible"]["nom_hobbit"]." (".$retourAttaque["cible"]["id_hobbit"] . ") a riposté.";
				$detailsBot .= PHP_EOL."Consultez vos événements pour plus de détails.";
			}

			if ($degatCase) {
				$details .= " (compétence spéciale utilisée) ";
				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot, $hobbitAttaquant->niveau_hobbit, "hobbit", true, $view, $retourAttaque["idMatchSoule"]);
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculAttaqueHobbitRiposte - exit -");
	}

	public static function attaqueMonstre(&$hobbitAttaquant, $monstre, $jetAttaquant, $jetCible, $jetsDegat, $view, $degatCase, $tir=false, $riposte = false) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - degatSurCase=".$degatCase);

		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["jetDegat"] = 0;
		$retourAttaque["jetDegatReel"] = 0;
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
		$retourAttaque["etape"] = false;
		$retourAttaque["gains"] = null;

		$retourAttaque["attaqueReussie"] = false;

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
					$effetMotE = Bral_Util_Commun::getEffetMotE($hobbitAttaquant->id_hobbit);
					if ($effetMotE != null) {
						$retourAttaque["effetMotE"] = true;
						$gainPv = ($retourAttaque["jetDegat"] / 2);
						if ($gainPv > $effetMotE * 3) {
							$gainPv = $effetMotE * 3;
						}
						$retourAttaque["effetMotEPoints"] = $gainPv;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE True effetMotE=".$effetMotE." gainPv=".$gainPv);

						$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit + $gainPv;
						if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit + $hobbitAttaquant->pv_max_bm_hobbit) {
							$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit + $hobbitAttaquant->pv_max_bm_hobbit;
						}
					}
				}

					
				$effetMotG = Bral_Util_Commun::getEffetMotG($hobbitAttaquant->id_hobbit);
				if ($effetMotG != null) {
					$retourAttaque["effetMotG"] = true;
					$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotG True (degats ajoutes=".$effetMotG."), jetDegat apres MotG =".$retourAttaque["jetDegat"]);
				}

				$effetMotI = Bral_Util_Commun::getEffetMotI($hobbitAttaquant->id_hobbit);
				if ($effetMotI != null) {
					$retourAttaque["effetMotI"] = true;
					$monstre["regeneration_malus_monstre"] = $monstre["regeneration_malus_monstre"] + $effetMotI;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotI True (regeneration ajoutee=".$effetMotI."), monstre->regeneration_malus_monstre=".$monstre["regeneration_malus_monstre"]);
				}

				$effetMotJ = Bral_Util_Commun::getEffetMotJ($hobbitAttaquant->id_hobbit);
				if ($effetMotJ != null) {
					$retourAttaque["effetMotJ"] = true;
					$monstre["vue_malus_monstre"] = $monstre["vue_malus_monstre"] + $effetMotJ;
				}

				$effetMotQ = Bral_Util_Commun::getEffetMotQ($hobbitAttaquant->id_hobbit);
				if ($effetMotQ != null) {
					$retourAttaque["effetMotQ"] = true;
					$monstre["agilite_malus_monstre"] = $monstre["agilite_malus_monstre"] + $effetMotQ;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotQ True (agilite malus=".$effetMotQ."), monstre->agilite_malus_monstre=".$monstre["agilite_malus_monstre"]);
					$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
				}
			}

			//on enlève l'armure naturelle du monstre
			$retourAttaque["jetDegatReel"] = $retourAttaque["jetDegat"] - $monstre["armure_naturelle_monstre"];
			//le jet de degat est au moins égal à 1
			if ($retourAttaque["jetDegatReel"] <= 0 ) {
				$retourAttaque["jetDegatReel"] = 1;
			}

			$retourAttaque["arm_nat_cible"] = $monstre["armure_naturelle_monstre"];
			$retourAttaque["arm_eqpt_cible"] = 0;

			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre avant degat=".$monstre["pv_restant_monstre"]);
			$monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegatReel"];
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre apres degat=".$monstre["pv_restant_monstre"]);

			if ($monstre["pv_restant_monstre"] <= 0) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mort du monstre !");
				$effetD = null;
				$effetH = null;

				// si c'est un gibier, on n'incrémente pas de compteur, pas d'effet de mot non plus
				if ($monstre["id_fk_type_groupe_monstre"] != $config->game->groupe_monstre->type->gibier) {

					$hobbitAttaquant->nb_monstre_kill_hobbit = $hobbitAttaquant->nb_monstre_kill_hobbit + 1;

					$effetD = Bral_Util_Commun::getEffetMotD($hobbitAttaquant->id_hobbit);
					if ($effetD != 0) {
						$retourAttaque["effetMotD"]= true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetD=".$effetD);
					}

					$effetH = Bral_Util_Commun::getEffetMotH($hobbitAttaquant->id_hobbit);
					if ($effetH == true) {
						$retourAttaque["effetMotH"] = true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetH=".$effetH);
					}

					if (Bral_Util_Commun::getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
						$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 3;
						$retourAttaque["effetMotL"] = true;
						Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True hobbitAttaquant->pa_hobbit=".$hobbitAttaquant->pa_hobbit);
					}
				}

				Zend_Loader::loadClass("Bral_Util_Quete");
				$retourAttaque["etape"] = Bral_Util_Quete::etapeTuer($hobbitAttaquant, $monstre["id_fk_taille_monstre"], $monstre["id_fk_type_monstre"], $monstre["niveau_monstre"]);

				$retourAttaque["mort"] = true;
				$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
				$retourAttaque["gains"] = $vieMonstre->mortMonstreDb($cible["id_cible"], $effetD, $effetH, $hobbitAttaquant->niveau_hobbit, $view);
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

		$detailsBot = self::getDetailsBot($hobbitAttaquant, $cible, "monstre", $retourAttaque["jetAttaquant"], $retourAttaque["jetCible"], $retourAttaque["jetDegat"], $retourAttaque["ballonLache"], $retourAttaque["critique"], $retourAttaque["mort"]) ;

		$libelleMonstreGibier = "monstre";
		if ($monstre["id_fk_type_groupe_monstre"] == $config->game->groupe_monstre->type->gibier) {
			$libelleMonstreGibier = "gibier";
		}
			
		if ($retourAttaque["mort"] === true) {
			$idTypeEvenement = $config->game->evenements->type->killmonstre;
			$details = "[h".$hobbitAttaquant->id_hobbit."] a tué le ".$libelleMonstreGibier." [m".$cible["id_cible"]."]";
			//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot); // fait dans competence.php avec le détail du résulat
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $config->game->evenements->type->killmonstre, $details, "", $cible["niveau_cible"], "monstre");
		} else {
			$idTypeEvenement = $config->game->evenements->type->attaquer;
			if ($tir) {
				$verbe =" a tiré sur ";
			} else {
				$verbe =" a attaqué ";
			}
			$details = " [h".$hobbitAttaquant->id_hobbit."] ".$verbe." le ".$libelleMonstreGibier." [m".$cible["id_cible"]."]";

			if ($retourAttaque["jetAttaquant"] * 2 < $retourAttaque["jetCible"]) { // esquive parfaite
				$details .= " qui a esquivé parfaitement";
			} else if ($retourAttaque["jetAttaquant"] <= $retourAttaque["jetCible"]) { // esquive
				$details .= " qui a esquivé ";
			} else { // attaque reussie
				$details .= "";
			}

			//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot);
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $idTypeEvenement, $details, "", $cible["niveau_cible"], "monstre");
		}

		if ($degatCase || $riposte) {
			$details .= " (compétence spéciale utilisée) ";
			if ($riposte) {
				$detailsBot .= " Riposte de ".$hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.")".PHP_EOL;
			}
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $idTypeEvenement, $details, $detailsBot, $hobbitAttaquant->niveau_hobbit);
		}

		if ($tir==false) {
			//pour un tir l'attaquant n'est pas engagé
			self::calculStatutEngage(&$hobbitAttaquant, true);
		}

		$retourAttaque["details"] = $details;
		$retourAttaque["typeEvenement"] = $idTypeEvenement;

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - exit -");
		return $retourAttaque;
	}

	public static function calculJetCibleHobbit($hobbitCible) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - enter -");
		$config = Zend_Registry::get('config');
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - config->game->base_agilite=".$config->game->base_agilite." hobbitCible->agilite_base_hobbit=".$hobbitCible->agilite_base_hobbit);

		$jetCible = Bral_Util_De::getLanceDe6($config->game->base_agilite + $hobbitCible->agilite_base_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - jetCible=".$jetCible);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - hobbitCible->agilite_bm_hobbit=".$hobbitCible->agilite_bm_hobbit);
		$jetCible = $jetCible + $hobbitCible->agilite_bm_hobbit + $hobbitCible->agilite_bbdf_hobbit + $hobbitCible->bm_defense_hobbit;
		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - calculJetCibleHobbit - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - exit -");
		return $jetCible;
	}

	public static function calculJetCibleMonstre($monstre) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - enter -");
		$config = Zend_Registry::get('config');
		$jetCible = Bral_Util_De::getLanceDe6($monstre["agilite_base_monstre"]);
		$jetCible = $jetCible + $monstre["agilite_bm_monstre"] + $monstre["bm_defense_monstre"];
		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - exit -");
		return $jetCible;
	}

	public static function calculJetAttaqueNormale($hobbit) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetAttaquant = Bral_Util_De::getLanceDe6($config->game->base_agilite + $hobbit->agilite_base_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant=".$jetAttaquant);
		$jetAttaquant = $jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit;
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant + agilite_bm_hobbit + bm_attaque_hobbit =".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		return $jetAttaquant;
	}

	public static function calculDegatAttaqueNormale($hobbit) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;

		$jetDegat["critique"] = Bral_Util_De::getLanceDe6(($config->game->base_force + $hobbit->force_base_hobbit) * $coefCritique);
		$jetDegat["noncritique"] = Bral_Util_De::getLanceDe6($config->game->base_force + $hobbit->force_base_hobbit);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - critique=".$jetDegat["critique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - noncritique=".$jetDegat["noncritique"]);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->force_bm_hobbit=".$hobbit->force_bm_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->force_bbdf_hobbit=".$hobbit->force_bbdf_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->bm_degat_hobbit=".$hobbit->bm_degat_hobbit);

		$jetDegat["critique"] = floor($jetDegat["critique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit);
		$jetDegat["noncritique"] = floor($jetDegat["noncritique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit);

		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - critique=".$jetDegat["critique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - noncritique=".$jetDegat["noncritique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - exit -");
		return $jetDegat;
	}

	public static function calculDegatCase($config, $hobbit, $degats, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCase - enter -");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$retour["hobbitMorts"] = null;
		$retour["hobbitTouches"] = null;
		$retour["monstreMorts"] = null;
		$retour["monstreTouches"] = null;
		$retour["n_cible"] = 0;

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($hobbit->x_hobbit, $hobbit->y_hobbit);
		if ($estRegionPvp) {
			self::calculDegatCaseHobbit($config, $hobbit, $degats, $retour, $view);
		}
		self::calculDegatCaseMonstre($config, $hobbit, $degats, $retour, $view);
		$retour["n_cible"] = count($retour["hobbitTouches"]) + count($retour["monstreTouches"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCase - exit -");
		return $retour;
	}

	public static function calculDegatCaseHobbit($config, $hobbit, $degats, &$retour, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseHobbit - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit, $hobbit->id_hobbit, false);

		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;

		$i = 0;
		foreach($hobbits as $h) {
			$hobbitRowset = $hobbitTable->find($h["id_hobbit"]);
			$hobbitCible = $hobbitRowset->current();
			$retour["hobbitTouches"][$i]["hobbit"] = $h;
			$retour["hobbitTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueHobbit($hobbit, $hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $view, true);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseHobbit - exit -");
		return $retour;
	}

	public static function calculDegatCaseMonstre($config, $hobbit, $degats, &$retour, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);

		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;

		$i = 0;
		foreach($monstres as $m) {
			$retour["monstreTouches"][$i]["monstre"] = $m;
			$retour["monstreTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueMonstre($hobbit, $m, $jetAttaquant, $jetCible, $jetsDegat, $view, true);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - exit -");
		return $retour;
	}

	public static function calculSoinCase($config, $hobbit, $soins) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - enter -");
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit, $hobbit->id_hobbit, false);
		$retour["hobbitTouches"] = null;
		$i = 0;
		foreach($hobbits as $h) {
			$retour["hobbitTouches"][$i]["hobbit"] = $h;
			$retour["hobbitTouches"][$i]["retourAttaque"] = null;
			$i++;
			if ($h["pv_max_hobbit"] >  $h["pv_restant_hobbit"]) {
				$h["pv_restant_hobbit"] = $h["pv_restant_hobbit"] + $soins;
				if ($h["pv_restant_hobbit"] > $h["pv_max_hobbit"]) {
					$h["pv_restant_hobbit"] = $h["pv_max_hobbit"];
				}
				$data = array("pv_restant_hobbit" => $h["pv_restant_hobbit"]);
					
				$where = "id_hobbit = ".$h["id_hobbit"];
				$hobbitTable->update($data, $where);
					
				$idTypeEvenement = $config->game->evenements->type->effet;
				$details = " [h".$hobbit->id_hobbit."] a soigné le hobbit [h".$h["id_hobbit"]."]";
				$detailsBot = $soins." PV soigné";
				if ($soins > 1) {
					$detailsBot = $detailsBot . "s";
				}
				Bral_Util_Evenement::majEvenements($hobbit->id_hobbit, $idTypeEvenement, $details, $detailsBot, $hobbit->niveau_hobbit);
				Bral_Util_Evenement::majEvenements($h["id_hobbit"], $idTypeEvenement, $details, $detailsBot, $h["niveau_hobbit"]);
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - exit -");
		return $retour;
	}

	private static function getDetailsBot($hobbitAttaquant, $cible, $typeCible, $jetAttaquant, $jetCible, $jetDegat = 0, $ballonLache = false, $critique = false, $mortCible = false) {
		$retour = "";
		$retour .= $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.")";

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

			if (array_key_exists('armure_naturelle_hobbit', $cible) && array_key_exists('armure_equipement_hobbit', $cible)) {
				if ($cible["armure_naturelle_hobbit"] > 0) {
					$retour .= PHP_EOL."L'armure naturelle l'a protégé en réduisant les dégâts de ";
					$retour .= $cible["armure_naturelle_hobbit"].".";
				} else {
					$retour .= PHP_EOL."L'armure naturelle ne l'a pas protégé (ARM NAT:".$cible["armure_naturelle_hobbit"].")";
				}
					
				if ($cible["armure_equipement_hobbit"] > 0) {
					$retour .= PHP_EOL."L'équipement l'a protégé en réduisant les dégâts de ";
					$retour .= $cible["armure_equipement_hobbit"].".";
				} else {
					$retour .= PHP_EOL."Aucun équipement ne l'a protégé (ARM EQU:".$cible["armure_equipement_hobbit"].")";
				}
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

	public static function calculStatutEngage(&$hobbit, $updateDbAFaire = false) {
		$est_engage_hobbit = 'non';
		$est_engage_next_dla_hobbit = 'non';

		$c = "stdClass";

		if ($hobbit instanceof $c) {
			$est_ko_hobbit = $hobbit->est_ko_hobbit;
			$est_engage_hobbit = $hobbit->est_engage_hobbit;
			$est_engage_next_dla_hobbit = $hobbit->est_engage_next_dla_hobbit;
			$date_fin_tour_hobbit = $hobbit->date_fin_tour_hobbit;
		} else {
			$est_ko_hobbit = $hobbit["est_ko_hobbit"];
			$est_engage_hobbit = $hobbit["est_engage_hobbit"];
			$est_engage_next_dla_hobbit = $hobbit["est_engage_next_dla_hobbit"];
			$date_fin_tour_hobbit = $hobbit["date_fin_tour_hobbit"];
		}

		if ($est_ko_hobbit == 'non') {
			$est_engage_hobbit = 'oui';
			$date_courante = date("Y-m-d H:i:s");
			// si le hobbit n'a pas encore activé ce tour
			if ($date_fin_tour_hobbit < $date_courante) {
				$est_engage_next_dla_hobbit = 'oui';
			}
		}

		if ($hobbit instanceof $c) {
			$hobbit->est_engage_hobbit = $est_engage_hobbit;
			$hobbit->est_engage_next_dla_hobbit = $est_engage_next_dla_hobbit;
			$idHobbit = $hobbit->id_hobbit;
		} else {
			$hobbit["est_engage_hobbit"] = $est_engage_hobbit;
			$hobbit["est_engage_next_dla_hobbit"] = $est_engage_next_dla_hobbit;
			$idHobbit = $hobbit["id_hobbit"];
		}

		if ($updateDbAFaire) {
			self::updateDbStatutEngage($idHobbit, $est_engage_hobbit, $est_engage_next_dla_hobbit);
		}
	}

	private static function updateDbStatutEngage($idHobbit, $est_engage_hobbit, $est_engage_next_dla_hobbit) {
		$data = array(
			'est_engage_hobbit' => $est_engage_hobbit,
			'est_engage_next_dla_hobbit' => $est_engage_next_dla_hobbit,
		);
		$where = "id_hobbit=".$idHobbit;
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);
	}
}

