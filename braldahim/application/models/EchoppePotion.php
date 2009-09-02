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
class EchoppePotion extends Zend_Db_Table {
	protected $_name = 'echoppe_potion';
	protected $_primary = "id_echoppe_potion";

	public function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->from('potion')
		->where('id_echoppe_potion = id_potion')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite')
		->where('id_fk_echoppe_echoppe_potion = ?', $idEchoppe)
		->order(array('type_potion ASC', 'id_echoppe_potion ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
