<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Franchirportail extends Bral_Competences_Competence {

	function prepareCommun() {

		Zend_Loader::loadClass('Bral_Util_Marcher');
		Zend_Loader::loadClass('Bral_Util_Quete');
		Zend_Loader::loadClass('Palissade');

		$numeroPortail = $this->request->get("numeroPortail");
		$numeroPortailChoisi = $this->request->get("valeur_2");

		$xPortail = $this->view->user->x_braldun;
		$yPortail = $this->view->user->y_braldun - 1;

		$x_min = $this->view->user->x_braldun - 1;
		$y_min = $this->view->user->y_braldun - 1;
		$x_max = $this->view->user->x_braldun + 1;
		$y_max = $this->view->user->y_braldun + 1;

		// On compte le nombre de portails à une distance de 1.
		$tablePalissades = new Palissade();
		$portails = $tablePalissades->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun, true);

		$tabPortails = null;
		$portailEnCours = null;
		if ($portails != null && count($portails) > 0) {
			foreach ($portails as $p) {
				$tab = array(
					'x_portail' => $p["x_palissade"],
					'y_portail' => $p["y_palissade"],
					'id_portail' => $p["id_palissade"],
					'code_1_palissade' => $p["code_1_palissade"],
					'code_2_palissade' => $p["code_2_palissade"],
					'code_3_palissade' => $p["code_3_palissade"],
					'code_4_palissade' => $p["code_4_palissade"],
				);
				if ($p["id_palissade"] == $numeroPortail || $p["id_palissade"] == $numeroPortailChoisi || count($portails) == 1) { // portail en cours
					$portailEnCours = $tab;
				}
				$tabPortails[$p["id_palissade"]] = $tab;
			}
		}

		$etatFormulaire = null;

		if ($portailEnCours != null) {
			$etatFormulaire = "choixDestination";
			// S'il n'y a qu'un portail
			$this->prepareCommunDestination($portailEnCours["x_portail"], $portailEnCours["y_portail"]);
		} elseif (count($tabPortails) > 1) { // S'il y a plusieurs portails
			$etatFormulaire = "choixPortail";
			$this->view->assezDePa = true;
		}

		if (count($tabPortails) == 0) {
			// Pas de portail
			$etatFormulaire = null;
		}

		$this->view->numeroPortail = $numeroPortail;
		$this->portailEnCours = $portailEnCours;
		$this->view->tabPortails = $tabPortails;
		$this->view->etatFormulaire = $etatFormulaire;
		$tabChiffres = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
		$this->view->chiffres = $tabChiffres;
	}

	function prepareCommunDestination($xPortail, $yPortail) {
		$utilMarcher = new Bral_Util_Marcher();

		$selection = $this->request->get("valeur_1"); // si l'on vient de la vue (clic sur l'icone marcher)

		$calcul = $utilMarcher->calcul($this->view->user, $selection, false, $xPortail, $yPortail);

		$this->view->effetMot = $calcul["effetMot"];
		$this->view->assezDePa = $calcul["assezDePa"];
		$this->view->nb_cases = $calcul["nb_cases"];
		$this->view->nb_pa = $calcul["nb_pa"];
		$this->view->tableau = $calcul["tableau"];
		$this->tableauValidation = $calcul["tableauValidation"];
		$this->view->environnement = $calcul["environnement"];
		$this->view->franchirPossible = $calcul["marcherPossible"];
		$this->view->estEngage = $calcul["estEngage"];
		$this->view->estSurRoute = $calcul["estSurRoute"];

		$this->view->x_min = $calcul["x_min"];
		$this->view->x_max = $calcul["x_max"];
		$this->view->y_min = $calcul["y_min"];
		$this->view->y_max = $calcul["y_max"];
	}

	function calculNbPa() {
		// fait dans UtilMarcher
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		if ($this->view->assezDePa == false) {
			return;
		}

		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = preg_split("/h/", $x_y);

		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this) . " Deplacement X impossible : " . $offset_x);
		}

		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this) . " Deplacement Y impossible : " . $offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this) . " Deplacement XY impossible : " . $offset_x . $offset_y);
		}

		if ($this->portailEnCours == null) {
			throw new Zend_Exception(get_class($this) . " portail invalide");
		}

		$chiffre_1 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$chiffre_2 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		$chiffre_3 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
		$chiffre_4 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_6"));

		$this->view->codeOk = false;
		$this->view->codeSaisi = $chiffre_1 . "" . $chiffre_2 . "" . $chiffre_3 . "" . $chiffre_4;
		if ($this->portailEnCours["code_1_palissade"] == $chiffre_1 &&
			$this->portailEnCours["code_2_palissade"] == $chiffre_2 &&
			$this->portailEnCours["code_3_palissade"] == $chiffre_3 &&
			$this->portailEnCours["code_4_palissade"] == $chiffre_4
		) {
			$this->view->codeOk = true;
		}

		if (!$this->view->codeOk) {
			$id_type = $this->view->config->game->evenements->type->deplacement;
			if ($this->view->user->sexe_braldun == "feminin") {
				$e = "e";
			} else {
				$e = "";
			}
			$details = "[b" . $this->view->user->id_braldun . "] reste bloqué" . $e . " devant un portail";
			$this->setDetailsEvenement($details, $id_type);
			$this->setEvenementQueSurOkJet1(false);

			$this->calculBalanceFaim();
			$this->majBraldun();
			return;
		}

		$this->view->user->x_braldun = $this->view->user->x_braldun + $offset_x;
		$this->view->user->y_braldun = $this->view->user->y_braldun + $offset_y;

		Zend_Loader::loadClass("Bral_Util_Crevasse");
		$this->view->estCrevasseEvenement = Bral_Util_Crevasse::calculCrevasse($this->view->user);

		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = "[b" . $this->view->user->id_braldun . "] a franchi un portail";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);

		$this->view->estQueteEvenement = Bral_Util_Quete::etapeMarcher($this->view->user);

		$this->calculBalanceFaim();
		$this->calculFinMatchSoule();
		$this->majBraldun();

		Zend_Loader::loadClass("Bral_Util_Filature");
		Bral_Util_Filature::action($this->view->user, $this->view);

		if ($this->view->user->est_soule_braldun == "oui") {
			Zend_Loader::loadClass("Bral_Util_Soule");
			Bral_Util_Soule::deplacerAvecBallon($this->view->user, $offset_x, $offset_y);
		}
	}


	function getListBoxRefresh() {
		$tab = array("box_vue", "box_lieu", "box_echoppes", "box_champs", "box_blabla");
		if ($this->view->user->est_soule_braldun == "oui") {
			$tab[] = "box_soule";
		}
		return $this->constructListBoxRefresh($tab);
	}

}