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
		$this->prepareDonjon();
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
		}

	}

	private function inscriptionNouvelleEquipe() {
		$hobbits = $this->recupereHobbitFromValeur1();

		Zend_Loader::loadClass("Bral_Util_Messagerie");

		Zend_Loader::loadClass("DonjonEquipe");
		$donjonEquipeTable = new DonjonEquipe();
		$mdate = date("Y-m-d H:i:s");
		$mdateLimite = Bral_Util_ConvertDate::get_date_add_day_to_date($mdate, 5);
		$dataEquipe = array(
			"id_fk_donjon_equipe" => $this->donjonCourant["id_donjon"],
			"id_fk_hobbit_meneur_equipe" => $this->view->user->id_hobbit,
			"date_creation_donjon_equipe" => $mdate,
			"date_limite_inscription_donjon_equipe" => $mdateLimite,
			"etat_donjon_equipe" => "inscription",
			"nb_jour_restant_donjon_equipe" => null,
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

		$this->messageSignature($message);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $hobbit["id_hobbit"], $message, $this->view);
	}

	public function envoieMessageInscriptionHobbit() {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;

		$message .= "Félicitations ! ".PHP_EOL;
		$message .= "Vous avez validé votre inscription au Donjon.".PHP_EOL.PHP_EOL;
		$message .= " Attendez maintenant que tous vos coéquipiers valident leur ";
		$message .= " inscription et que le meneur puisse ouvrir la porte";

		$this->messageSignature($message);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $this->view->user->id_hobbit, $message, $this->view);
	}

	private function messageSignature(&$message) {
		$message .= $this->donjonCourant["prenom_hobbit"]. " ".$this->donjonCourant["nom_hobbit"]. ", ";
		if ($this->donjonCourant["sexe_hobbit"] == "masculin") {
			$message .= "garde";
		} else {
			$message .= "gardienne";
		}
		$message .= " du donjon de la ".$this->donjonCourant["nom_region"].PHP_EOL;
		$message .= "Inutile de répondre à ce message.";
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
			$h["y_hobbit"] != $this->view->user->y_hobbit)) {
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
				"z_hobbit" => -1,
			);
			$hobbitTable->update($data, $where);
		}
	}

	public function envoieMessageDescente() {
		$message = "[Poste de Garde]".PHP_EOL.PHP_EOL;
		$message .=  $this->view->user->prenom_hobbit. " ".$this->view->user->nom_hobbit;
		$message .= " (".$this->view->user->id_hobbit.") a ouvert la porte du Donjon.";
		$message .= "Vous êtes entrés avec lui...".PHP_EOL;
		$message .= "Vous avez maintenant deux lunes pour sortir victorieux ou sinon gare aux conséquences.".PHP_EOL;

		$this->messageSignature($message);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->donjonCourant["id_fk_pnj_donjon"], $this->view->user->id_hobbit, $message, $this->view);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

	private function calculCoutCastars() {
		return 0;
	}

}