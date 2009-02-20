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
class Bral_Competences_Distribuercastars extends Bral_Competences_Competence {

	function prepareCommun() {
		// récupération des hobbits qui sont présents dans la vue
		$hobbitTable = new Hobbit();
		// s'il y a trop de hobbits, on prend que les plus proches
		$this->view->estMaxHobbits = false;

		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->id_hobbit);

		$tabHobbits = null;
		foreach($hobbits as $h) {
			if ($h["id_hobbit"] != $this->view->user->id_hobbit) {
				$tabHobbits[] = array(
				 "id_hobbit" => $h["id_hobbit"],
				 "nom_hobbit" => $h["nom_hobbit"], 
				 "prenom_hobbit" => $h["prenom_hobbit"],
				 "niveau_hobbit" => $h["niveau_hobbit"]
				);
			}
		}

		if ($tabHobbits != null && $this->view->config->game->competence->distribuerpx->nb_max_hobbit) {
			$this->view->estMaxHobbits = true;
		}

		$this->view->tabHobbits = $tabHobbits;
		$this->view->n_hobbits = count($tabHobbits);
		$this->refreshVue = false;
	}

	function prepareFormulaire() {
		// rien a faire ici
	}

	function prepareResultat() {
		$tabDistribution = null;
		$total_distribution = 0;
		for ($i=2 ;$i<=$this->view->n_hobbits*2; $i=$i+2) {
			$tab["castars_recus"] = 0;
			$tab["id_hobbit"] = (int)$this->request->get("valeur_".($i-1));
			if ((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."") {
				throw new Zend_Exception(get_class($this)." Valeur invalide : i=$i val=".$this->request->get("valeur_".$i));
			} else {
				$tab["castars_recus"] = (int)$this->request->get("valeur_".$i);
			}

			$trouve = false;
			foreach($this->view->tabHobbits  as $h) {
				if ($tab["id_hobbit"] == $h["id_hobbit"]) {
					$tab["niveau_hobbit"] = $h["niveau_hobbit"];
					$trouve = true;
				}
			}
			if ($trouve == false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide : o:".$this->view->user->id_hobbit." d:".$tab["id_hobbit"]);
			}
			if ($tab["castars_recus"] > 0) {
				$tabDistribution[] = $tab;
			}

			$total_distribution = $total_distribution + $tab["castars_recus"];
		}

		if ($total_distribution > $this->view->user->castars_hobbit) {
			throw new Zend_Exception(get_class($this)." Total trop eleve:".$total_distribution. " c=".$this->view->user->castars_hobbit);
		}
		
		// distribution
		$tabAffiche = null;
		$hobbitTable = new Hobbit();
		$this->setEstEvenementAuto(false);
		foreach ($tabDistribution as $t) {
			$hobbitRowset = $hobbitTable->find($t["id_hobbit"]);
			$hobbit = $hobbitRowset->current();
			
			// Contrôle du poids à faire.
			$poidsRestant = $hobbit->poids_transportable_hobbit - $hobbit->poids_transporte_hobbit;
			if ($poidsRestant < 0) $poidsRestant = 0;
			
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
		
			if ($nbCastarsPossible >= 1) { // On met dans le laban ce qu'on peut
				$hobbit->castars_hobbit = $hobbit->castars_hobbit + $t["castars_recus"];
				$hobbit->poids_transporte_hobbit = $hobbit->poids_transporte_hobbit + $nbCastarsPossible * Bral_Util_Poids::POIDS_CASTARS;
				$data = array(
					'castars_hobbit' => $hobbit->castars_hobbit,
					'poids_transporte_hobbit' => $hobbit->poids_transporte_hobbit,
				);
				$where = "id_hobbit=".$t["id_hobbit"];
				$hobbitTable->update($data, $where);
			}
			
			$tab["castars_recus_terre"] = 0;
			if ($nbCastarsPossible < $t["castars_recus"]) {
				Zend_Loader::loadClass("Castar");
				
				$tab["castars_recus_terre"] = $t["castars_recus"] - $nbCastarsPossible;
				$castarsTable = new Castar();
				$data = array(
					"nb_castar" => $tab["castars_recus_terre"],
					"x_castar" => $this->view->user->x_hobbit,
					"y_castar" => $this->view->user->y_hobbit,
				);
				$castarsTable->insertOrUpdate($data);
				$this->refreshVue = true;
			}
			
			// SI poids dépassé, on dépose à terre
			
			$tab["id_hobbit"] = $t["id_hobbit"];
			$tab["niveau_hobbit"] = $t["niveau_hobbit"];
			$tab["nom_hobbit"] = $hobbit->prenom_hobbit. " " .$hobbit->nom_hobbit;
			$tab["nom_hobbit_details"] = $hobbit->prenom_hobbit. " " .$hobbit->nom_hobbit;
			
			$tab["castars_recus"] = $t["castars_recus"];
			$tabAffiche[] = $tab;

			$id_type = $this->view->config->game->evenements->type->don;
			$detailsD = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a donné des castars à ".$tab["nom_hobbit_details"]." (".$tab["id_hobbit"].")";
			$detailsR = $tab["nom_hobbit_details"]." (".$tab["id_hobbit"].") a reçu des castars la part de ".$this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.")";
			
			$s = "";
			if ($tab["castars_recus"] > 1) $s = "s";
			$detailDonneur = "Vous avez donné ".$tab["castars_recus"]." castar$s à ".$tab["nom_hobbit"]." (".$tab["id_hobbit"].")";
			$detailReceveur = "Vous avez reçu ".$tab["castars_recus"]." castar$s de la part de ".$this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.")";
			if ($tab["castars_recus_terre"] > 0) {
				$s = "";
				if ($tab["castars_recus_terre"] > 1) $s = "s";
				$detailDonneur .= ", dont ".$tab["castars_recus_terre"]." tombé$s à terre";
				$detailReceveur .= ", dont ".$tab["castars_recus_terre"]." tombé$s à terre";
			}
			Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $id_type, $detailsD, $detailDonneur, $this->view->user->niveau_hobbit);
			if ($tab["id_hobbit"] != $this->view->user->id_hobbit) {
				Bral_Util_Evenement::majEvenements($tab["id_hobbit"], $id_type, $detailsR, $detailReceveur, $tab["niveau_hobbit"]);
			}
		}

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $total_distribution;

		$this->view->tabAffiche = $tabAffiche;
		$this->view->totalDistribution = $total_distribution;
		
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		$tab = $this->constructListBoxRefresh();
		if ($this->refreshVue === true) {
			$tab[] = "box_vue";
		}
		return $tab;
	}
}