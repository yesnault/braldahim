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
class BoutiqueBois extends Zend_Db_Table {
	protected $_name = 'boutique_bois';
	protected $_primary = array('id_boutique_bois');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_bois', '*')
		->where('id_fk_lieu_boutique_bois = '.intval($id_lieu));
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
		$select->from('boutique_bois', 'SUM(quantite_rondin_boutique_bois) as nombre')
		->where('date_achat_boutique_bois >= ?', $dateDebut)
		->where('date_achat_boutique_bois <= ?', $dateFin)
		->where('action_boutique_bois = ?', $type);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
	
}
