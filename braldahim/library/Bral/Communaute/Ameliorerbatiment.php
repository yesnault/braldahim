<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Ameliorerbatiment extends Bral_Communaute_Communaute {

	function getNomInterne() {
		return "box_action";
	}

	function getTitre() {
		return "Améliorer un bâtiment";
	}

	function getTitreOnglet() {}
	function setDisplay($display) {}

	function prepareCommun() {
		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			throw new Zend_Exception(get_class($this)." Vous n'êtes pas tenancier de la communauté ". $this->view->user->rangCommunaute);
		}

		Zend_Loader::loadClass("Bral_Util_Communaute");
		if (!Bral_Util_Communaute::possedeUnHall($this->view->user->id_fk_communaute_braldun)) {
			throw new Zend_Exception("Bral_Communaute_Construirebatiment :: Hall invalide idC:".$this->view->user->id_fk_communaute_braldun);
		}

		$this->view->nomLieu = null;

		$this->view->nb_pa = 1;

		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('TypeLieu');

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun);

		$tabLieux = null;
		if ($lieux != null && count($lieux) > 0) {
			foreach($lieux as $l) {
				if ($l["niveau_lieu"] == $l["niveau_prochain_lieu"] && $l["id_type_lieu"] != TypeLieu::ID_TYPE_HALL) {
					$tab["lieu"] = $l;
					$tab["couts"] = Bral_Util_Communaute::getCoutsAmeliorationBatiment($l["niveau_prochain_lieu"]);
					$tabLieux[] = $tab;
				}
			}
		}

		$this->view->lieuxCommunaute = $tabLieux;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->assezDePa == false) {
			return;
		}

		if (((int)$this->_request->get("valeur_1").""!=$this->_request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Type invalide : ".$this->_request->get("valeur_1"));
		} else {
			$idLieu = (int)$this->_request->get("valeur_1");
		}

		$trouve = false;
		$lieu = null;

		foreach($this->view->lieuxCommunaute as $l) {
			if ($l['lieu']['id_lieu'] == $idLieu) {
				$trouve = true;
				$lieu = $l['lieu'];
			}
		}

		if ($trouve == false || $lieu == null) {
			throw new Zend_Exception(get_class($this)." Lieu invalide : ".$idLieu);
		}

		$this->calculAmeliorer($lieu);
		$this->majBraldun();
		$this->view->nomLieu = $lieu['nom_lieu'];
	}

	private function calculAmeliorer($lieu) {

		$niveauSuivant = $lieu['niveau_lieu'] + 1;

		$lieuTable = new Lieu();
		$data = array(
			'niveau_prochain_lieu' => $niveauSuivant,
			'nb_pa_depenses_lieu' => 0,
			'nb_castars_depenses_lieu' => 0,
		);
		$where = 'id_lieu = '.intval($lieu['id_lieu']);
		$lieuTable->update($data, $where);

		$this->view->niveauSuivant = $niveauSuivant;

		Zend_Loader::loadClass("TypeEvenementCommunaute");
		Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

		$details = $lieu['nom_lieu'];
		$detailsBot = "Le bâtiment -".$lieu['nom_lieu']."- a été amélioré. ".PHP_EOL;
		$detailsBot .= "Le bâtiment est maintenant en construction vers le niveau ".$niveauSuivant.".".PHP_EOL;
		$detailsBot .= "Pour le construire complètement, chaque Braldûn de la communauté peut aller sur le bâtiment et ";
		$detailsBot .= "utiliser l'action -Construire un bâtiment- pour faire progresser la construction".PHP_EOL.PHP_EOL;
		$detailsBot .= "La progression de chaque construction est visible dans l'onglet Communauté".PHP_EOL;

		$detailsBot .= PHP_EOL.PHP_EOL."Action réalisée par [b".$this->view->user->id_braldun."]";
		Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_AMELIORATION, $details, $detailsBot, $this->view);

	}

	function getListBoxRefresh() {
		$tab = array("box_profil", "box_lieu", "box_communaute", "box_evenements", "box_communaute_evenements");
		if ($this->view->nomLieu != null) {
			$tab[] = "box_vue";
		}
		return $tab;
	}

}