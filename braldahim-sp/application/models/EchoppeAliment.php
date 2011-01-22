<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppeAliment.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppeAliment extends Zend_Db_Table {
	protected $_name = 'echoppe_aliment';
	protected $_primary = "id_echoppe_aliment";

	public function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_aliment', '*')
		->from('type_aliment')
		->from('aliment')
		->from('type_qualite')
		->where('id_echoppe_aliment = id_aliment')
		->where('id_fk_type_aliment = id_type_aliment')
		->where('id_fk_type_qualite_aliment = id_type_qualite')
		->where('id_fk_echoppe_echoppe_aliment = ?', $idEchoppe)
		->order(array('type_bbdf_type_aliment ASC', 'nom_type_aliment ASC', 'id_type_qualite ASC', 'bbdf_aliment ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}