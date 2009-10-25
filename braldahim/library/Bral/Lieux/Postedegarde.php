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
class Bral_Lieux_Postedegarde extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Donjon");

		$this->prepareDonjon();
		Bral_Util_Donjon::controleInscriptionEquipe($this->donjonCourant, $this->view);

		$this->prepareEquipe();
		$this->prepareInscription();
		if ($this->view->estMeneur && $this->view->inscriptionRealisee) {
			$this->prepareDescente();
		}
	}

	private function prepareDonjon() {
		Zend_Loader::loadClass('Donjon');

		$donjonTable = new Donjon();
		$donjonCourant = $donjonTable->findByIdLieu($this->view->idLieu);

		if ($donjonCourant == null || count($donjonCourant) != 1) {
			throw new Zend_Exception('Donjon invalide. idLieu='.$this->view->idLieu);
		}
		$this->donjonCourant = $donjonCourant[0];
		$this->view->idTypeDistinctionCourante = $this->donjonCourant["id_fk_distinction_quete_region"];
	}

	private function prepareEquipe() {
		Zend_Loader::loadClass('DonjonEquipe');

		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($this->donjonCourant["id_donjon"]);

		$this->view->nouvelleEquipePossible = true;
		if ($donjonEquipe != null) { // si on a une equipe en cours
			$this->view->nouvelleEquipePossible = false;
		}

		if (count($donjonEquipe) > 1) {
			throw new Exception('Equipe de donjon en cours > 1 idDonjon:'.$this->donjonCourant["id_donjon"]);
		}

		$this->view->estMeneur = false;

		if (count($donjonEquipe) == 1) {
			$this->equipeCourante = $donjonEquipe[0];
			if ($this->view->user->id_hobbit == $this->equipeCourante["id_fk_hobbit_meneur_equipe"]) {
				$this->view->estMeneur = true;
			}
		} else {
			$this->equipeCourante = null;
		}
	}

	private function prepareInscription() {

		if ($this->equipeCourante == null) {

			$this->view->inscriptionParHobbitNouvelleEquipePossible = false;

			Zend_Loader::loadClass("HobbitsDistinction");
			$hobbitsDistinctionTable = new HobbitsDistinction();
			$distinction = $hobbitsDistinctionTable->findDistinctionsByHobbitIdAndIdTypeDistinction($this->view->user->id_hobbit, $this->donjonCourant["id_fk_distinction_quete_region"]);
			if (count($distinction) == 1) {
				$this->view->inscriptionParHobbitNouvelleEquipePossible = true;
			} else if (count($distinction) > 1) {
				throw new Zend_Exception("Nb. Distinctions invalides nb=".count($distinction));
			}
			return;
		}

		// on regarde si le joueur est demandé dans l'équipe courante

		Zend_Loader::loadClass('DonjonHobbit');
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdHobbitAndIdEquipe($this->view->user->id_hobbit, $this->equipeCourante["id_donjon_equipe"]);

		$this->view->inscriptionDemandee = false;
		$this->view->inscriptionRealisee = false;
		if ($donjonHobbit != null) {
			$donjonHobbit = $donjonHobbit[0];
			if ($donjonHobbit["date_inscription_donjon_hobbit"] == null) {
				$this->view->inscriptionDemandee = true;
				$this->view->dateLimiteInscription = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e\; H:i:s', $this->equipeCourante["date_limite_inscription_donjon_equipe"]);
			} else {
				$this->view->inscriptionRealisee = true;
			}
		}
	}


	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : pa:".$this->view->user->pa_hobbit." cout:".$this->$this->view->paUtilisationLieu);
		}

		if ($this->view->estMeneur && $this->view->descentePossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 1 idh:".$this->view->user->id_hobbit);
		} elseif ($this->view->estMeneur == false && $this->view->nouvelleEquipePossible == false && $this->view->inscriptionDemandee == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 2 idh:".$this->view->user->id_hobbit);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParHobbitNouvelleEquipePossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 3 idh:".$this->view->user->id_hobbit);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParHobbitNouvelleEquipePossible == true) {
			$this->inscriptionNouvelleEquipe();
			$this->view->inscriptionEquipe = true;
		} elseif ($this->view->inscriptionDemandee == true) {
			$this->inscriptionHobbit();
			$this->envoieMessageInscriptionHobbit();
		} elseif ($this->view->estMeneur && $this->view->inscriptionRealisee) {
			$this->calculDescente();
			$this->creationDonjon();
		}

	}

	private function inscriptionNouvelleEquipe() {
		$hobbits = $this->recupereHobbitFromValeur1();

		Zend_Loader::loadClass("Bral_Util_Messagerie");

		Zend_Loader::loadClass("DonjonEquipe");
		$donjonEquipeTable = new DonjonEquipe();
		$mdate = date("Y-m-d H:i:s");
		$mdateLimite = Bral_Util_ConvertDate::get_date_add_day_to_date($mdate, 5);

		$niveauMoyenHobbits = null;
		$totalNiveau = 0;
		foreach($hobbits as $h) {
			$totalNiveau = $totalNiveau + $h["niveau_hobbit"];
		}

		$niveauMoyenHobbits = $totalNiveau / count($hobbits);

		$dataEquipe = array(
			"id_fk_donjon_equipe" => $this->donjonCourant["id_donjon"],
			"id_fk_hobbit_meneur_equipe" => $this->view->user->id_hobbit,
			"date_creation_donjon_equipe" => $mdate,
			"date_limite_inscription_donjon_equipe" => $mdateLimite,
			"etat_donjon_equipe" => "inscription",
			"nb_jour_restant_donjon_equipe" => null,
			"niveau_moyen_donjon_equipe" => $niveauMoyenHobbits,
		);
		$idEquipe = $donjonEquipeTable->insert($dataEquipe);
		$dataEquipe["id_donjon_equipe"] = $idEquipe;

		Zend_Loader::loadClass("DonjonHobbit");
		$donjonHobbitTable = new DonjonHobbit();
		$tabHobbits = null;
		foreach($hobbits as $h) {
			$dataHobbit = array("id_fk_hobbit_donjon_hobbit" => $h["id_hobbit"], "id_fk_equipe_donjon_hobbit" => $idEquipe);
			$donjonHobbitTable->insert($dataHobbit);
			$this->envoieMessageInscriptionEquipe($h, $mdateLimite);
			$tabHobbits[] = array(
				"nom_hobbit" => $h["nom_hobbit"],
				"prenom_hobbit" => $h["prenom_hobbit"],
				"id_hobbit" => $h["id_hobbit"],
			);
		}
		$this->view->tabHobbitsEquipe = $tabHobbits;

		$this->equipeCourante = $dataEquipe;
		$this->inscriptionHobbit();
	}

	private function recupereHobbitFromValeur1() {
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		$hobbitsList = $filter->filter(trim($this->request->get('valeur_1')));

		$hobbitsList = $hobbitsList.",".$this->view->user->id_hobbit;
		$idHobbitsTab = split(',', $hobbitsList);

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByIdListAndIdTypeDistinction($idHobbitsTab, $this->view->idTypeDistinctionCourante);

		if ($hobbits == null) {
			throw new Zend_Exception(get_class($this)." Liste invalide:".$hobbitsList);
		} else if (count($hobbits) != 9) {
			throw new Zend_Exception(get_class($this)." Liste invalide B:".$hobbitsList);
		}

		Zend_Loader::loadClass('Bral_Util_Distinction');
		$idTypeDistinctionDonjon = Bral_Util_Distinction::getIdDistinctionDonjonFromIdDistinctionBourlingueur($this->view->idTypeDistinctionCourante);
		Zend_Loader::loadClass("HobbitsDistinction");
		$hobbitsDistinctionTable = new HobbitsDistinction();
		$distinctionsDonjon = $hobbitsDistinctionTable->countDistinctionByIdHobbitList($idHobbitsTab, $idTypeDistinctionDonjon);
		foreach($distinctionsDonjon as $d) {
			if ($d["nombre"] != 0) {
				throw new Zend_Exception(get_class($this)." Liste invalide C:".$d["nombre"]. " h:".$d["id_fk_hobbit_hdistinction"]);
			}
		}

		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();
		$soule = $souleEquipeTable->countNonDebuteByIdHobbitList($idHobbitsTab);
		foreach($soule as $s) {
			if ($s["nombre"] != 0) {
				throw new Zend_Exception(get_class($this)." Liste invalide D:".$s["nombre"]. " h:".$s["id_fk_hobbit_soule_equipe"]);
			}
		}

		return $hobbits;
	}

	public function envoieMessageInscriptionEquipe($hobbit, $dateLimite) {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		if ($hobbit["id_hobbit"] != $this->view->user->id_hobbit) {
			$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;
			$message .=  $this->view->user->prenom_hobbit. " ".$this->view->user->nom_hobbit;
			$message .= " (".$this->view->user->id_hobbit.") vous a demandé en tant que coéquipier";
			$message .= " pour rentrer dans le donjon de la ".$this->donjonCourant["nom_region"].".".PHP_EOL;
			$message .= " Vous pouvez accepter en validant votre inscription au Poste de Garde en ".$this->view->user->x_hobbit. "/".$this->view->user->y_hobbit.'.'.PHP_EOL.PHP_EOL;
			$message .= " Si vous ne validez pas votre inscription avant le ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e\; H:i:s', $dateLimite);
			$message .= ", le meneur ne pourra pas ouvrir la porte et l'inscription de toute l'équipe sera annulée.".PHP_EOL.PHP_EOL;
		} else {
			$message .= " Vous avez inscrit une équipe pour le donjon de la ".$this->donjonCourant["nom_region"].".".PHP_EOL;
			$message .= " En tant que meneur, votre inscription est automatiquement validée.".PHP_EOL;
			$message .= " Par contre, tous vos coéquipiers doivent valider leur inscription au Poste de Garde en ".$this->view->user->x_hobbit. "/".$this->view->user->y_hobbit.'.'.PHP_EOL;
			$message .= " Et quand ils auront tous validé leur inscription, vous pourrez ouvrir la porte du Donjon pour les faire tous descendre avec vous, automatiquement.".PHP_EOL.PHP_EOL;
		}

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $hobbit["id_hobbit"], $message, $this->view);
	}

	public function envoieMessageInscriptionHobbit() {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Félicitations ! ".PHP_EOL;
		$message .= "Vous avez validé votre inscription au Donjon.".PHP_EOL.PHP_EOL;
		$message .= " Attendez maintenant que tous vos coéquipiers valident leur ";
		$message .= " inscription et que le meneur puisse ouvrir la porte";

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $this->view->user->id_hobbit, $message, $this->view);
	}

	private function inscriptionHobbit() {
		Zend_Loader::loadClass('DonjonHobbit');
		$donjonHobbitTable = new DonjonHobbit();

		$where = "id_fk_hobbit_donjon_hobbit = ".$this->view->user->id_hobbit;
		$where .= " AND id_fk_equipe_donjon_hobbit = ".$this->equipeCourante["id_donjon_equipe"];
			
		$data["date_inscription_donjon_hobbit"] = date("Y-m-d H:i:s");
		$donjonHobbitTable->update($data, $where);
	}

	private function prepareDescente() {
		// verification que tous les hobbits de l'équipe sont sur la case.
		Zend_Loader::loadClass('DonjonHobbit');
		$donjonHobbitTable = new DonjonHobbit();
		$donjonHobbit = $donjonHobbitTable->findByIdEquipe($this->equipeCourante["id_donjon_equipe"]);

		$inscriptionEquipeOk = true;

		foreach($donjonHobbit as $h) {

			if ($h["date_inscription_donjon_hobbit"] == null) {
				$inscriptionEquipeOk = false;
				break;
			} elseif (($h["x_hobbit"] != $this->view->user->x_hobbit ||
			$h["y_hobbit"] != $this->view->user->y_hobbit &&
			$h["z_hobbit"] != $this->view->user->z_hobbit)) {
				$inscriptionEquipeOk = false;
				break;
			}
		}

		$this->view->descentePossible = false;

		if ($inscriptionEquipeOk) {
			$this->view->descentePossible = true;
			$this->hobbitsADescendre = $donjonHobbit;
		}

	}

	private function calculDescente() {

		$hobbitTable = new Hobbit();
		foreach($this->hobbitsADescendre as $h) {
			$where = "id_hobbit = ".$h["id_hobbit"];
			$data = array(
				'z_hobbit' => -1,
				'est_donjon_hobbit' => 'oui',
			);
			$hobbitTable->update($data, $where);
		}
		$this->view->user->z_hobbit = -1;

		$donjonEquipeTable = new DonjonEquipe();

		$data = array(
			"etat_donjon_equipe" => "en_cours",
			"nb_jour_restant_donjon_equipe" => 42,
		);
		$where = 'id_donjon_equipe='.$this->equipeCourante["id_donjon_equipe"];
		$donjonEquipeTable->update($data, $where);
		
		$this->envoieMessageDescente();
	}

	public function envoieMessageDescente() {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;
		$message .=  $this->view->user->prenom_hobbit. " ".$this->view->user->nom_hobbit;
		$message .= " (".$this->view->user->id_hobbit.") a ouvert la porte du Donjon.";
		$message .= "Vous êtes entrés avec lui...".PHP_EOL;
		$message .= "Vous avez maintenant deux lunes pour sortir victorieux ou sinon gare aux conséquences.".PHP_EOL.PHP_EOL;

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $this->view->user->id_hobbit, $message, $this->view);
	}

	function getListBoxRefresh() {
		$tab = array("box_vue", "box_lieu");
		return $this->constructListBoxRefresh($tab);
	}

	private function creationDonjon() {

		Zend_Loader::loadClass("Palissade");
		$palissadeTable = new Palissade();
		$where = array("id_fk_donjon_palissade" => $this->donjonCourant["id_donjon"]);
		$palissadeTable->delete($where);

		$this->suppressionElementsCreationPalissadesAlentour();
		$this->creationPalissadesInternes();
		$this->creationCrevasses();
		$this->creationNids();
		$this->creationMonstres();
	}

	private function suppressionElementsCreationPalissadesAlentour() {

		Zend_Loader::loadClass("Zone");
		$zoneTable = new Zone();

		$zones = $zoneTable->findByIdDonjon($this->donjonCourant["id_donjon"]);

		$data["id_fk_donjon_palissade"] = $this->donjonCourant["id_donjon"];
		$data["est_destructible_palissade"] = "non";

		Zend_Loader::loadClass("Palissade");
		$palissadeTable = new Palissade();

		foreach ($zones as $z) {
			$data["z_palissade"] = $z["z_zone"];

			for($x=$z["x_min_zone"] - 1; $x <= $z["x_max_zone"] + 1; $x++) {
				$data["x_palissade"] = $x;
				$data["y_palissade"] = $z["y_min_zone"];
				$palissadeTable->insert($data);
				$data["y_palissade"] = $z["y_max_zone"] + 1;
				$palissadeTable->insert($data);
			}

			for($y=$z["y_min_zone"] + 1; $y <= $z["y_max_zone"]; $y++) {
				$data["y_palissade"] = $y;
				$data["x_palissade"] = $z["x_min_zone"] - 1;
				$palissadeTable->insert($data);
				$data["x_palissade"] = $z["x_max_zone"] + 1;
				$palissadeTable->insert($data);
			}
			
			$this->suppressionElements($z["x_min_zone"], $z["x_max_zone"], $z["y_min_zone"], $z["y_max_zone"], $z["z_zone"]);
		}
	}

	private function creationPalissadesInternes() {
		Zend_Loader::loadClass("DonjonPalissade");

		$donjonPalissadeTable = new DonjonPalissade();
		$palissades = $donjonPalissadeTable->findByIdDonjon($this->donjonCourant["id_donjon"]);

		Zend_Loader::loadClass("Palissade");
		$palissadeTable = new Palissade();

		foreach ($palissades as $p) {
			$data["x_palissade"] = $p["x_donjon_palissade"];
			$data["y_palissade"] = $p["y_donjon_palissade"];
			$data["z_palissade"] = $p["z_donjon_palissade"];
			$data["agilite_palissade"] = $p["agilite_donjon_palissade"];
			$data["armure_naturelle_palissade"] = $this->equipeCourante["niveau_moyen_donjon_equipe"];
			$data["pv_max_palissade"] = $this->equipeCourante["niveau_moyen_donjon_equipe"] * 10;
			$data["pv_restant_palissade"] = $data["pv_max_palissade"];
			$data["est_destructible_palissade"] = $p["est_destructible_donjon_palissade"];
			$data["id_fk_donjon_palissade"] = $p["id_fk_donjon_palissade"];
			$data["date_creation_palissade"] = date("Y-m-d H:i:s");
			$data["date_fin_palissade"] = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), 100);
			$palissadeTable->insert($data);
		}
	}


	private function creationCrevasses() {
		Zend_Loader::loadClass("Crevasse");
		$crevasseTable = new Crevasse();
		$where = "id_fk_donjon_crevasse = ".$this->donjonCourant["id_donjon"];
		$crevasseTable->delete($where);

		Zend_Loader::loadClass("DonjonCrevasse");
		$donjonCrevasseTable = new DonjonCrevasse();
		$crevasses = $donjonCrevasseTable->findByIdDonjon($this->donjonCourant["id_donjon"]);

		foreach ($crevasses as $c) {
			$data["x_crevasse"] = $c["x_donjon_crevasse"];
			$data["y_crevasse"] = $c["y_donjon_crevasse"];
			$data["z_crevasse"] = $c["z_donjon_crevasse"];
			$data["id_fk_donjon_crevasse"] = $c["id_fk_donjon_crevasse"];
			$crevasseTable->insert($data);
		}
	}

	private function creationNids() {
		Zend_Loader::loadClass("Nid");
		$nidTable = new Nid();
		$where = "id_fk_donjon_nid = ".$this->donjonCourant["id_donjon"];
		$nidTable->delete($where);

		Zend_Loader::loadClass("DonjonNid");
		$donjonNidTable = new DonjonNid();
		$nids = $donjonNidTable->findByIdDonjon($this->donjonCourant["id_donjon"]);

		foreach ($nids as $n) {
			$nbMonstres = Bral_Util_De::get_de_specifique($n["nb_membres_min_type_groupe_monstre"], $n["nb_membres_max_type_groupe_monstre"]);
			$data["x_nid"] = $n["x_donjon_nid"];
			$data["y_nid"] = $n["y_donjon_nid"];
			$data["z_nid"] = $n["z_donjon_nid"];
			$data["nb_monstres_total_nid"] = $nbMonstres;
			$data["nb_monstres_restants_nid"] = $nbMonstres;

			$data["id_fk_zone_nid"] = $n["id_fk_zone_nid_donjon_nid"];
			$data["id_fk_type_monstre_nid"] = $n["id_fk_type_monstre_donjon_nid"];

			$data["id_fk_donjon_nid"] = $n["id_fk_donjon_nid"];
			$data["date_creation_nid"] = date("Y-m-d H:i:s");
			
			$data["date_generation_nid"] = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), abs($n["z_donjon_nid"]) - 7);

			$nidTable->insert($data);
		}
	}

	private function creationMonstres() {
		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();
		$where = "id_fk_donjon_monstre = ".$this->donjonCourant["id_donjon"];
		$monstreTable->delete($where);

		Zend_Loader::loadClass("Bral_Batchs_Factory");
		Bral_Batchs_Factory::calculBatch("CreationMonstres", $this->view, $this->donjonCourant["id_donjon"]);
	}
	
	private function suppressionElements($xmin, $xmax, $ymin, $ymax, $z) {
		$this->deleteInElement("ElementAliment", "_element_aliment", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementEquipement", "_element_equipement", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementMateriel", "_element_materiel", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementMinerai", "_element_minerai", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementPartieplante", "_element_partieplante", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementPotion", "_element_potion", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementRune", "_element_rune", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("ElementTabac", "_element_tabac", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("Charrette", "_charrette", $xmin, $xmax, $ymin, $ymax, $z);
		$this->deleteInElement("Element", "_element", $xmin, $xmax, $ymin, $ymax, $z);
	}
	
	private function deleteInElement($nom, $prefix, $xmin, $xmax, $ymin, $ymax, $z) {
		Zend_Loader::loadClass($nom);
		$table = new $nom();
		$where = "x".$prefix. " >= ". $xmin;
		$where .= " AND x".$prefix. " <= ". $xmax;
		$where .= " AND y".$prefix. " >= ". $ymin;
		$where .= " AND y".$prefix. " <= ". $ymax;
		$where .= " AND z".$prefix. " = ". $z;
		$table->delete($where);
	}

	private function calculCoutCastars() {
		return 0;
	}
}