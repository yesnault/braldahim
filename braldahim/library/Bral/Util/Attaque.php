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

	public static function attaqueHobbit(&$hobbitAttaquant, &$hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $view, $effetMotSPossible = true) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - effetMotSPossible=".$effetMotSPossible);
		
		$config = Zend_Registry::get('config');
		
		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["attaqueReussie"] = false;
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		$retourAttaque["critique"]  = false;
		
		$retourAttaque["effetMotD"] = false;
		$retourAttaque["effetMotE"] = false;
		$retourAttaque["effetMotG"] = false;
		$retourAttaque["effetMotH"] = false;
		$retourAttaque["effetMotI"] = false;
		$retourAttaque["effetMotJ"] = false;
		$retourAttaque["effetMotL"] = false;
		$retourAttaque["effetMotQ"] = false;
		$retourAttaque["effetMotS"] = false;
		
		$cible = array('nom_cible' => $hobbitCible->prenom_hobbit ." ". $hobbitCible->nom_hobbit, 
			'id_cible' => $hobbitCible->id_hobbit, 
			'x_cible' => $hobbitCible->x_hobbit, 
			'y_cible' => $hobbitCible->y_hobbit,
			'niveau_cible' => $hobbitCible->niveau_hobbit,
			'armure_naturelle_hobbit' => $hobbitCible->armure_naturelle_hobbit,
			'armure_equipement_hobbit' => $hobbitCible->armure_equipement_hobbit,
		);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - attaqueHobbit - jetAttaquant".$retourAttaque["jetAttaquant"]. " jetCible=".$retourAttaque["jetCible"]);
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
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
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
				}
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE hobbitAttaquant->pv_restant_hobbit=".$hobbitAttaquant->pv_restant_hobbit. " hobbitAttaquant->pv_max_hobbit=".$hobbitAttaquant->pv_max_hobbit);
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
			
			$pvTotal =  $hobbitCible->pv_restant_hobbit + ($hobbitCible->armure_naturelle_hobbit + $hobbitCible->armure_equipement_hobbit);
			$pvTotalAvecDegat = $pvTotal - $retourAttaque["jetDegat"];
			
			if ($pvTotalAvecDegat < $hobbitCible->pv_restant_hobbit) {
				$hobbitCible->pv_restant_hobbit = $pvTotalAvecDegat;
			}
			if ($hobbitCible->pv_restant_hobbit <= 0) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mort du hobbit !");
				$hobbitCible->pv_restant_hobbit = 0;
				$hobbitCible->est_mort_hobbit = "oui";
				$hobbitCible->nb_mort_hobbit = $hobbitCible->nb_mort_hobbit + 1;
				$hobbitAttaquant->nb_hobbit_kill_hobbit = $hobbitAttaquant->nb_hobbit_kill_hobbit + 1;
				$hobbitCible->date_fin_tour_hobbit = date("Y-m-d H:i:s");
				
				$effetH = Bral_Util_Commun::getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true && $effetMotSPossible == true) {
					$retourAttaque["effetMotH"] = true;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotH True");
				}
				
				if (Bral_Util_Commun::getEffetMotL($hobbitAttaquant->id_hobbit) == true && $effetMotSPossible == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					$retourAttaque["effetMotL"] = true;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True hobbitAttaquant->pa_hobbit=".$hobbitAttaquant->pa_hobbit);
				}
				
				$retourAttaque["mort"] = true;
				$nbCastars = Bral_Util_Commun::dropHobbitCastars($hobbitCible, $effetH);
				$hobbitCible->castars_hobbit = $hobbitCible->castars_hobbit - $nbCastars;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - nbCastars=".$nbCastars);
				if ($hobbitCible->castars_hobbit < 0) {
					$hobbitCible->castars_hobbit = 0;
				}
			} else {
				$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit - $hobbitCible->niveau_hobbit;
				$hobbitCible->est_mort_hobbit = "non";
				$retourAttaque["mort"] = false;
				$retourAttaque["fragilisee"] = true;
			}
			$data = array(
				'castars_hobbit' => $hobbitCible->castars_hobbit,
				'pv_restant_hobbit' => $hobbitCible->pv_restant_hobbit,
				'est_mort_hobbit' => $hobbitCible->est_mort_hobbit,
				'nb_mort_hobbit' => $hobbitCible->nb_mort_hobbit,
				'date_fin_tour_hobbit' => $hobbitAttaquant->date_fin_tour_hobbit,
				'regeneration_malus_hobbit' => $hobbitCible->regeneration_malus_hobbit,
				'vue_bm_hobbit' => $hobbitCible->vue_bm_hobbit,
				'vue_malus_hobbit' => $hobbitCible->vue_malus_hobbit,
				'agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit,
				'agilite_malus_hobbit' => $hobbitCible->agilite_malus_hobbit,
			);
			$where = "id_hobbit=".$hobbitCible->id_hobbit;
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);
			
			$id_type = $config->game->evenements->type->attaquer;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit;
			if ($retourAttaque["mort"] == true) {
				$details .=" a tué";
			} else {
				$details .=" a attaqué ";
			}
			
			$details .= " le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			
			$detailsBot = self::getDetailsBot($hobbitAttaquant, $cible, $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"] , $retourAttaque["jetDegat"], $retourAttaque["critique"], $retourAttaque["mort"]);
			if ($effetMotSPossible == false) {
				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // uniquement en cas de riposte
			}
			
			if ($retourAttaque["mort"] == false) {
				Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, $detailsBot, "hobbit", true, $view);
//				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot);  // fait dans competence.php avec le détail du résulat
			} else {
				$id_type = $config->game->evenements->type->mort;
				Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, $detailsBot, "hobbit", true, $view);
				$id_type = $config->game->evenements->type->kill;
