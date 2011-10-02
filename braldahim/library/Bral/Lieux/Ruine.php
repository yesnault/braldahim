<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Ruine extends Bral_Lieux_Lieu
{

	function prepareCommun()
	{

		$this->view->transformerOk = false;

		if ($this->view->idCommunauteLieu != null) {
			throw new Zend_Exception("Bral_Lieux_Ruine: Action invalide");
		}

		if ($this->view->user->id_fk_communaute_braldun == null || $this->view->user->id_fk_rang_communaute_braldun == null) {
			return;
		}

		Zend_Loader::loadClass("Bral_Util_Communaute");
		$this->view->possedeHall = Bral_Util_Communaute::possedeUnHall($this->view->user->id_fk_communaute_braldun);
		if ($this->view->possedeHall) {
			return;
		}

		Zend_Loader::loadClass("RangCommunaute");
		$rangCommunauteTable = new RangCommunaute();
		$rang = $rangCommunauteTable->findRangCreateur($this->view->user->id_fk_communaute_braldun);

		if ($this->view->user->id_fk_rang_communaute_braldun == $rang["id_rang_communaute"]) { // rang 1 : Gestionnaire
			$this->view->transformerOk = true;
		}

	}

	function prepareFormulaire()
	{

	}

	function prepareResultat()
	{

		if ($this->view->transformerOk == false) {
			throw new Zend_Exception("Erreur Bral_Lieux_Ruine, transformer KO");
		}

		$this->view->user->balance_faim_braldun = $this->view->user->balance_faim_braldun - 6;
		$this->majBraldun();

		Zend_Loader::loadClass("Communaute");
		$communauteTable = new Communaute();
		$communautes = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
		$nomCommunauteLieu = "";
		if ($communautes != null && count($communautes) == 1) {
			$communaute = $communautes[0];
			$nomCommunauteLieu = $communaute["nom_communaute"];
		}

		// mise à jour de la table lieu
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('TypeLieu');
		$lieuTable = new Lieu();
		$data = array(
			'id_fk_communaute_lieu' => $this->view->user->id_fk_communaute_braldun,
			'id_fk_type_lieu' => TypeLieu::ID_TYPE_HALL,
			'nom_lieu' => 'Hall de la communauté ' . $nomCommunauteLieu,
		);
		$where = 'id_lieu=' . $this->view->idLieu;
		$lieuTable->update($data, $where);

		// mise à jour de la table communaute
		Zend_Loader::loadClass('Communaute');
		$communauteTable = new Communaute();
		$data = array(
			'x_communaute' => $this->view->user->x_braldun,
			'y_communaute' => $this->view->user->y_braldun,
			'z_communaute' => $this->view->user->z_braldun,
		);
		$where = 'id_communaute=' . $this->view->user->id_fk_communaute_braldun;
		$communauteTable->update($data, $where);
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu"));
	}
}