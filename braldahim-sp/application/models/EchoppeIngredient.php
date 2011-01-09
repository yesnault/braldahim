<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppeIngredient.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppeIngredient extends Zend_Db_Table {
	protected $_name = 'echoppe_ingredient';
	protected $_primary = array('id_fk_echoppe_echoppe_ingredient', 'id_fk_type_echoppe_ingredient');

	function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_echoppe_echoppe_ingredient = ?', intval($idEchoppe))
		->where('echoppe_ingredient.id_fk_type_echoppe_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
