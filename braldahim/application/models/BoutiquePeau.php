<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class BoutiquePeau extends Zend_Db_Table {
	protected $_name = 'boutique_peau';
	protected $_primary = array('id_boutique_peau');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_peau', '*')
		->where('id_fk_lieu_boutique_peau = '.intval($id_lieu));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function countVenteByDate($dateDebut, $dateFin) {
		return $this->countByDate($dateDebut, $dateFin, "vente");
	}

	function countRepriseByDate($dateDebut, $dateFin) {
		return $this->countByDate($dateDebut, $dateFin, "reprise");
	}

	private function countByDate($dateDebut, $dateFin, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_peau', 'SUM(quantite_peau_boutique_peau) as nombre')
		->where('date_achat_boutique_peau >= ?', $dateDebut)
		->where('date_achat_boutique_peau <= ?', $dateFin)
		->where('action_boutique_peau = ?', $type);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}

}
