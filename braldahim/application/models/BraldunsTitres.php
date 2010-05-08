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
class BraldunsTitres extends Zend_Db_Table {
    protected $_name = 'bralduns_titres';
	protected $_primary = array('id_fk_braldun_htitre', 'id_fk_type_htitre', 'niveau_acquis_htitre');
	
    function findTitresByBraldunId($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_titres', '*')
		->from('type_titre', '*')
		->where('bralduns_titres.id_fk_braldun_htitre = '.intval($idBraldun))
		->where('bralduns_titres.id_fk_type_htitre = type_titre.id_type_titre')
		->order('bralduns_titres.niveau_acquis_htitre');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
}