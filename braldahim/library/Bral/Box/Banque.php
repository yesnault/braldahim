<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Banque extends Bral_Box_Box
{

	public function getTitreOnglet()
	{
		return 'Banque';
	}

	function getNomInterne()
	{
		return 'box_lieu';
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		if ($this->view->affichageInterne) {
			$this->view->nom_interne = $this->getNomInterne();
			$this->preData();
			$this->data();
			$this->view->pocheNom = 'Tiroir';
			$this->view->pocheNomSysteme = 'Banque';
			$this->view->afficheTabac = false;
			$this->view->nb_castars = $this->view->coffre['nb_castar'];
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render('interface/banque.phtml');
	}

	protected function preData()
	{
		Zend_Loader::loadClass('Lieu');

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		unset($lieuxTable);

		if (count($lieuRowset) <= 0) {
			throw new Zend_Exception('Bral_Box_Banque::nombre de lieux invalide <= 0 !');
		} elseif (count($lieuRowset) > 1) {
			throw new Zend_Exception('Bral_Box_Banque::nombre de lieux invalide > 1 !');
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			unset($lieuRowset);
			$this->view->nomLieu = $lieu['nom_lieu'];
			$this->view->paUtilisationBanque = $lieu['pa_utilisation_type_lieu'];
		}

	}

	protected function data()
	{

		Zend_Loader::loadClass('BraldunsMetiers');
		Zend_Loader::loadClass('Metier');
		Zend_Loader::loadClass('TypePlante');
		Zend_Loader::loadClass('TypePartieplante');

		Zend_Loader::loadClass('Bral_Helper_DetailRune');

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
		unset($braldunsMetiersTable);

		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall(null, 'nom_masculin_metier');
		unset($metiersTable);
		$metiersRowset = $metiersRowset->toArray();
		$tabBraldunMetiers = null;
		$tabMetiers = null;

		foreach ($metiersRowset as $m) {
			if ($this->view->user->sexe_braldun == 'feminin') {
				$nom_metier = $m['nom_feminin_metier'];
			} else {
				$nom_metier = $m['nom_masculin_metier'];
			}

			$possedeMetier = false;
			foreach ($braldunsMetierRowset as $h) {
				if ($h['id_metier'] == $m['id_metier']) {
					$possedeMetier = true;
					break;
				}
			}

			if ($possedeMetier == true) {
				$tabBraldunMetiers[$m['nom_systeme_metier']] = array(
					'id_metier' => $m['id_metier'],
					'nom' => $nom_metier,
					'nom_systeme' => $m['nom_systeme_metier'],
					'a_afficher' => true,
				);
			} else {
				$tabMetiers[$m['nom_systeme_metier']] = array(
					'id_metier' => $m['id_metier'],
					'nom' => $m['nom_masculin_metier'],
					'nom_systeme' => $m['nom_systeme_metier'],
					'a_afficher' => false,
				);
			}
		}

		Zend_Loader::loadClass('Bral_Util_Coffre');
		// passage par reference de tabMetiers et this->view
		Bral_Util_Coffre::prepareData($tabMetiers, $this->view, $this->view->user->id_braldun, null);

		$this->view->tabMetiers = $tabMetiers;
		$this->view->tabBraldunMetiers = $tabBraldunMetiers;

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;

		$this->view->nom_interne = $this->getNomInterne();
	}

}