//				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot);
			}
			
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mise a jour du hobbit ".$hobbitCible->id_hobbit." pv_restant_hobbit=".$hobbitCible->pv_restant_hobbit);
		} else if ($retourAttaque["jetCible"] / 2 <= $retourAttaque["jetAttaquant"]) {
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque esquivee malus sur ajoute a agilite_bm_hobbit=".(floor($cible["niveau_cible"] / 10) + 1 ));
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit - ( floor($cible["niveau_cible"] / 10) + 1 );
			$data = array('agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit);
			$where = "id_hobbit=".$hobbitCible->id_hobbit;
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);
			$retourAttaque["mort"] = false;
			$retourAttaque["fragilisee"] = true;
			
			$id_type = $config->game->evenements->type->attaquer;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$details .= " qui a esquivé l'attaque";
			$detailsBot = self::getDetailsBot($hobbitAttaquant, $cible, $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
			if ($effetMotSPossible == false) {
				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // uniquement en cas de riposte
			}
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, $detailsBot, "hobbit", true, $view);
//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // fait dans competence.php avec le détail du résulat
			
		} else { // esquive parfaite
			$id_type = $config->game->evenements->type->attaquer;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$detailsBot = self::getDetailsBot($hobbitAttaquant, $cible, $retourAttaque["jetAttaquant"] , $retourAttaque["jetCible"]);
			$details .= " qui a esquivé parfaitement l'attaque";
			if ($effetMotSPossible == false) {
				Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // uniquement en cas de riposte
			}
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, $detailsBot, "hobbit", true, $view);
//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // fait dans competence.php avec le détail du résulat
		}
		
		if ($effetMotSPossible == true && $retourAttaque["mort"] == false) {
			$effetMotS = Bral_Util_Commun::getEffetMotS($hobbitCible->id_hobbit);
			if ($effetMotS != null) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Debut !");
				$retourAttaque["effetMotS"] = true;
				$jetAttaquantRiposte = Bral_Util_Attaque::calculJetAttaqueNormale($hobbitCible);
				$jetCibleRiposte = Bral_Util_Attaque::calculJetCibleHobbit($hobbitAttaquant);
				$jetsDegatRiposte = Bral_Util_Attaque::calculDegatAttaqueNormale($hobbitCible);
				$retourAttaque["retourAttaqueEffetMotS"] = self::attaqueHobbit($hobbitCible, $hobbitAttaquant, $jetAttaquantRiposte, $jetCibleRiposte, $jetsDegatRiposte, $view, false);
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - EffetMotS Riposte Fin !");
			}
		}
				
		$retourAttaque["details"] = $details;
		$retourAttaque["typeEvemenent"] = $id_type;
		
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueHobbit - exit -");
		return $retourAttaque;
	}
	
	public static function attaqueMonstre(&$hobbitAttaquant, $monstre, $jetAttaquant, $jetCible, $jetsDegat) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - enter -");
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetAttaquant=".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - jetCible=".$jetCible);
		
		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["jetDegat"] = 0;
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
		
		$retourAttaque["attaqueReussie"] = false;
		
		if ($monstre["genre_type_monstre"] == 'feminin') {
			$m_taille = $monstre["nom_taille_f_monstre"];
		} else {
			$m_taille = $monstre["nom_taille_m_monstre"];
		}
		
		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille, 'id_cible' => $monstre["id_monstre"], 'niveau_cible' => $monstre["niveau_monstre"],  'x_cible' => $monstre["x_monstre"], 'y_cible' => $monstre["y_monstre"]);
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
			
			$effetMotE = Bral_Util_Commun::getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				$retourAttaque["effetMotE"] = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				$retourAttaque["effetMotEPoints"] = $gainPv;
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotE True effetMotE=".$effetMotE." gainPv=".$gainPv);
				
				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit	+ $hobbitAttaquant->pv_max_hobbit;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
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
			
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre avant degat=".$monstre["pv_restant_monstre"]);
			$monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegat"];
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - pv_restant_monstre apres degat=".$monstre["pv_restant_monstre"]);
			
			if ($monstre["pv_restant_monstre"] <= 0) {
				Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Mort du monstre !");
				$effetD = null;
				$effetH = null;
				
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
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					$retourAttaque["effetMotL"] = true;
					Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - effetMotL True hobbitAttaquant->pa_hobbit=".$hobbitAttaquant->pa_hobbit);
				}

				$retourAttaque["mort"] = true;
				$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
				$vieMonstre->mortMonstreDb($cible["id_cible"], $effetD, $effetH);
			} else {
				$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] - $monstre["niveau_monstre"];
				$retourAttaque["fragilisee"] = true;
				
				$retourAttaque["mort"] = false;
				$data = array(
					'pv_restant_monstre' => $monstre["pv_restant_monstre"],
					'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
					'regeneration_malus_monstre' => $monstre["regeneration_malus_monstre"],
					'vue_malus_monstre' => $monstre["vue_malus_monstre"],
					'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
					'agilite_malus_monstre' => $monstre["agilite_malus_monstre"],
				);
				$where = "id_monstre=".$cible["id_cible"];
				$monstreTable = new Monstre();
				$monstreTable->update($data, $where);
			}
		} else if ($retourAttaque["jetCible"] / 2 <= $retourAttaque["jetAttaquant"]) {
			Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - Attaque esquivee malus sur ajoute a agilite_bm_monstre=".( floor($monstre["niveau_monstre"] / 10) + 1 ));
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] - ( floor($monstre["niveau_monstre"] / 10) + 1 );
			$retourAttaque["mort"] = false;
			$data = array('agilite_bm_monstre' => $monstre["agilite_bm_monstre"]);
			$where = "id_monstre=".$cible["id_cible"];
			$monstreTable = new Monstre();
			$monstreTable->update($data, $where);
			$retourAttaque["fragilisee"] = true;
		}
		
		$detailsBot = self::getDetailsBot($hobbitAttaquant, $cible, $retourAttaque["jetAttaquant"], $retourAttaque["jetCible"], $retourAttaque["jetDegat"], $retourAttaque["critique"], $retourAttaque["mort"]) ;
		
		if ($retourAttaque["mort"] === true) {
			$id_type = $config->game->evenements->type->kill;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a tué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot); // fait dans competence.php avec le détail du résulat
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $config->game->evenements->type->mort, $details, "", "monstre");
		} else {
			$id_type = $config->game->evenements->type->attaquer;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			
			if ($retourAttaque["jetAttaquant"] * 2 < $retourAttaque["jetCible"]) { // esquive parfaite
				$details .= " qui a esquivé parfaitement";
			} else if ($retourAttaque["jetAttaquant"] <= $retourAttaque["jetCible"]) { // esquive
				$details .= " qui a esquivé ";
			} else { // attaque reussie
				$details .= "";
			}
			
//			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details, $detailsBot);
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, "", "monstre");
		}
		
		$retourAttaque["details"] = $details;
		$retourAttaque["typeEvemenent"] = $id_type;
		
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - attaqueMonstre - exit -");
		return $retourAttaque;
	}
	
	public static function calculJetCibleHobbit($hobbitCible) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - enter -");
		$config = Zend_Registry::get('config');
		$jetCible = 0;
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - config->game->base_agilite=".$config->game->base_agilite." hobbitCible->agilite_base_hobbit=".$hobbitCible->agilite_base_hobbit);
		for ($i=1; $i<=$config->game->base_agilite + $hobbitCible->agilite_base_hobbit; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
			Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - jetCible=".$jetCible);
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - hobbitCible->agilite_bm_hobbit=".$hobbitCible->agilite_bm_hobbit);
		$jetCible = $jetCible + $hobbitCible->agilite_bm_hobbit + $hobbitCible->agilite_bbdf_hobbit + $hobbitCible->bm_defense_hobbit;
		Bral_Util_Log::attaque()->debug("Bral_Util_Attaque - calculJetCibleHobbit - jetCible=".$jetCible);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleHobbit - exit -");
		return $jetCible;
	}
	
	public static function calculJetCibleMonstre($monstre) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - enter -");
		$config = Zend_Registry::get('config');
		$jetCible = 0;
		for ($i=1; $i <= $monstre["agilite_base_monstre"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $monstre["agilite_bm_monstre"];
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetCibleMonstre - exit -");
		return $jetCible;
	}
	
	public static function calculJetAttaqueNormale($hobbit) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetAttaquant = 0;
		for ($i=1; $i<=$config->game->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant=".$jetAttaquant);
		$jetAttaquant = $jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit;
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - jetAttaquant + agilite_bm_hobbit + bm_attaque_hobbit + =".$jetAttaquant);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculJetAttaqueNormale - enter -");
		return $jetAttaquant;
	}
	
	public static function calculDegatAttaqueNormale($hobbit) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - enter -");
		$config = Zend_Registry::get('config');
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;
		
		for ($i=1; $i<= ($config->game->base_force + $hobbit->force_base_hobbit) * $coefCritique; $i++) {
			$jetDegat["critique"] = $jetDegat["critique"] + Bral_Util_De::get_1d6();
		}
		
		for ($i=1; $i<= ($config->game->base_force + $hobbit->force_base_hobbit); $i++) {
			$jetDegat["noncritique"] = $jetDegat["noncritique"] + Bral_Util_De::get_1d6();
		}
		
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - critique=".$jetDegat["critique"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - noncritique=".$jetDegat["noncritique"]);
		
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->force_bm_hobbit=".$hobbit->force_bm_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->force_bbdf_hobbit=".$hobbit->force_bbdf_hobbit);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatAttaqueNormale - hobbit->bm_degat_hobbit=".$hobbit->bm_degat_hobbit);
		
		$jetDegat["critique"] = $jetDegat["critique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit;
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit;
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
		self::calculDegatCaseHobbit($config, $hobbit, $degats, $retour, $view);
		self::calculDegatCaseMonstre($config, $hobbit, $degats, $retour);
		$retour["n_cible"] = count($retour["hobbitTouches"]) + count($retour["monstreTouches"]);
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCase - exit -");
		return $retour;
	}
	
	public static function calculDegatCaseHobbit($config, $hobbit, $degats, &$retour, $view) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseHobbit - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->id_hobbit);
		
		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;
		
		$i = 0;
		foreach($hobbits as $h) {
			$hobbitRowset = $hobbitTable->find($h["id_hobbit"]);
			$hobbitCible = $hobbitRowset->current();
			$retour["hobbitTouches"][$i]["hobbit"] = $h;
			$retour["hobbitTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueHobbit($hobbit, $hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $view);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseHobbit - exit -");
		return $retour;
	}
	
	public static function calculDegatCaseMonstre($config, $hobbit, $degats, &$retour) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - enter -");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit);
		
		$jetsDegat["critique"] = $degats;
		$jetsDegat["noncritique"] = $degats;
		$jetAttaquant = 1;
		$jetCible = 0;
		
		$i = 0;
		foreach($monstres as $m) {
			$retour["monstreTouches"][$i]["monstre"] = $m;
			$retour["monstreTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueMonstre($hobbit, $m, $jetAttaquant, $jetCible, $jetsDegat);
			$i++;
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculDegatCaseMonstre - exit -");
		return $retour;
	}
	
	public static function calculSoinCase($config, $hobbit, $soins) {
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - enter -");
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->id_hobbit);
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
					
				$id_type = $config->game->evenements->type->effet;
				$details = $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") N".$hobbit->niveau_hobbit." a soigné le hobbit ".$h["prenom_hobbit"] ." ". $h["nom_hobbit"] ." (".$h["id_hobbit"].") N".$h["niveau_hobbit"];
				$detailsBot = $soins." PV soigné";
				if ($soins > 1) {
					$detailsBot = $detailsBot . "s";
				}
				Bral_Util_Evenement::majEvenements($hobbit->id_hobbit, $id_type, $details, $detailsBot);
				Bral_Util_Evenement::majEvenements($h["id_hobbit"], $id_type, $details, $detailsBot);
			}
		}
		Bral_Util_Log::attaque()->trace("Bral_Util_Attaque - calculSoinCase - exit -");
		return $retour;
	}
	
	private static function getDetailsBot($hobbitAttaquant, $cible, $jetAttaquant, $jetCible, $jetDegat = 0, $critique = false, $mortCible = false) {
		$retour = "";

		$retour .= $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit;
		if ($mortCible) {
			$retour .= " a tué";
		} else if ($jetCible >= $jetAttaquant) {
			$retour .= " a esquivé";
		} else {
			$retour .= " a attaqué";
		}
		$retour .= " ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
		
		if ($jetAttaquant <= $jetCible) {
			if ($jetCible > $jetAttaquant * 2) {
				$retour .= " qui a esquivé parfaitement";
			} else {
				$retour .= " qui a esquivé";
			}
		}
		
		$retour .= "
Jet d'attaque : ".$jetAttaquant;
		$retour .= "
Jet de défense : ".$jetCible;
		
		
		if ($jetAttaquant > $jetCible) {
			$retour .= "
Jet de dégâts : ".$jetDegat;
		
			if ($critique) {
				$retour .= "
La cible a été touchée par une attaque critique";
			} else {
			$retour .= "
La cible a été touchée";
			}
			
			if (array_key_exists('armure_naturelle_hobbit', $cible) && array_key_exists('armure_equipement_hobbit', $cible)) {
				if ($cible["armure_naturelle_hobbit"] > 0) {
					$retour .= "
L'armure naturelle l'a protégé en réduisant les dégâts de ";
					$retour .= $cible["armure_naturelle_hobbit"].".";
				} else {
					$retour .= "
L'armure naturelle ne l'a pas protégé (ARM NAT:".$cible["armure_naturelle_hobbit"].")"; 	
				}
			
				if ($cible["armure_equipement_hobbit"] > 0) {
					$retour .= "
L'équipement  l'a protégé en réduisant les dégâts de ";
					$retour .= $cible["armure_equipement_hobbit"].".";
				} else {
					$retour .= "
Aucun équipement ne l'a protégé (ARM EQU:".$cible["armure_equipement_hobbit"].")"; 	
				}
			}
			
			if ($mortCible) {
			$retour .= "
La cible a été tuée";
			}
		} else if ($jetCible > $jetAttaquant * 2) { // esquive
			$retour .= "
La cible a esquivé parfaitement l'attaque";
		} else { // esquive parfaite
			$retour .= "
La cible a equivé l'attaque";
		}
		return $retour;
	}
}

