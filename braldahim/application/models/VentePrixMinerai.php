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
class VentePrixMinerai extends Zend_Db_Table {
	protected $_name = 'vente_prix_minerai';
	protected $_primary = array("id_fk_type_vente_prix_minerai","id_fk_vente_prix_minerai");
	
    function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_vente_prix_minerai', (int)$idVente)
		->where('vente_prix_minerai.id_fk_type_vente_prix_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}
