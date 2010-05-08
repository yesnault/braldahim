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

		if ($this->verificationSoule() == false) {
			return;
		}

		$this->prepareDonjon();
		Bral_Util_Donjon::controleInscriptionEquipe($this->donjonCourant, $this->view);

		$this->prepareEquipe();
		$this->prepareInscription();
		if ($this->view->estMeneur && $this->view->inscriptionRealisee) {
			$this->prepareDescente();
		}
	}

	private function verificationSoule() {
		$retour = true;

		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();
		$idBraldunsTab[] = $this->view->user->id_braldun;
		$soule = $souleEquipeTable->countNonDebuteByIdBraldunList($idBraldunsTab);
		foreach($soule as $s) {
			if ($s["nombre"] != 0) {
				$retour = false;
				break;
			}
		}
		
		if ($this->view->user->est_soule_braldun == 'oui') {
			$retour = false;
		}

		$this->view->soulePossible = $retour;
		
		return $retour;
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
			if ($this->view->user->id_braldun == $this->equipeCourante["id_fk_braldun_meneur_equipe"]) {
				$this->view->estMeneur = true;
			}
		} else {
			$this->equipeCourante = null;
		}
	}

	private function prepareInscription() {

		if ($this->equipeCourante == null) {

			$this->view->inscriptionParBraldunNouvelleEquipePossible = false;

			Zend_Loader::loadClass("BraldunsDistinction");
			$braldunsDistinctionTable = new BraldunsDistinction();
			$distinction = $braldunsDistinctionTable->findDistinctionsByBraldunIdAndIdTypeDistinction($this->view->user->id_braldun, $this->donjonCourant["id_fk_distinction_quete_region"]);
			if (count($distinction) == 1) {
				$this->view->inscriptionParBraldunNouvelleEquipePossible = true;
			} else if (count($distinction) > 1) {
				throw new Zend_Exception("Nb. Distinctions invalides nb=".count($distinction));
			}
			return;
		}

		// on regarde si le joueur est demandé dans l'équipe courante

		Zend_Loader::loadClass('DonjonBraldun');
		$donjonBraldunTable = new DonjonBraldun();
		$donjonBraldun = $donjonBraldunTable->findByIdBraldunAndIdEquipe($this->view->user->id_braldun, $this->equipeCourante["id_donjon_equipe"]);

		$this->view->inscriptionDemandee = false;
		$this->view->inscriptionRealisee = false;
		if ($donjonBraldun != null) {
			$donjonBraldun = $donjonBraldun[0];
			if ($donjonBraldun["date_inscription_donjon_braldun"] == null) {
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
			throw new Zend_Exception(get_class($this)." Utilisation impossible : pa:".$this->view->user->pa_braldun." cout:".$this->$this->view->paUtilisationLieu);
		}

		if ($this->view->estMeneur && $this->view->descentePossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 1 idh:".$this->view->user->id_braldun);
		} elseif ($this->view->estMeneur == false && $this->view->nouvelleEquipePossible == false && $this->view->inscriptionDemandee == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 2 idh:".$this->view->user->id_braldun);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParBraldunNouvelleEquipePossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible 3 idh:".$this->view->user->id_braldun);
		}

		if ($this->view->nouvelleEquipePossible == true && $this->view->inscriptionParBraldunNouvelleEquipePossible == true) {
			$this->inscriptionNouvelleEquipe();
			$this->view->inscriptionEquipe = true;
			$this->majBraldun();
		} elseif ($this->view->inscriptionDemandee == true) {
			$this->inscriptionBraldun();
			$this->envoieMessageInscriptionBraldun();
			$this->majBraldun();
		} elseif ($this->view->estMeneur && $this->view->inscriptionRealisee) {
			$this->calculDescente();
			$this->creationDonjon();
			$this->majBraldun();
		}

	}

	private function inscriptionNouvelleEquipe() {
		$bralduns = $this->recupereBraldunFromValeur1();

		Zend_Loader::loadClass("Bral_Util_Messagerie");

		Zend_Loader::loadClass("DonjonEquipe");
		$donjonEquipeTable = new DonjonEquipe();
		$mdate = date("Y-m-d H:i:s");
		$mdateLimite = Bral_Util_ConvertDate::get_date_add_day_to_date($mdate, 5);

		$niveauMoyenBralduns = null;
		$totalNiveau = 0;
		foreach($bralduns as $h) {
			$totalNiveau = $totalNiveau + $h["niveau_braldun"];
		}

		$niveauMoyenBralduns = $totalNiveau / count($bralduns);

		$dataEquipe = array(
			"id_fk_donjon_equipe" => $this->donjonCourant["id_donjon"],
			"id_fk_braldun_meneur_equipe" => $this->view->user->id_braldun,
			"date_creation_donjon_equipe" => $mdate,
			"date_limite_inscription_donjon_equipe" => $mdateLimite,
			"etat_donjon_equipe" => "inscription",
			"date_fin_donjon_equipe" => null,
			"niveau_moyen_donjon_equipe" => $niveauMoyenBralduns,
		);
		$idEquipe = $donjonEquipeTable->insert($dataEquipe);
		$dataEquipe["id_donjon_equipe"] = $idEquipe;

		Zend_Loader::loadClass("DonjonBraldun");
		$donjonBraldunTable = new DonjonBraldun();
		$tabBralduns = null;
		foreach($bralduns as $h) {
			$dataBraldun = array("id_fk_braldun_donjon_braldun" => $h["id_braldun"], "id_fk_equipe_donjon_braldun" => $idEquipe);
			$donjonBraldunTable->insert($dataBraldun);
			$this->envoieMessageInscriptionEquipe($h, $mdateLimite);
			$tabBralduns[] = array(
				"nom_braldun" => $h["nom_braldun"],
				"prenom_braldun" => $h["prenom_braldun"],
				"id_braldun" => $h["id_braldun"],
			);
		}
		$this->view->tabBraldunsEquipe = $tabBralduns;

		$this->equipeCourante = $dataEquipe;
		$this->inscriptionBraldun();
	}

	private function recupereBraldunFromValeur1() {
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		$braldunsList = $filter->filter(trim($this->request->get('valeur_1')));

		$braldunsList = $braldunsList.",".$this->view->user->id_braldun;
		$idBraldunsTab = split(',', $braldunsList);

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByIdListAndIdTypeDistinction($idBraldunsTab, $this->view->idTypeDistinctionCourante);

		if ($bralduns == null) {
			throw new Zend_Exception(get_class($this)." Liste invalide:".$braldunsList." distinction".$this->view->idTypeDistinctionCourante);
		} else if (count($bralduns) != 9) {
			throw new Zend_Exception(get_class($this)." Liste invalide B:".$braldunsList. " count=".count($bralduns));
		}

		Zend_Loader::loadClass('Bral_Util_Distinction');
		$idTypeDistinctionDonjon = Bral_Util_Distinction::getIdDistinctionDonjonFromIdDistinctionBourlingueur($this->view->idTypeDistinctionCourante);
		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();
		$distinctionsDonjon = $braldunsDistinctionTable->countDistinctionByIdBraldunList($idBraldunsTab, $idTypeDistinctionDonjon);
		foreach($distinctionsDonjon as $d) {
			if ($d["nombre"] != 0) {
				throw new Zend_Exception(get_class($this)." Liste invalide C:".$d["nombre"]. " h:".$d["id_fk_braldun_hdistinction"]);
			}
		}

		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();
		$soule = $souleEquipeTable->countNonDebuteByIdBraldunList($idBraldunsTab);
		foreach($soule as $s) {
			if ($s["nombre"] != 0) {
				throw new Zend_Exception(get_class($this)." Liste invalide D:".$s["nombre"]. " h:".$s["id_fk_braldun_soule_equipe"]);
			}
		}

		return $bralduns;
	}

	public function envoieMessageInscriptionEquipe($braldun, $dateLimite) {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		if ($braldun["id_braldun"] != $this->view->user->id_braldun) {
			$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;
			$message .=  $this->view->user->prenom_braldun. " ".$this->view->user->nom_braldun;
			$message .= " (".$this->view->user->id_braldun.") vous a demandé en tant que coéquipier";
			$message .= " pour rentrer dans le donjon de la ".$this->donjonCourant["nom_region"].".".PHP_EOL;
			$message .= " Vous pouvez accepter en validant votre inscription au Poste de Garde en ".$this->view->user->x_braldun. "/".$this->view->user->y_braldun.'.'.PHP_EOL.PHP_EOL;
			$message .= " Si vous ne validez pas votre inscription avant le ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e\; H:i:s', $dateLimite);
			$message .= ", le meneur ne pourra pas ouvrir la porte et l'inscription de toute l'équipe sera annulée.".PHP_EOL.PHP_EOL;
		} else {
			$message .= " Vous avez inscrit une équipe pour le donjon de la ".$this->donjonCourant["nom_region"].".".PHP_EOL;
			$message .= " En tant que meneur, votre inscription est automatiquement validée.".PHP_EOL;
			$message .= " Par contre, tous vos coéquipiers doivent valider leur inscription au Poste de Garde en ".$this->view->user->x_braldun. "/".$this->view->user->y_braldun.'.'.PHP_EOL;
			$message .= " Et quand ils auront tous validé leur inscription, vous pourrez ouvrir la porte du Donjon pour les faire tous descendre avec vous, automatiquement.".PHP_EOL.PHP_EOL;
		}

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $braldun["id_braldun"], $message, $this->view);
	}

	public function envoieMessageInscriptionBraldun() {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Félicitations ! ".PHP_EOL;
		$message .= "Vous avez validé votre inscription au Donjon.".PHP_EOL.PHP_EOL;
		$message .= " Attendez maintenant que tous vos coéquipiers valident leur ";
		$message .= " inscription et que le meneur puisse ouvrir la porte";

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $this->view->user->id_braldun, $message, $this->view);
	}

	private function inscriptionBraldun() {
		Zend_Loader::loadClass('DonjonBraldun');
		$donjonBraldunTable = new DonjonBraldun();

		$where = "id_fk_braldun_donjon_braldun = ".$this->view->user->id_braldun;
		$where .= " AND id_fk_equipe_donjon_braldun = ".$this->equipeCourante["id_donjon_equipe"];
			
		$data["date_inscription_donjon_braldun"] = date("Y-m-d H:i:s");
		$donjonBraldunTable->update($data, $where);
	}

	private function prepareDescente() {
		// verification que tous les Braldûns de l'équipe sont sur la case.
		Zend_Loader::loadClass('DonjonBraldun');
		$donjonBraldunTable = new DonjonBraldun();
		$donjonBraldun = $donjonBraldunTable->findByIdEquipe($this->equipeCourante["id_donjon_equipe"]);

		$inscriptionEquipeOk = true;

		foreach($donjonBraldun as $h) {

			if ($h["date_inscription_donjon_braldun"] == null) {
				$inscriptionEquipeOk = false;
				break;
			} elseif (($h["x_braldun"] != $this->view->user->x_braldun ||
			$h["y_braldun"] != $this->view->user->y_braldun &&
			$h["z_braldun"] != $this->view->user->z_braldun)) {
				$inscriptionEquipeOk = false;
				break;
			}
		}

		$this->view->descentePossible = false;

		if ($inscriptionEquipeOk) {
			$this->view->descentePossible = true;
			$this->braldunsADescendre = $donjonBraldun;
		}

	}

	private function calculDescente() {

		$braldunTable = new Braldun();
		foreach($this->braldunsADescendre as $h) {
			$where = "id_braldun = ".$h["id_braldun"];
			$data = array(
				'z_braldun' => -1,
				'est_donjon_braldun' => 'oui',
			);
			$braldunTable->update($data, $where);
		}
		$this->view->user->z_braldun = -1;

		$donjonEquipeTable = new DonjonEquipe();

		$data = array(
			"etat_donjon_equipe" => "en_cours",
			"date_fin_donjon_equipe" =>  Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d 00:00:00"), 21),
		);
		$where = 'id_donjon_equipe='.$this->equipeCourante["id_donjon_equipe"];
		$donjonEquipeTable->update($data, $where);
	}

	public function envoieMessageDescente($idBraldun) {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		if ($idBraldun == $this->view->user->id_braldun) {
			$message .= "Vous avez ouvert la porte du Donjon".PHP_EOL;
		} else {
			$message .=  $this->view->user->prenom_braldun. " ".$this->view->user->nom_braldun;
			$message .= " (".$this->view->user->id_braldun.") a ouvert la porte du Donjon.";
			$message .= "Vous êtes entrés avec lui...".PHP_EOL;
		}
		$message .= "Vous avez maintenant une lune pour sortir victorieux ou sinon gare aux conséquences.".PHP_EOL.PHP_EOL;

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $idBraldun, $message, $this->view);
	}

	private function envoieMessageDescenteBralduns() {
		Bral_Util_Log::batchs()->trace("Bral_Lieux_Postedegarde - envoieMessageDescenteBralduns - enter -");

		$listeBralduns = "";
		foreach($this->braldunsADescendre as $h) {
			$this->envoieMessageDescente($h["id_braldun"]);
			$listeBralduns .= $h["prenom_braldun"]. " ".$h["nom_braldun"]. " (".$h["id_braldun"]."), ";
		}

		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findById($this->donjonCourant["id_fk_region_donjon"]);
		$nomComte = $region["nom_region"];

		$donjonEquipeTable = new DonjonEquipe();
		$donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($this->donjonCourant["id_donjon"]);
		$equipeCourante = $donjonEquipe[0];

		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Bonjour à vous habitants de Braldahim.".PHP_EOL.PHP_EOL;
		$message .= "En ce jour, une bien belle équipe ";
		$message .= " est entrée dans le Donjon de la $nomComte afin d'en découdre avec [m".$equipeCourante["id_fk_monstre_donjon_equipe"]."].".PHP_EOL.PHP_EOL;
		$message .= "Souhaitons leur bonne chance : ".$listeBralduns;
		$message .= " espérons qu'ils lui mettent une bonne rouste à [m".$equipeCourante["id_fk_monstre_donjon_equipe"]."], sinon les conséquences seraient terribles.".PHP_EOL.PHP_EOL;

		Zend_Loader::loadClass("Bral_Util_Lien");
		$message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

		Bral_Util_Donjon::messageSignature($message, $this->donjonCourant);
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findAllJoueurs();
		Bral_Util_Log::batchs()->trace("Bral_Lieux_Postedegarde - envoieMessageDescenteBralduns - nbJoueurs:".count($bralduns));
		foreach($bralduns as $h) {
			Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $h["id_braldun"], $message, $this->view);
		}
		Bral_Util_Log::batchs()->trace("Bral_Lieux_Postedegarde - envoieMessageDescenteBralduns - exit -");
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
		$this->envoieMessageDescenteBralduns();
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
		Bral_Util_Donjon::creationNids($this->donjonCourant, "creation");
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