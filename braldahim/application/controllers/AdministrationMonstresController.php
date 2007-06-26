<?php

class AdministrationMonstresController extends Zend_Controller_Action {

	function init() {
		/** TODO a completer */

		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();

		Zend_Loader::loadClass('ReferentielMonstre');
		Zend_Loader::loadClass('TailleMonstre');
		Zend_Loader::loadClass('TypeMonstre');

		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
	}

	function indexAction() {
		$this->render();
	}

	function referentielAction() {
		
		$modifier = false;
		$nomAction = '';
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());

			$id_fk_type_ref_monstre = $filter->filter($this->_request->getPost('id_type'));
			$id_fk_taille_ref_monstre = $filter->filter($this->_request->getPost('id_taille'));
			$niveau_min_ref_monstre = $filter->filter($this->_request->getPost('niveau_min'));
			$niveau_max_ref_monstre = $filter->filter($this->_request->getPost('niveau_max'));
			$pourcentage_force_ref_monstre = $filter->filter($this->_request->getPost('p_force'));
			$pourcentage_sagesse_ref_monstre = $filter->filter($this->_request->getPost('p_sagesse'));
			$pourcentage_vigueur_ref_monstre = $filter->filter($this->_request->getPost('p_vigueur'));
			$pourcentage_agilite_ref_monstre = $filter->filter($this->_request->getPost('p_agilite'));
			$vue_ref_monstre = $filter->filter($this->_request->getPost('vue'));

			$data = array(
			"id_fk_type_ref_monstre" => $id_fk_type_ref_monstre,
			"id_fk_taille_ref_monstre" => $id_fk_taille_ref_monstre,
			"niveau_min_ref_monstre" => $niveau_min_ref_monstre,
			"niveau_max_ref_monstre" => $niveau_max_ref_monstre,
			"pourcentage_vigueur_ref_monstre" => $pourcentage_vigueur_ref_monstre,
			"pourcentage_agilite_ref_monstre" => $pourcentage_agilite_ref_monstre,
			"pourcentage_sagesse_ref_monstre" => $pourcentage_sagesse_ref_monstre,
			"pourcentage_force_ref_monstre" => $pourcentage_force_ref_monstre,
			"vue_ref_monstre" => $vue_ref_monstre, 
			);

			$refTable = new ReferentielMonstre();
			if ($this->_request->getParam('update', 0) != 0) {
				// Mise à jour
				$where = "id_ref_monstre=".(int)$this->_request->getParam('update', 0);
				$refTable->update($data, $where);
			} else {
				// Insertion
				$refTable = new ReferentielMonstre();
				$refTable->insert($data);
			}
		} else if ($this->_request->getParam('modifier', 0) != 0) {
			$modifier = true;
			$nomAction = 'update/'.$this->_request->getParam('modifier');
		}
		$this->referentielPrepare();
		$this->view->modifier = $modifier;
		$this->view->nomAction = $nomAction;
		$this->render();
	}

	private function referentielPrepare() {
		$ref = null;
		$tailles = null;
		$types = null;
		$referenceCourante = array(
		"id_ref_monstre" =>'',
		"id_type_monstre" => '',
		"id_taille_monstre" => '',
		"niveau_min" => '',
		"niveau_max" => '',
		"p_force" => '',
		"p_sagesse" => '',
		"p_vigueur" => '',
		"p_agilite" => '',
		"vue" => ''
		);

		$refTable = new ReferentielMonstre();
		$taillesTable = new TailleMonstre();
		$typesTable = new TypeMonstre();

		$refRowset = $refTable->findAll();
		$taillesRowset = $taillesTable->fetchall();
		$typesRowset = $typesTable->fetchall();

		foreach($refRowset as $r) {
			if ($r["genre_type_monstre"] == 'feminin') {
				$m_taille = $r["nom_taille_f_monstre"];
			} else {
				$m_taille = $r["nom_taille_m_monstre"];
			}
			$ref[] = array(
			"id_ref_monstre" => $r["id_ref_monstre"],
			"nom_type" => $r["nom_type_monstre"],
			"taille" => $m_taille,
			"niveau_min" => $r["niveau_min_ref_monstre"],
			"niveau_max" => $r["niveau_max_ref_monstre"],
			"p_force" => $r["pourcentage_force_ref_monstre"],
			"p_sagesse" => $r["pourcentage_sagesse_ref_monstre"],
			"p_vigueur" => $r["pourcentage_vigueur_ref_monstre"],
			"p_agilite" => $r["pourcentage_agilite_ref_monstre"],
			"vue" => $r["vue_ref_monstre"]
			);

			// si l'on veut modifier une reference, on prepare l'objet
			if ($this->_request->getParam('modifier', 0) == $r["id_ref_monstre"]) {
				$referenceCourante = array(
				"id_ref_monstre" => $r["id_ref_monstre"],
				"id_type_monstre" => $r["id_fk_type_ref_monstre"],
				"id_taille_monstre" => $r["id_fk_taille_ref_monstre"],
				"niveau_min" => $r["niveau_min_ref_monstre"],
				"niveau_max" => $r["niveau_max_ref_monstre"],
				"p_force" => $r["pourcentage_force_ref_monstre"],
				"p_sagesse" => $r["pourcentage_sagesse_ref_monstre"],
				"p_vigueur" => $r["pourcentage_vigueur_ref_monstre"],
				"p_agilite" => $r["pourcentage_agilite_ref_monstre"],
				"vue" => $r["vue_ref_monstre"]
				);
			}
		}

		foreach($taillesRowset as $t) {
			$tailles[] = array(
			"id_taille_monstre" => $t->id_taille_monstre,
			"nom_feminin" => $t->nom_taille_f_monstre,
			"nom_masculin" => $t->nom_taille_m_monstre
			);
		}

		foreach($typesRowset as $t) {
			$types[] = array(
			"id_type_monstre" => $t->id_type_monstre,
			"nom_type" => $t->nom_type_monstre,
			);
		}

		$this->view->refMonstre = $ref;
		$this->view->taillesMonstre = $tailles;
		$this->view->typesMonstre = $types;
		$this->view->referenceCourante = $referenceCourante;
	}
}
