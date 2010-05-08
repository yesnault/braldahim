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
class Bral_Competences_Distribuercastars extends Bral_Competences_Competence {

	function prepareCommun() {
		// récupération des bralduns qui sont présents dans la vue
		$braldunTable = new Braldun();
		// s'il y a trop de bralduns, on prend que les plus proches
		$this->view->estMaxBralduns = false;

		$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun);

		$tabBralduns = null;
		foreach($bralduns as $h) {
			if ($h["id_braldun"] != $this->view->user->id_braldun) {
				$tabBralduns[] = array(
				 "id_braldun" => $h["id_braldun"],
				 "nom_braldun" => $h["nom_braldun"], 
				 "prenom_braldun" => $h["prenom_braldun"],
				 "niveau_braldun" => $h["niveau_braldun"]
				);
			}
			if (count($tabBralduns) >= $this->view->config->game->competence->distribuerpx->nb_max_braldun) {
				$this->view->estMaxBralduns = true;
			}
		}

		$this->view->tabBralduns = $tabBralduns;
		$this->view->n_bralduns = count($tabBralduns);
		$this->refreshVue = false;
	}

	function prepareFormulaire() {
		// rien a faire ici
	}

	function prepareResultat() {
		$tabDistribution = null;
		$total_distribution = 0;
		for ($i=2 ;$i<=$this->view->n_bralduns*2; $i=$i+2) {
			$tab["castars_recus"] = 0;
			$tab["id_braldun"] = (int)$this->request->get("valeur_".($i-1));
			if ((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."") {
				throw new Zend_Exception(get_class($this)." Valeur invalide : i=$i val=".$this->request->get("valeur_".$i));
			} else {
				$tab["castars_recus"] = (int)$this->request->get("valeur_".$i);
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
			if ($tab["castars_recus"] > 0) {
				$tabDistribution[] = $tab;
			}

			$total_distribution = $total_distribution + $tab["castars_recus"];
		}

		if ($total_distribution > $this->view->user->castars_braldun) {
			throw new Zend_Exception(get_class($this)." Total trop eleve:".$total_distribution. " c=".$this->view->user->castars_braldun);
		}

		// distribution
		$tabAffiche = null;
		$braldunTable = new Braldun();
		$this->setEstEvenementAuto(false);
		foreach ($tabDistribution as $t) {
			$braldunRowset = $braldunTable->find($t["id_braldun"]);
			$braldun = $braldunRowset->current();
				
			// Contrôle du poids à faire.
			$poidsRestant = $braldun->poids_transportable_braldun - $braldun->poids_transporte_braldun;
			if ($poidsRestant < 0) $poidsRestant = 0;
				
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);

			if ($nbCastarsPossible >= 1) { // On met dans le laban ce qu'on peut
				$braldun->castars_braldun = $braldun->castars_braldun + $t["castars_recus"];
				$braldun->poids_transporte_braldun = $braldun->poids_transporte_braldun + $t["castars_recus"] * Bral_Util_Poids::POIDS_CASTARS;
				$data = array(
					'castars_braldun' => $braldun->castars_braldun,
					'poids_transporte_braldun' => $braldun->poids_transporte_braldun,
				);
				$where = "id_braldun=".$t["id_braldun"];
				$braldunTable->update($data, $where);
			}
				
			$tab["castars_recus_terre"] = 0;
			if ($nbCastarsPossible < $t["castars_recus"]) {

				$tab["castars_recus_terre"] = $t["castars_recus"] - $nbCastarsPossible;

				Zend_Loader::loadClass("Element");
				$elementTable = new Element();
				$data = array(
				"quantite_castar_element" => $tab["castars_recus_terre"],
				"x_element" => $this->view->user->x_braldun,
				"y_element" => $this->view->user->y_braldun,
				"z_element" => $this->view->user->z_braldun,
				);
				$elementTable->insertOrUpdate($data);

				$this->refreshVue = true;
			}
				
			// SI poids dépassé, on dépose à terre
				
			$tab["id_braldun"] = $t["id_braldun"];
			$tab["niveau_braldun"] = $t["niveau_braldun"];
			$tab["nom_braldun"] = $braldun->prenom_braldun. " " .$braldun->nom_braldun;
			$tab["nom_braldun_details"] = $braldun->prenom_braldun. " " .$braldun->nom_braldun;
				
			$tab["castars_recus"] = $t["castars_recus"];
			$tabAffiche[] = $tab;

			$id_type = $this->view->config->game->evenements->type->don;
			$detailsD = "[h".$this->view->user->id_braldun."] a donné des castars à [h".$tab["id_braldun"]."]";
			$detailsR = "[h".$tab["id_braldun"]."] a reçu des castars de la part de [h".$this->view->user->id_braldun."]";
				
			$s = "";
			if ($tab["castars_recus"] > 1) $s = "s";
			$detailDonneur = "Vous avez donné ".$tab["castars_recus"]." castar$s à ".$tab["nom_braldun"]." (".$tab["id_braldun"].")";
			$detailReceveur = "Vous avez reçu ".$tab["castars_recus"]." castar$s de la part de ".$this->view->user->prenom_braldun ." ". $this->view->user->nom_braldun ." (".$this->view->user->id_braldun.")";
			if ($tab["castars_recus_terre"] > 0) {
				$s = "";
				if ($tab["castars_recus_terre"] > 1) $s = "s";
				$detailDonneur .= ", dont ".$tab["castars_recus_terre"]." tombé$s à terre";
				$detailReceveur .= ", dont ".$tab["castars_recus_terre"]." tombé$s à terre";
			}
			Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $id_type, $detailsD, $detailDonneur, $this->view->user->niveau_braldun);
			if ($tab["id_braldun"] != $this->view->user->id_braldun) {
				Bral_Util_Evenement::majEvenements($tab["id_braldun"], $id_type, $detailsR, $detailReceveur, $tab["niveau_braldun"]);
			}
		}

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $total_distribution;

		$this->view->tabAffiche = $tabAffiche;
		$this->view->totalDistribution = $total_distribution;

		$this->majBraldun();
	}

	function getListBoxRefresh() {
		$tab = $this->constructListBoxRefresh();
		if ($this->refreshVue === true) {
			$tab[] = "box_vue";
		}
		$tab[] = "box_laban";
		return $tab;
	}
}