<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Competence extends Zend_Db_Table {
	protected $_name = 'competence';
	protected $_primary = 'id_competence';
	protected $_dependentTables = array('hobbits_competences');

	public function findBasiques(){
		$where = $this->getAdapter()->quoteInto("type_competence = ?", "basic");
		return $this->fetchAll($where);
	}
	
	public function findCommunesInscription($niveau){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('competence', '*')
		->where('type_competence = ?', "commun")
		->where('niveau_requis_competence = 0');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findCommunesByNiveau($niveau){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('competence', '*')
		->where('type_competence = ?', "commun")
		->where('niveau_requis_competence <= ?', $niveau);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	public function findByIdMetier($idMetier){
		$where = $this->getAdapter()->quoteInto("id_fk_metier_competence = ?", $idMetier);
		return $this->fetchAll($where);
	}
}