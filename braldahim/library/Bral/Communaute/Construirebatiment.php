<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Construirebatiment extends Bral_Communaute_Communaute {

	function getTitreOnglet() {}
	function setDisplay($display) {}

	function getNomInterne() {
		return "box_action";
	}

	function getTitre() {
		return "Construire un bâtiment";
	}

	function prepareCommun() {

		Zend_Loader::loadClass("Bral_Util_Communaute");
		if (!Bral_Util_Communaute::possedeSurHall($this->view->user->id_fk_communaute_braldun)) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: Hall invalide idC:".$this->view->user->id_fk_communaute_braldun);
		}
		
		$this->view->nb_pa = 1;

		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('TypeLieu');
		Zend_Loader::loadClass('Bral_Helper_Communaute');

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$tabBatiment = null;
		$tabPA = null;

		if ($lieux != null && count($lieux) == 1) {
			$lieu = $lieux[0];
			if ($lieu["niveau_lieu"] != $lieu["niveau_prochain_lieu"]) {
				$tab["lieu"] = $lieu;
				$tab["couts"] = Bral_Util_Communaute::getCoutsAmeliorationBatiment($lieu["niveau_prochain_lieu"]);
				$tabBatiment = $tab;

				for ($i = 1; $i <= $this->view->user->pa_braldun; $i ++) {
					$tabPA[] = $i;
				}
			}
		}
		$this->view->tabPA = $tabPA;
		$this->view->batiment = $tabBatiment;
	}


	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->assezDePa == false) {
			return;
		}

		if (((int)$this->_request->get("valeur_1").""!=$this->_request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: Choix invalide : ".$this->_request->get("valeur_1"));
		} else {
			$participationNbPA = (int)$this->_request->get("valeur_1");
		}

		if (((int)$this->_request->get("valeur_2").""!=$this->_request->get("valeur_2")."")) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: Choix invalide : ".$this->_request->get("valeur_2"));
		} else {
			$paticipationNbCastars = (int)$this->_request->get("valeur_2");
		}

		if ($participationNbPA <= 0) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: participation PA invalide 1 : ".$participationNbPA);
		}

		if ($participationNbPA > $this->view->user->pa_braldun) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: participation PA invalide 2 : ".$participationNbPA. " PA B:".$this->view->user->pa_braldun);
		}

		if ($paticipationNbCastars < 0) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: participation castars invalide : ". $paticipationNbCastars);
		}

		if ($paticipationNbCastars > $this->view->user->castars_braldun) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: participation castars invalide 2 : ". $paticipationNbCastars. " castars B:".$this->view->user->castars_braldun);
		}

		$this->calculConstruire($participationNbPA, $paticipationNbCastars);
		$this->majBraldun();

		$this->view->nomLieu = $this->view->batiment['lieu']['nom_lieu'];
	}

	private function calculConstruire($participationNbPA, $paticipationNbCastars) {

		$niveauAtteint = false;
		$niveau = $this->view->batiment['lieu']['niveau_lieu'];

		$nbPaManquants = $this->view->batiment['couts']['cout_pa']  - $this->view->batiment['lieu']['nb_pa_depenses_lieu'];
		$nbCastarsManquants = $this->view->batiment['couts']['cout_castar'] - $this->view->batiment['lieu']['nb_pa_depenses_lieu'];

		$nbPaDepenses = $this->view->batiment['lieu']['nb_pa_depenses_lieu'] + $participationNbPA;
		$nbCastarsDepenses = $this->view->batiment['lieu']['nb_castars_depenses_lieu'] + $paticipationNbCastars;

		if ($this->view->batiment['couts']['cout_pa'] <= $nbPaDepenses
		&& $this->view->batiment['couts']['cout_castar'] <= $nbCastarsDepenses) {
			$niveauAtteint = true;
			$niveau = $this->view->batiment['lieu']['niveau_lieu'] + 1;
		}

		if ($this->view->batiment['couts']['cout_pa'] <= $nbPaDepenses) {
			if ($nbPaDepenses > $this->view->batiment['couts']['cout_pa']) {
				$nbPaDepenses = $this->view->batiment['couts']['cout_pa'];
				$participationNbPA = $nbPaManquants;
			}
		}

		if ($this->view->batiment['couts']['cout_castar'] <= $nbCastarsDepenses) {
			if ($nbCastarsDepenses > $this->view->batiment['couts']['cout_castar']) {
				$nbCastarsDepenses = $this->view->batiment['couts']['cout_castar'];
				$paticipationNbCastars = $nbCastarsManquants;
			}
		}

		$lieuTable = new Lieu();
		$data = array(
			'niveau_lieu' => $niveau,
			'nb_pa_depenses_lieu' => $nbPaDepenses,
			'nb_castars_depenses_lieu' => $nbCastarsDepenses,
		);
		$where = 'id_lieu = '.intval($this->view->batiment['lieu']['id_lieu']);
		$lieuTable->update($data, $where);

		$this->view->niveauAtteint = $niveauAtteint;
		$this->view->participationPA = $participationNbPA;
		$this->view->participationCastars = $paticipationNbCastars;

		$this->view->nb_pa = $participationNbPA;
		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $paticipationNbCastars;

		$details = $this->view->batiment['lieu']['nom_lieu'];
		if ($niveauAtteint) {
			$detailsBot = "La construction du bâtiment -".$this->view->batiment['lieu']['nom_lieu']."- est terminée".PHP_EOL.PHP_EOL;
		} else {
			$detailsBot = "La construction du bâtiment -".$this->view->batiment['lieu']['nom_lieu']."- a avancé. ".PHP_EOL.PHP_EOL;
		}

		$s = '';
		if ($paticipationNbCastars > 1) {
			$s = 's';
		}
		$detailsBot .= "Dépense : ".$paticipationNbCastars." castar".$s." et ".$participationNbPA." PA".PHP_EOL;

		if ($niveauAtteint) {
			$detailsBot .= "Le bâtiment est entièrement construit !";
		} else {
			$detailsBot .= "Le bâtiment est toujours en construction vers le niveau ".$this->view->batiment['lieu']['niveau_prochain_lieu'].".";
		}

		$detailsBot .= PHP_EOL.PHP_EOL."Action réalisée par [b".$this->view->user->id_braldun."]";
		Zend_Loader::loadClass('Bral_Util_EvenementCommunaute');
		Zend_Loader::loadClass('TypeEvenementCommunaute');
		Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_CONSTRUCTION_BATIMENT, $details, $detailsBot, $this->view);

	}

	function getListBoxRefresh() {
		$tab = array("box_profil", "box_lieu", "box_communaute", "box_evenements");
		if ($this->view->nomLieu != null) {
			$tab[] = "box_vue";
		}
		return $tab;
	}

}