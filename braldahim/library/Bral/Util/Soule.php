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
		$whereCible = " id_fk_match_soule_equipe = ".$hobbitCible->id_fk_soule_match_hobbit;
		$whereCible .= " AND id_fk_hobbit_soule_equipe=".$hobbitCible->id_hobbit;
		$souleEquipeTable->update($dataCible, $whereCible);

		$dataAttaquant = array("nb_hobbit_plaquage_soule_equipe" => "nb_hobbit_plaquage_soule_equipe + 1");
		$whereAttaquant = " id_fk_match_soule_equipe = ".$hobbitAttaquant->id_fk_soule_match_hobbit;
		$whereAttaquant .= " AND id_fk_hobbit_soule_equipe=".$hobbitAttaquant->id_hobbit;
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

	public static function calculFinMatch(&$hobbit, $view, $faireCalculFin) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - enter idHobbit(".$hobbit->id_hobbit.")");
		$retourFinMatch = false;

		Zend_Loader::loadClass("SouleMatch");
		Zend_Loader::loadClass("SouleEquipe");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		Zend_Loader::loadClass("CoffreMinerai");
		Zend_Loader::loadClass("CoffrePartieplante");
		Zend_Loader::loadClass("Bral_Util_Lien");
		
		$souleMatchTable = new SouleMatch();
		$matchsRowset = $souleMatchTable->findByIdHobbitBallon($hobbit->id_hobbit);
		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$match = $matchsRowset[0];
			if (($hobbit->soule_camp_hobbit == "a" && $hobbit->y_hobbit == $match["y_min_soule_terrain"])
			|| ($hobbit->soule_camp_hobbit == "b" && $hobbit->y_hobbit == $match["y_max_soule_terrain"])) {
				
				Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - true");
				
				$souleEquipeTable = new SouleEquipe();
				$joueurs = $souleEquipeTable->findByIdMatch($match["id_soule_match"]);

				if ($joueurs == null) {
					Bral_Util_Log::soule()->err("Bral_Util_Soule - calculFinMatch - Erreur Nb Joueurs (".$match["id_soule_match"].") ");
				} else {
					if ($faireCalculFin === true) {
						self::calculFinMatchGains($hobbit->id_hobbit, $view, $joueurs, $match, $hobbit->soule_camp_hobbit);
						self::calculFinMatchDb($match, $hobbit->soule_camp_hobbit);
						self::calculFinMatchJoueursDb($hobbit, $joueurs, $match);
						$hobbit->est_soule_hobbit = "non";
						$hobbit->soule_camp_hobbit = null;
					} else {
						Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - pas de calcul");
					}
					$retourFinMatch = true;
				}
			}
		} else {
			Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - Le joueur (".$hobbit->id_hobbit.") n'a pas le ballon");
		}
			
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - exit (".$retourFinMatch.") -");
		return $retourFinMatch;
	}

	private static function calculFinMatchGains($idHobbitFin, $view, $joueurs, $match, $campGagnant) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - enter -");

		$equipeA = array();
		$equipeB = array();

		$niveauTotal = 0;
		foreach($joueurs as $j) {
			if ($j["camp_soule_equipe"] == "a") {
				$equipeA[$j["id_hobbit"]]["nb_plaquage"] = $j["nb_hobbit_plaquage_soule_equipe"];
				$equipeA[$j["id_hobbit"]]["niveau_hobbit"] = $j["niveau_hobbit"];
			} else {
				$equipeB[$j["id_hobbit"]]["nb_plaquage"] = $j["nb_hobbit_plaquage_soule_equipe"];
				$equipeB[$j["id_hobbit"]]["niveau_hobbit"] = $j["niveau_hobbit"];
			}
			$niveauTotal = $niveauTotal + $j["niveau_hobbit"];
		}

		$typeMineraiTable = new TypeMinerai();
		$minerais = $typeMineraiTable->fetchAll();
		$minerais = $minerais->toArray();

		$plantes = self::getTabPlantes();

		if ($campGagnant == 'a') {
			self::repartitionGain($match, $idHobbitFin, $view, $niveauTotal, $equipeA, true, $minerais, $plantes);
			self::repartitionGain($match, $idHobbitFin, $view, $niveauTotal, $equipeB, false, $minerais, $plantes);
		} else {
			self::repartitionGain($match, $idHobbitFin, $view, $niveauTotal, $equipeA, false, $minerais, $plantes);
			self::repartitionGain($match, $idHobbitFin, $view, $niveauTotal, $equipeB, true, $minerais, $plantes);
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

				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]]) && $val == true) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_type_partieplante' => $p["nom_type_partieplante"],
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
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - getTabPlantes - exit (".count($tabTypePlantesRetour).")");
	}

	private static function repartitionGain($match, $idHobbitFin, $view, $niveauTotal, $equipe, $estGagnant, $minerais, $plantes) {
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
		foreach($equipe as $idHobbit => $tab) {
			$nbHobbit++;
			if ($nbPlaquageCourant == -1) {
				$nbPlaquageCourant = $tab["nb_plaquage"];
				$rang = 1;
			}
			if ($nbPlaquageCourant != $tab["nb_plaquage"]) {
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
				
			self::calculGainHobbit($match, $idHobbitFin, $idHobbit, $tab["niveau_hobbit"], $view, $nbGain, $minerais, $plantes, $rang, $estGagnant);
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - exit -");
	}

	private static function calculGainHobbit($match, $idHobbitFin, $idHobbit, $niveauHobbit, $view, $nbGain, $minerais, $plantes, $rang, $estGagnant) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculGainHobbit - enter - idHobbit(".$idHobbit.") gain(".$nbGain.")");

		$nbMinerai = count($minerais);
		$nbPlante = count($plantes);

		$nbUnitaireGain = ceil($nbGain / 3);

		$tirage1 = Bral_Util_De::get_de_specifique(0, $nbMinerai + $nbPlante - 1);
		$tirage2 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbMinerai + $nbPlante - 1, array($tirage1));
		$tirage3 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbMinerai + $nbPlante - 1, array($tirage1, $tirage2));

		$texte = self::updateDbData($idHobbit, $nbUnitaireGain, $tirage1, $nbMinerai, $nbPlante, $minerais, $plantes);
		$texte .= self::updateDbData($idHobbit, $nbUnitaireGain, $tirage2, $nbMinerai, $nbPlante, $minerais, $plantes);
		$texte .= self::updateDbData($idHobbit, $nbUnitaireGain, $tirage3, $nbMinerai, $nbPlante, $minerais, $plantes);

		$config = Zend_Registry::get('config');
		$idType = $config->game->evenements->type->soule;

		
		$details = "[h".$idHobbitFin."] a marqué";
		if ($idHobbitFin == $idHobbit) {
			$details .=  " et ";
		} else {
			$details .= ", [h".$idHobbit."] ";
		}
		$details .= " a terminé au rang n°".$rang;
		 
		if ($estGagnant) {
			$details .= " des gagnants";
		} else {
			$details .= " des perdants";
		}
		
		if ($idHobbitFin != $idHobbit) {
			$detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs("[h".$idHobbitFin."]", false);
			$detailsBot .= " a";
		} else {
			$detailsBot = " Vous avez";
		}
		$detailsBot .= " apporté le ballon au bon endroit, le match de soule est terminé.".PHP_EOL.PHP_EOL;
		$detailsBot .= " Vous avez gagné : ".PHP_EOL;
		$detailsBot .= $texte;
		$detailsBot .= " placés directement dans votre coffre à la banque";

		Bral_Util_Evenement::majEvenements($idHobbit, $idType, $details, $detailsBot, $niveauHobbit, "hobbit", true, $view, $match["id_soule_match"]);

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculGainHobbit - exit -");
	}

	private static function updateDbData($idHobbit, $nbUnitaireGain, $tirage, $nbMinerai, $nbPlante, $minerais, $plantes) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData - enter $idHobbit, $nbUnitaireGain, $tirage, $nbMinerai, $nbPlante -");

		if ($nbUnitaireGain > 1) {
			$s = "s";
		} else {
			$s = "";
		}
			
		if ($tirage < $nbMinerai) {
			$coffreMineraiTable = new CoffreMinerai();
			$data = array (
				"id_fk_hobbit_coffre_minerai" => $idHobbit,
				"id_fk_type_coffre_minerai" => $minerais[$tirage]["id_type_minerai"],
				"quantite_brut_coffre_minerai" => $nbUnitaireGain,
			);
			$texte = "  ".$nbUnitaireGain ." minerai$s brut$s de ".$minerais[$tirage]["nom_type_minerai"];
			Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData minerai type(".$minerais[$tirage]["id_type_minerai"].") nb(".$nbUnitaireGain.")");
			$coffreMineraiTable->insertOrUpdate($data);
		} else {
			$coffrePartieplanteTable = new CoffrePartieplante();
			$data = array (
				"id_fk_hobbit_coffre_partieplante" => $idHobbit,
				"id_fk_type_coffre_partieplante" => $plantes[$tirage - $nbMinerai]["id_type_partieplante"],
				"id_fk_type_plante_coffre_partieplante" => $plantes[$tirage - $nbMinerai]["id_type_plante"],
				"quantite_coffre_partieplante" => $nbUnitaireGain,
			);
				
			$texte = "  ".$nbUnitaireGain ." ".$plantes[$tirage - $nbMinerai]["nom_type_partieplante"]."$s de ".$plantes[$tirage - $nbMinerai]["nom_type_plante"];
			Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData minerai type(".$plantes[$tirage - $nbMinerai]["id_type_partieplante"].", ".$plantes[$tirage - $nbMinerai]["id_type_plante"].") nb(".$nbUnitaireGain.")");
			$coffrePartieplanteTable->insertOrUpdate($data);
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData - exit");
		return $texte.PHP_EOL;
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

	private static function calculFinMatchJoueursDb($hobbit, $joueurs, $match) {
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
				"id_fk_soule_match_hobbit" => null,
			);

			$where = "id_hobbit = ".$j["id_hobbit"];
			$hobbitTable->update($data, $where);
			
			if ($hobbit->id_hobbit == $j["id_hobbit"]) {
				$hobbit->est_soule_hobbit = "non";
				$hobbit->soule_camp_hobbit = null;
				$hobbit->est_intangible_hobbit = "oui";
				$hobbit->est_engage_hobbit = "non";
				$hobbit->est_engage_next_dla_hobbit = "non";
				$hobbit->x_hobbit = $x_hobbit;
				$hobbit->y_hobbit = $y_hobbit;
				$hobbit->id_fk_soule_match_hobbit = null;
			}
		}

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchJoueursDb - exit -");
	}

}
