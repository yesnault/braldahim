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
class Bral_Batchs_Soule extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - enter -");

		Zend_Loader::loadClass("Bral_Util_Evenement");
		Zend_Loader::loadClass("SouleEquipe");
		Zend_Loader::loadClass("SouleNomEquipe");
		Zend_Loader::loadClass("SouleMatch");
		Zend_Loader::loadClass("SouleNomEquipe");

		$retour = $this->calculCreationMatchs();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculCreationMatchs() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - enter -");

		$retour = "";

		$souleMatch = new SouleMatch();
		$matchs = $souleMatch->findNonDebutes();

		$souleEquipe = new SouleEquipe();

		$nbJoueursEquipeMin = floor($this->config->game->soule->max->joueurs / 2);
		$nbJoueursEquipeMinQuota = floor($this->config->game->soule->min->joueurs / 2);

		if ($matchs != null) {
			foreach($matchs as $m) { // pour tous les matchs non débutés
				$equipes = $souleEquipe->countInscritsNonDebuteByIdMatch($m["id_soule_match"]);
				if ($equipes != null && count($equipes) == 2
				&& (($equipes[0]["nombre"] >= $nbJoueursEquipeMin && $equipes[1]["nombre"] >= $nbJoueursEquipeMin)
				|| ($m["nb_jours_quota_soule_match"] >= 2)))  {
					$retour .= $this->calculCreationMath($m);
				} elseif ($equipes != null && count($equipes) == 2
				&& $equipes[0]["nombre"] >= $nbJoueursEquipeMinQuota && $equipes[1]["nombre"] >= $nbJoueursEquipeMinQuota) {
					$retour .= $this->updateJoursQuotaMinMatch($m);
				} else {
					if (count($equipes) == 2) {
						$retour .= " match(".$m["id_soule_match"].") e1:".$equipes[0]["nombre"]." e2:".$equipes[1]["nombre"];
						Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - match(".$m["id_soule_match"].") equipe 1:".$equipes[0]["nombre"]." equipe 2:".$equipes[1]["nombre"]);
					} elseif (count($equipes) == 1) {
						$retour .= " match(".$m["id_soule_match"].") e1:".$equipes[0]["nombre"]." e2:0";
						Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - match(".$m["id_soule_match"].") equipe 1:".$equipes[0]["nombre"]);
					} else {
						Bral_Util_Log::batchs()->erreur("Bral_Batchs_Soule - pas d'equipe avec un match (".$m["id_soule_match"].") initialise");
						$retour .= " Erreur match(".$m["id_soule_match"].")";
					}
				}
			}
		} else {
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - pas de match non debute -");
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - exit -");
		return $retour;
	}

	private function updateJoursQuotaMinMatch($match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateJoursQuotaMinMatch - enter -");
		$retour = " updateQuota match(".$match["id_soule_match"].")";

		$souleMatchTable = new SouleMatch();
		$data = array(
			"nb_jours_quota_soule_match" => $match["nb_jours_quota_soule_match"] + 1,
		);
		$where = "id_soule_match = ".(int)$match["id_soule_match"];
		$souleMatchTable->update($data, $where);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateJoursQuotaMinMatch - enter -");
		return $retour;
	}

	private function calculCreationMath($match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - enter -");
		$retour = " creation match(".$match["id_soule_match"].")";

		$souleEquipe = new SouleEquipe();

		$this->calculNomEquipe($match);

		// on récupère tous les joueurs
		$joueurs = $souleEquipe->findByIdMatch($match["id_soule_match"]);

		if ($joueurs != null && count($joueurs) > 0) {
			foreach($joueurs as $j) {
				$retour .= $this->calculCreationJoueur($j, $match);
			}
		} else {
			throw new Zend_Exception("Bral_Batchs_Soule - calculCreationMath nb.joueurs invaides match(".$match["id_soule_match"].")");
		}

		$this->updateMatchDb($match);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - enter -");
		return $retour;
	}

	private function updateMatchDb($match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateMatchDb - enter -");
		$souleMatchTable = new SouleMatch();
		$data = array(
			"date_debut_soule_match" =>	date("Y-m-d H:i:s"),
			"nom_equipea_soule_match" => $match["nom_equipea_soule_match"],
			"nom_equipeb_soule_match" => $match["nom_equipeb_soule_match"],
		);
		$where = "id_soule_match = ".(int)$match["id_soule_match"];
		$souleMatchTable->update($data, $where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateMatchDb - exit -");
	}

	private function calculNomEquipe(&$match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - enter -");
		$souleNomEquipe = new SouleNomEquipe();
		$nomRowset = $souleNomEquipe->fetchAll();

		foreach ($nomRowset as $n) {
			$noms[] = $n["nom_soule_nom_equipe"];
		}
		srand((float)microtime()*1000000);
		shuffle($noms);

		$nomA = array_pop($noms);
		$nomB = array_pop($noms);

		$match["nom_equipea_soule_match"] = $nomA;
		$match["nom_equipeb_soule_match"] = $nomB;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - nomA:".$match["nom_equipea_soule_match"]);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - nomB:".$match["nom_equipeb_soule_match"]);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - exit -");
	}

	private function calculCreationJoueur($joueur, $match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - enter -");
		$retour = "";

		$hobbitTable = new Hobbit();

		$ecartX = $match["x_max_soule_terrain"] - $match["x_min_soule_terrain"];
		if ($ecartX <= 2) {
			throw new Zend_Exception("Bral_Batchs_Soule - calculCreationMath ecartX invalide(".$ecartX.") match(".$match["id_soule_match"].") xmax".$match["x_max_soule_terrain"]. " xmin:".$match["x_min_soule_terrain"]);
		}

		if ($joueur["camp_soule_equipe"] == "a") {
			$x = $match["x_min_soule_terrain"] + Bral_Util_De::get_de_specifique(0, $ecartX);
			$y = $match["y_max_soule_terrain"]; // en haut du terrain
		} else { // b, en bas
			$x = $match["x_min_soule_terrain"] + Bral_Util_De::get_de_specifique(0, $ecartX);
			$y = $match["y_min_soule_terrain"]; // en bas du terrain
		}

		$data = array(
			"x_hobbit" => $x,
			"y_hobbit" => $y,
			"est_soule_hobbit" => "oui",
			"soule_camp_hobbit" => $joueur["camp_soule_equipe"], // dénormalisation
		);
		$where = "id_hobbit = ".(int)$joueur["id_hobbit"];
		$hobbitTable->update($data, $where);

		$souleEquipe = new SouleEquipe();
		$data = array(
			"x_avant_hobbit_soule_equipe" => $joueur["x_hobbit"],
			"y_avant_hobbit_soule_equipe" => $joueur["y_hobbit"],
		);
		$where = "id_fk_hobbit_soule_equipe = ".(int)$joueur["id_hobbit"];
		$souleEquipe->update($data, $where);

		$idType = $this->config->game->evenements->type->soule;
		$details = "La roulotte a pris [h".$joueur["id_hobbit"]."] pour aller jouer un match sur le ".$match["nom_soule_terrain"];
		$detailsBot = "Vous êtes arrivés sur le ".$match["nom_soule_terrain"] ." en ".$x.",".$y. PHP_EOL."Le nom de votre équipe est : ";
		if ($joueur["camp_soule_equipe"] == "a") {
			$detailsBot .= $match["nom_equipea_soule_match"];
		} else {
			$detailsBot .= $match["nom_equipeb_soule_match"];
		}
		Bral_Util_Evenement::majEvenements($joueur["id_hobbit"], $idType, $details, $detailsBot, $joueur["niveau_hobbit"], "hobbit", $joueur["niveau_hobbit"], true, $this->view);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - joueur(".$joueur["id_hobbit"].") x:".$x." y:".$y);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - exit -");
		return $retour;
	}
}