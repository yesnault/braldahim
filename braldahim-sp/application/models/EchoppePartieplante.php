<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppePartieplante.php 1767 2009-06-22 18:05:02Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-06-22 20:05:02 +0200 (Lun, 22 jui 2009) $
 * $LastChangedRevision: 1767 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppePartieplante extends Zend_Db_Table {
	protected $_name = 'echoppe_partieplante';
	protected $_primary = array('id_fk_type_echoppe_partieplante', 'id_echoppe_echoppe_partieplantefk_fk_');
	
    function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_echoppe_echoppe_partieplante = ?', intval($idEchoppe))
		->where('echoppe_partieplante.id_fk_type_echoppe_partieplante = type_partieplante.id_type_partieplante')
		->where('echoppe_partieplante.id_fk_type_plante_echoppe_partieplante = type_plante.id_type_plante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}
