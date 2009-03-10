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
class Bral_Util_Soule {

	public static function majPlaquage($hobbitAttaquant, $hobbitCible) {
		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();

		$dataCible = array("nb_plaque_soule_equipe" => "nb_plaque_soule_equipe + 1");
		$whereCible = " id_fk_match_soule_equipe = ".$hobbitCible["id_fk_soule_match_hobbit"];
		$whereCible .= " AND id_fk_hobbit_soule_equipe=".$hobbitCible["id_hobbit"];
		$souleEquipeTable->update($dataCible, $whereCible);

		$dataAttaquant = array("nb_hobbit_plaquage_soule_equipe" => "nb_hobbit_plaquage_soule_equipe + 1");
		$whereAttaquant = " id_fk_match_soule_equipe = ".$hobbitAttaquant["id_fk_soule_match_hobbit"];
		$whereAttaquant .= " AND id_fk_hobbit_soule_equipe=".$hobbitAttaquant["id_hobbit"];
		$souleEquipeTable->update($dataAttaquant, $whereAttaquant);
	}

	public static function calcuLacheBallon($hobbit, $mort) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calcuLacheBallon - enter idHobbit(".$hobbit->id_hobbit.")");

		$retour = false;

		Zend_Loader::loadClass("SouleMatch");
		$souleMatchTable = new SouleMatch();

		$match = $souleMatchTable->findByIdHobbitBallon($hobbit->id_hobbit);
		if ($match != null && ($mort || Bral_Util_De::get_1d6() == 1)) {
			$data = array(
						"x_ballon_soule_match" => $hobbit->x_hobbit,
						"y_ballon_soule_match" => $hobbit->y_hobbit,
						"id_fk_joueur_ballon_soule_match" => null,
			);
			$where = "id_soule_match = ".$match[0]["id_soule_match"];
			$souleMatchTable->update($data, $where);
			Bral_Util_Log::attaque()->debug("Bral_Util_Soule - Match(".$match[0]["id_soule_match"].") Le ballon est lache en x:".$hobbit->x_hobbit." y:".$hobbit->y_hobbit."!");
			$retour = true;
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calcuLacheBallon - exit (".$retour.") -");
		return $retour;
	}

	public static function calculFinMatch(&$hobbit) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - enter idHobbit(".$hobbit->id_hobbit.")");
		$retourFinMatch = false;

		Zend_Loader::loadClass("SouleMatch");
		Zend_Loader::loadClass("SouleEquipe");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");

