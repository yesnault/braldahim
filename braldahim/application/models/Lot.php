<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Lot extends Zend_Db_Table {
	protected $_name = 'lot';
	protected $_primary = array('id_lot');

	function findByIdConteneur($idLot) {
		return $this->findByIdLot($idLot);
	}

	function findByIdEchoppe($idEchoppe, $idLot = null, $idBraldunDestinataire = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'))
				->where('id_fk_echoppe_lot = ?', intval($idEchoppe));

		if ($idLot != null) {
			$select->where('id_lot = ?', intval($idLot));
		}

		if ($idBraldunDestinataire != null) {
			$where = 'id_fk_braldun_lot is null OR id_fk_braldun_lot = ' . intval($idBraldunDestinataire);
			$where .= ' OR id_fk_vendeur_braldun_lot = ' . intval($idBraldunDestinataire);
			$select->where($where);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByVentePublique() {
		Zend_Loader::loadClass("TypeLot");
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'));

		$where = 'id_fk_type_lot = ' . TypeLot::ID_TYPE_VENTE_HOTEL . ' OR id_fk_type_lot = ' . TypeLot::ID_TYPE_VENTE_ECHOPPE_TOUS;
		$select->where($where);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByHotel($perime = false, $limite = false) {
		Zend_Loader::loadClass("TypeLot");
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'))
				->where('id_fk_type_lot = ?', TypeLot::ID_TYPE_VENTE_HOTEL)
				->order('id_lot desc');

		if ($perime === true) {
			$dateFin = date('Y-m-d H:i:s');
			$select->where('date_fin_lot <= ?', $dateFin);
		}

		if ($limite != false) {
			$select->limit(5);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdCommunaute($idCommunaute, $idLot = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'))
				->where('id_fk_communaute_lot = ?', intval($idCommunaute));

		if ($idLot != null) {
			$select->where('id_lot = ?', $idLot);
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdLot($idLot, $typeLot = null) {

		$liste = "";
		$nomChamp = "id_lot";

		if (is_array($idLot)) {
			foreach ($idLot as $id) {
				if ((int)$id . "" == $id . "") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste . " OR " . $nomChamp . "=" . $id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'));

		if ($typeLot != null) {
			$select->where('id_fk_type_lot = ?', $typeLot);
		}
		if ($liste != "") {
			$select->where($nomChamp . '=' . $liste);
		} else {
			$select->where('id_lot = ?', intval($idLot));
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', '*')
				->from('braldun as braldun_vendeur', array('braldun_vendeur.nom_braldun as nom_braldun_vendeur', 'braldun_vendeur.prenom_braldun as prenom_braldun_vendeur'))
				->where('braldun_vendeur.id_braldun = id_fk_vendeur_braldun_lot')
				->joinLeft('braldun as braldun_destinataire', 'id_fk_braldun_lot = braldun_destinataire.id_braldun', array('braldun_destinataire.nom_braldun as nom_braldun_destinataire', 'braldun_destinataire.prenom_braldun as prenom_braldun_destinataire'))
				->where('id_fk_braldun_lot = ?', intval($idBraldun));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot', 'count(*) as nombre,
		quantite_peau_lot as quantitePeau, 
		quantite_cuir_lot as quantiteCuir,
		quantite_fourrure_lot as quantiteFourrure,
		quantite_planche_lot as quantitePlanche,
		quantite_rondin_lot as quantiteRondin,
		quantite_castar_lot as quantiteCastar')
				->where('id_lot = ?', $data["id_lot"])
				->group(array('quantitePeau', 'quantiteCuir', 'quantiteFourrure', 'quantitePlanche', 'quantiteRondin', 'quantiteCastar'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			$quantiteRondin = $resultat[0]["quantiteRondin"];
			$quantiteCastar = $resultat[0]["quantiteCastar"];

			if (isset($data["quantite_peau_lot"])) {
				$dataUpdate['quantite_peau_lot'] = $quantitePeau + $data["quantite_peau_lot"];
			}
			if (isset($data['quantite_cuir_lot'])) {
				$dataUpdate['quantite_cuir_lot'] = $quantiteCuir + $data["quantite_cuir_lot"];
			}
			if (isset($data['quantite_fourrure_lot'])) {
				$dataUpdate['quantite_fourrure_lot'] = $quantiteFourrure + $data["quantite_fourrure_lot"];
			}
			if (isset($data['quantite_planche_lot'])) {
				$dataUpdate['quantite_planche_lot'] = $quantitePlanche + $data["quantite_planche_lot"];
			}
			if (isset($data['quantite_rondin_lot'])) {
				$dataUpdate['quantite_rondin_lot'] = $quantiteRondin + $data["quantite_rondin_lot"];
			}
			if (isset($data['quantite_castar_lot'])) {
				$dataUpdate['quantite_castar_lot'] = $quantiteCastar + $data["quantite_castar_lot"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_lot = ' . $data["id_lot"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
