<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}

	function biereDuMilieuAction() {

		return;

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");

		Zend_Loader::loadClass("ElementAliment");
		$elementAlimentTable = new ElementAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		Zend_Loader::loadClass("LabanAliment");
		$labanTable = new LabanAliment();

		Zend_Loader::loadClass("TypeAliment");
		Zend_Loader::loadClass("Bral_Util_Effets");
			
		foreach ($bralduns as $h) {

			$idAliment = $idsAliment->prepareNext();

			$idEffetBraldun = null;

			$idTypeAliment = TypeAliment::ID_TYPE_JOUR_MILIEU;
			$idEffetBraldun = Bral_Util_Effets::ajouteEtAppliqueEffetBraldun(null, Bral_Util_Effets::CARACT_BBDF, Bral_Util_Effets::TYPE_BONUS, 100, 5, 'Je bois, je mincis et ça se voit. Ah non ... tant pis !');

			$data = array(
				"id_aliment" => $idAliment,
				"id_fk_type_aliment" => $idTypeAliment,
				"id_fk_type_qualite_aliment" => 2,
				"bbdf_aliment" => 0,
				"id_fk_effet_braldun_aliment" => $idEffetBraldun,
			);
			$alimentTable->insert($data);

			$data = null;
			$data["id_fk_braldun_laban_aliment"] = $h["id_braldun"];
			$data['id_laban_aliment'] = $idAliment;
			$labanTable->insert($data);

			$data = null;
			$data["balance_faim_braldun"] = 100;
			$where = "id_braldun=".$h["id_braldun"];
			$braldunTable->update($data, $where);

		}

		//$this->message();
	}

	private function message() {
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		foreach ($bralduns as $h) {
			$detailsBot = "Oyez Braldûns !".PHP_EOL.PHP_EOL."C'est aujourd'hui la fête du jour du milieu !";
			$detailsBot .= PHP_EOL."Je vous invite à boire un coup pour fêter la moitié de l'année.".PHP_EOL.PHP_EOL;
			$detailsBot .= "Jetez un oeil à votre laban je crois qu'il y a une surprise !".PHP_EOL.PHP_EOL;
			$detailsBot .= "A la votre,";

			$message = $detailsBot.PHP_EOL.PHP_EOL." Huguette Ptipieds".PHP_EOL."Inutile de répondre à ce message.";

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->huguette->id_braldun, $h["id_braldun"], $message, $this->view);
		}
			
	}

	function md5Action() {
		$this->render();
	}

}
