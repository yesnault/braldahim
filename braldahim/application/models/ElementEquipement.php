<?php

class ElementEquipement extends Zend_Db_Table {
	protected $_name = 'element_equipement';
	protected $_primary = 'id_element_equipement';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_equipement', '*')
		->from('recette_equipements')
		->from('type_equipement')
		->from('type_qualite')
		->from('type_emplacement')
		->where('id_fk_recette_element_equipement = id_recette_equipement')
		->where('id_fk_type_recette_equipement = id_type_equipement')
		->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
		->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
		->where('x_element_equipement <= ?', $x_max)
		->where('x_element_equipement >= ?', $x_min)
		->where('y_element_equipement <= ?', $y_max)
		->where('y_element_equipement >= ?', $y_min)
		->joinLeft('mot_runique','id_fk_mot_runique_element_equipement = id_mot_runique');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
}
