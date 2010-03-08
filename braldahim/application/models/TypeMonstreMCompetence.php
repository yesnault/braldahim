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
class TypeMonstreMCompetence extends Zend_Db_Table {
	protected $_name = 'type_monstre_mcompetence';
	protected $_primary = array('id_fk_type_monstre_mcompetence', 'id_fk_mcompetence_type_monstre_mcompetence');

	public function findFuiteByIdTypeGroupe($idTypeMonstre) {
		return $this->findByIdTypeGroupe($idTypeMonstre, "fuite");
	}

	public function findPreReperageByIdTypeGroupe($idTypeMonstre) {
		return $this->findByIdTypeGroupe($idTypeMonstre, "prereperage");
	}

	public function findReperageByIdTypeGroupe($idTypeMonstre) {
		return $this->findByIdTypeGroupe($idTypeMonstre, "reperage");
	}

	public function findAttaqueByIdTypeGroupe($idTypeMonstre) {
		return $this->findByIdTypeGroupe($idTypeMonstre, "attaque");
	}

	public function findPostAllByIdTypeGroupe($idTypeMonstre) {
		return $this->findByIdTypeGroupe($idTypeMonstre, "end");
	}

	private function findByIdTypeGroupe($idTypeMonstre, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre_mcompetence', '*')
		->from('mcompetence', '*')
		->where('type_monstre_mcompetence.id_fk_mcompetence_type_monstre_mcompetence = mcompetence.id_mcompetence')
		->where('id_fk_type_monstre_mcompetence = ?', intval($idTypeMonstre))
		->where('type_mcompetence = ?', $type)
		->order('ordre_type_monstre_mcompetence');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}