		$souleMatchTable = new SouleMatch();
		$matchsRowset = $souleMatchTable->findByIdHobbitBallon($hobbit->id_hobbit);
		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$match = $matchsRowset[0];
			if (($hobbit->soule_camp_hobbit == "a" && $hobbit->y_hobbit == $match["y_min_soule_terrain"])
			|| ($hobbit->soule_camp_hobbit == "b" && $hobbit->y_hobbit == $match["y_max_soule_terrain"])) {

				$souleEquipeTable = new SouleEquipe();
				$joueurs = $souleEquipeTable->findByIdMatch($match["id_soule_match"]);

				if ($joueurs == null) {
					Bral_Util_Log::soule()->error("Bral_Util_Soule - calculFinMatch - Erreur Nb Joueurs (".$match["id_soule_match"].") ");
				} else {
					self::calculFinMatchGains($joueurs, $match, $hobbit->soule_camp_hobbit);
					self::calculFinMatchDb($match, $hobbit->soule_camp_hobbit);
					self::calculFinMatchJoueursDb($joueurs, $match);
					$retourFinMatch = false;
					$hobbit->est_soule_hobbit = "non";
					$hobbit->soule_camp_hobbit = null;
				}
			}
		} else {
			Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - Le joueur (".$hobbit->id_hobbit.") n'a pas le ballon");
		}
			
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - exit (".$retourFinMatch.") -");
		return $retourFinMatch;
	}

	private static function calculFinMatchGains($joueurs, $match, $campGagnant) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - enter -");

		$equipeA = array();
		$equipeB = array();

		$niveauTotal = 0;
		foreach($joueurs as $j) {
			if ($j["camp_soule_equipe"] == "a") {
				$equipeA[$j["id_hobbit"]] = $j["nb_hobbit_plaquage_soule_equipe"];
			} else {
				$equipeB[$j["id_hobbit"]] = $j["nb_hobbit_plaquage_soule_equipe"];
			}
			$niveauTotal = $niveauTotal + $j["niveau_hobbit"];
		}

		$typeMineraiTable = new TypeMinerai();
		$minerais = $typeMineraiTable->fetchAll();
		$minerais = $minerais->toArray();
		
		$plantes = self::getTabPlantes();
		
		if ($campGagnant == 'a') {
			self::repartitionGain($niveauTotal, $equipeA, true, $minerais, $plantes);
			self::repartitionGain($niveauTotal, $equipeB, false, $minerais, $plantes);
		} else {
			self::repartitionGain($niveauTotal, $equipeA, false, $minerais, $plantes);
			self::repartitionGain($niveauTotal, $equipeB, true, $minerais, $plantes);
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - exit -");
	}

	private static function getTabPlantes() {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - getTabPlantes - enter -");
	
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);
		
		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();
	
		$tabTypePlantes = null;
		$tabTypePlantesRetour = null;
		
		foreach($typePartiePlantesRowset as $p) {
			foreach($typePlantesRowset as $t) {
				$val = false;
				$idChamp = "";
				
				if ($t["id_fk_partieplante1_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante2_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante3_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante4_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				
				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]])) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_systeme_type_plante' => $t["nom_systeme_type_plante"],
						'id_type_partieplante' => $p["id_type_partieplante"],
						'id_type_plante' => $t["id_type_plante"],
					);
					$tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]] = $tab;
					$tabTypePlantesRetour[] = $tab;
				}
			}
		}
		
		
		return $tabTypePlantesRetour;
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - getTabPlantes - exit (".count($tabTypePlantesRetour).")-");
	}
	
	private static function repartitionGain($niveauTotal, $equipe, $estGagnant, $minerais, $plantes) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - enter -");
		asort($equipe); // tri par valeur nbPlaquage
		
		if ($estGagnant) {
			$pourcentage = 0.7;
		} else {
			$pourcentage = 0.3;
		}
		
		$rang = -1;
		$nbPlaquageCourant = -1;
		$nbHobbit = 0;
		foreach($equipe as $idHobbit => $nbPlaquages) {
			$nbHobbit++;
			if ($nbPlaquageCourant == -1) {
				$nbPlaquageCourant = $nbPlaquages;
				$rang = 1;
			}
			if ($nbPlaquageCourant != $nbPlaquages) {
				$rang++;
			}
				
			$nbGain = ceil($niveauTotal * $pourcentage * self::getCoefRang($rang));
			if ($rang > 10) {
				$nbHobbitRestant = count($equipe) - $nbHobbit;
				$nbGain = ceil($nbGain / $nbHobbitRestant);
			}
			
			if ($estGagnant && $nbGain < 6) {
				$nbGain = 6;
			} else if ($estGagnant == false && $nbGain < 3) {
				$nbGain = 3;
			}
			
			self::calculGainHobbit($idHobbit, $nbGain, $minerais, $plantes);
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - exit -");
	}

	private static function calculGainHobbit($idHobbit, $nbGain, $minerais, $plantes) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - enter - idHobbit(".$idHobbit.") gain(".$nbGain.")");
		
		$nbMinerai = count($minerais);
		
		print_r($minerais);
		print_r($plantes);
		
		
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - exit -");
	}
	
	private static function getCoefRang($rang) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - getCoefRang - enter -");
		$coef = 0;
		switch($rang) {
			case 1:
				$coef = 0.3;
				break;
			case 2:
				$coef = 0.15;
				break;
			case 3:
				$coef = 0.1;
				break;
			case 4:
				$coef = 0.08;
				break;
			case 5:
				$coef = 0.07;
				break;
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
				$coef = 0.05;
				break;
			default:
				$coef = 0.05;
				break;
		}
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - getCoefRang - exit (".$coef.") -");
		return $coef;
	}

	private static function calculFinMatchDb($match, $campGagnant) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - enter - matchId(".$match["id_soule_match"].")");
		$souleMatchTable = new SouleMatch();
		$data = array(
			"date_fin_soule_match" => date("Y-m-d H:i:s"),
			"id_fk_joueur_ballon_soule_match" => null,
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
			"camp_gagnant_soule_match" => $campGagnant,
		);
		$where = "id_soule_match = ".(int)$match["id_soule_match"];
		$souleMatchTable->update($data, $where);

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - exit -");
	}

	private static function calculFinMatchJoueursDb($joueurs, $match) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchJoueursDb - enter - matchId(".$match["id_soule_match"].")");

		$hobbitTable = new Hobbit();

		foreach($joueurs as $j) {

			if ($j["retour_xy_soule_equipe"] == "oui") {
				$x_hobbit = $j["x_avant_hobbit_soule_equipe"];
				$y_hobbit = $j["y_avant_hobbit_soule_equipe"];
			} else {
				// Mairie de Krotrasque (555, -125)
				if (Bral_Util_De::get_1d2() == 1) {
					$xalea = Bral_Util_De::get_1d6() - 1;
				} else {
					$xalea = - (Bral_Util_De::get_1d6() - 1);
				}
					
				if (Bral_Util_De::get_1d2() == 1) {
					$yalea = Bral_Util_De::get_1d6() - 1;
				} else {
					$yalea = - (Bral_Util_De::get_1d6() - 1);
				}
					
				$x_hobbit = 555 + $xalea;
				$y_hobbit = -125 + $yalea;
			}

			$data = array(
				"est_soule_hobbit" => "non",
				"soule_camp_hobbit" => null,
				"est_intangible_hobbit" => "oui",
				"est_engage_hobbit" => "non",
				"est_engage_next_dla_hobbit" => "non",
				"x_hobbit" => $x_hobbit,
				"y_hobbit" => $y_hobbit,
			);

			$where = "id_hobbit = ".$j["id_hobbit"];
			$hobbitTable->update($data, $where);
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchJoueursDb - exit -");
	}

}
