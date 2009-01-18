<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Hobbit.php 1000 2009-01-15 20:26:10Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-15 21:26:10 +0100 (Thu, 15 Jan 2009) $
 * $LastChangedRevision: 1000 $
 * $LastChangedBy: yvonnickesnault $
 */
class Hobbit extends Zend_Db_Table {
	protected $_name = 'hobbit';
	protected $_primary = 'id_hobbit';

	function findAllByDate($dateDebut, $dateFin, $page, $nbMax) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array("nom_hobbit", "prenom_hobbit", "id_hobbit", "date_creation_hobbit"));
		$select->where('date_creation_hobbit >= ?', $dateDebut);
		$select->where('date_creation_hobbit <= ?', $dateFin);
		$select->where('est_compte_actif_hobbit = ?', 'oui');
		$select->order("date_creation_hobbit DESC");
		$select->limitPage($page, $nbMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}