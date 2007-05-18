<?php

class Bral_Lieux_Ahennepeheux extends Bral_Lieux_Lieu {

	function prepareCommun() {
	}

	function prepareFormulaire() {
		Zend_Loader::loadClass("Metier");
		Zend_Loader::loadClass("HobbitsMetiers");
		/*1er métier : 20 castars
		 2nd métier : 100 castars
		 3ème métier : 500 castars
		 4ème métier : 1000 castars
		 5ème métier : 2000 castars
		 6ème métier : 3000 castars
		 7ème métier : 4000 castars
		 8ème métier : 5000 castars
		 9ème métier : 6000 castars
		 10ème métier : 7000 castars*/

		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id);
		$tabMetiers = null;
		$possedeMetier = false;
		$convertDate = new Bral_Util_ConvertDate();

		foreach($hobbitsMetierRowset as $m) {
			$possedeMetier = true;

			$tabMetiers[] = array("id" => $m["id"],
			"nom" => $m["nom_metier"],
			"nom_systeme" => $m["nom_systeme_metier"],
			"est_actif" => ($m["est_actif_hmetier"] == "oui"),
			"date_apprentissage" => $convertDate->get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
			"description" => $m["description_metier"]);
		}

		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall();

		$tabNouveauMetiers = null;
		foreach ($metiersRowset as $m) {
			$nouveau = true;
			foreach ($tabMetiers as $t) {
				if ($m->id == $t["id"]) {
					$nouveau = false;
				}
			}

			if ($nouveau === true) {
				$tabNouveauMetiers[] = array("id" => $m->id,
				"nom" => $m->nom_metier,
				"nom_systeme" => $m->nom_systeme_metier,
				"description" => $m->description_metier);
			}
		}

		$this->view->tabNouveauMetiers = $tabNouveauMetiers;
		$this->view->tabMetiers = $tabMetiers;
		$this->view->possedeMetier = $possedeMetier;
	}

	function prepareResultat() {

	}


	function getListBoxRefresh() {
		return null;
	}
}