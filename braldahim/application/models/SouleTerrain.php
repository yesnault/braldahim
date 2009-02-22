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
class SouleTerrain extends Zend_Db_Table {
	protected $_name = 'soule_terrain';
	protected $_primary = 'id_soule_terrain';
	
	public function findByIdTerrain($idTerrain) {
		$where = $this->getAdapter()->quoteInto('id_soule_terrain = ?',(int)$idTerrain);
		return $this->fetchRow($where);
	}
	
	public function findByNiveau($niveauTerrain) {
		$where = $this->getAdapter()->quoteInto('niveau_soule_terrain = ?',(int)$niveauTerrain);
		return $this->fetchRow($where);
	}
}