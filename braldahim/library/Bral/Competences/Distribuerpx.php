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
class Bral_Competences_Distribuerpx extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');

		// récupération des bralduns qui sont présents dans la vue
		$braldunTable = new Braldun();
		// s'il y a trop de bralduns, on prend que les plus proches
		$this->view->estMaxBralduns = false;

		$vue = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
		if ($vue <= 0) {
			$vue = 0;
		}
		$bralduns = $braldunTable->findLesPlusProches($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $vue, $this->view->config->game->competence->distribuerpx->nb_max_braldun);

		$tabBralduns = null;
		
		foreach($bralduns as $h) {
			if ($this->view->user->points_gredin_braldun <= 0 || // si l'on est pas gredin
			($this->view->user->points_gredin_braldun > 0 && $h["points_gredin_braldun"] > 0)) { // si l'on est gredin et que le destinataire également
				if ($h["id_braldun"] == $this->view->user->id_braldun) {
					$nom = " Vous-Même : ".$h["prenom_braldun"]. " ". $h["nom_braldun"];
				}
				$tabBralduns[] = array("id_braldun" => $h["id_braldun"],
				 "nom_braldun" => $h["nom_braldun"], 
				 "prenom_braldun" => $h["prenom_braldun"],
				 "niveau_braldun" => $h["niveau_braldun"]);		
			}

		}

		if (count($tabBralduns) >= $this->view->config->game->competence->distribuerpx->nb_max_braldun) {
			$this->view->estMaxBralduns = true;
		}

		$this->view->tabBralduns = $tabBralduns;
		$this->view->n_bralduns = count($tabBralduns);
	}

	function prepareFormulaire() {
		// rien a faire ici
	}

	function prepareResultat() {
		$tabDistribution = null;
		$total_distribution = 0;
		for ($i=2 ;$i<=$this->view->n_bralduns*2; $i=$i+2) {
			$tab["px_recu"] = 0;
			$tab["id_braldun"] = (int)$this->request->get("valeur_".($i-1));
			if ((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."") {
				throw new Zend_Exception(get_class($this)." Valeur invalide : i=$i val=".$this->request->get("valeur_".$i));
			} else {
				$tab["px_recu"] = (int)$this->request->get("valeur_".$i);
			}

			$trouve = false;
			foreach($this->view->tabBralduns  as $h) {
				if ($tab["id_braldun"] == $h["id_braldun"]) {
					$tab["niveau_braldun"] = $h["niveau_braldun"];
					$trouve = true;
				}
			}
			if ($trouve == false) {
				throw new Zend_Exception(get_class($this)." Braldun invalide : o:".$this->view->user->id_braldun." d:".$tab["id_braldun"]);
			}
			if ($tab["px_recu"] > 0) {
				$tabDistribution[] = $tab;
			}

			$total_distribution = $total_distribution + $tab["px_recu"];
		}

		if ($total_distribution > $this->view->user->px_commun_braldun) {
			throw new Zend_Exception(get_class($this)." Total trop eleve:".$total_distribution. " c=".$this->view->user->px_commun_braldun);
		}

		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataStats["mois_stats_experience"] = date("Y-m-d", $moisEnCours);
		$statsExperience = new StatsExperience();
			
		// distribution
		$tabAffiche = null;
		$braldunTable = new Braldun();
		$this->setEstEvenementAuto(false);
		foreach ($tabDistribution as $t) {
			$braldunRowset = $braldunTable->find($t["id_braldun"]);
			$braldun = $braldunRowset->current();
			$braldun->px_perso_braldun = $braldun->px_perso_braldun + $t["px_recu"];
			$data = array(
				'px_perso_braldun' => $braldun->px_perso_braldun,
			);
			$where = "id_braldun=".$t["id_braldun"];
			$braldunTable->update($data, $where);

			$dataStats["nb_px_perso_gagnes_stats_experience"] = $t["px_recu"];
			$dataStats["id_fk_braldun_stats_experience"] = $t["id_braldun"];
			$dataStats["niveau_braldun_stats_experience"] = $t["niveau_braldun"];
			$statsExperience->insertOrUpdate($dataStats);

			if ($t["id_braldun"] == $this->view->user->id_braldun) {
				$this->view->user->px_perso_braldun = $braldun->px_perso_braldun;
			}
			$tab["id_braldun"] = $t["id_braldun"];
			$tab["niveau_braldun"] = $t["niveau_braldun"];
			if ($t["id_braldun"] == $this->view->user->id_braldun) {
				$tab["nom_braldun"] = "Vous-Même : ".$braldun->prenom_braldun. " " .$braldun->nom_braldun;
			} else {
				$tab["nom_braldun"] = $braldun->prenom_braldun. " " .$braldun->nom_braldun;
			}

			$tab["nom_braldun_details"] = $braldun->prenom_braldun. " " .$braldun->nom_braldun;

			$tab["px_recu"] = $t["px_recu"];
			$tabAffiche[] = $tab;

			$id_type = $this->view->config->game->evenements->type->don;
			$detailsD = "[b".$this->view->user->id_braldun."] a donné des PX à [b".$tab["id_braldun"]."]";
			$detailsR = "[b".$tab["id_braldun"]."] a reçu des PX de la part de [b".$this->view->user->id_braldun."]";

			$detailDonneur = "Vous avez donné ".$tab["px_recu"]." PX à ".$tab["nom_braldun"]." (".$tab["id_braldun"].")";
			$detailReceveur = "Vous avez reçu ".$tab["px_recu"]." PX de la part de ".$this->view->user->prenom_braldun ." ". $this->view->user->nom_braldun ." (".$this->view->user->id_braldun.")";
			Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $id_type, $detailsD, $detailDonneur, $this->view->user->niveau_braldun);
			if ($tab["id_braldun"] != $this->view->user->id_braldun) {
				Bral_Util_Evenement::majEvenements($tab["id_braldun"], $id_type, $detailsR, $detailReceveur, $tab["niveau_braldun"]);
			}
		}

		$this->view->user->px_commun_braldun = $this->view->user->px_commun_braldun - $total_distribution;

		$this->view->tabAffiche = $tabAffiche;
		$this->view->totalDistribution = $total_distribution;

		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}
}