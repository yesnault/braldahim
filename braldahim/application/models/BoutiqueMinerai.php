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
class BoutiqueMinerai extends Zend_Db_Table {
	protected $_name = 'boutique_minerai';
	protected $_primary = array('id_boutique_minerai');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_lieu_boutique_minerai = '.intval($id_lieu))
		->where('boutique_minerai.id_fk_type_boutique_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function countVenteByDate($dateDebut, $dateFin, $idTypeMinerai) {
		return $this->countByDate($dateDebut, $dateFin, $idTypeMinerai, "vente");
	}
	
	function countRepriseByDate($dateDebut, $dateFin, $idTypeMinerai) {
		return $this->countByDate($dateDebut, $dateFin, $idTypeMinerai, "reprise");
	}
	
	private function countByDate($dateDebut, $dateFin, $idTypeMinerai, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_minerai', 'SUM(quantite_brut_boutique_minerai) as nombre')
		->where('date_achat_boutique_minerai >= ?', $dateDebut)
		->where('date_achat_boutique_minerai <= ?', $dateFin)
		->where('action_boutique_minerai = ?', $type)
		->where('id_fk_type_boutique_minerai = ?', $idTypeMinerai);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
}
