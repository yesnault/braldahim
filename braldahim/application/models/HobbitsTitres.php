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
class HobbitsTitres extends Zend_Db_Table {
    protected $_name = 'hobbits_titres';
	protected $_primary = array('id_fk_hobbit_htitre', 'id_fk_type_htitre', 'niveau_acquis_htitre');
	
    function findTitresByHobbitId($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbits_titres', '*')
		->from('type_titre', '*')
		->where('hobbits_titres.id_fk_hobbit_htitre = '.intval($idHobbit))
		->where('hobbits_titres.id_fk_type_htitre = type_titre.id_type_titre')
		->order('hobbits_titres.niveau_acquis_htitre');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}