<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ElementRune extends Zend_Db_Table {
	protected $_name = 'element_rune';
	protected $_primary = 'id_rune_element_rune';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z, $controleButin = false, $listIdsButin = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_element_rune = id_rune')
		->where('id_fk_type_rune = id_type_rune')
		->where('x_element_rune <= ?',$x_max)
		->where('x_element_rune >= ?',$x_min)
		->where('y_element_rune <= ?',$y_max)
		->where('y_element_rune >= ?',$y_min)
		->where('z_element_rune = ?',$z);

		if ($controleButin) {

			if ($listIdsButin != null) {
				foreach($listIdsButin as $id) {
					if ((int) $id."" == $id."") {
						if ($liste == "") {
							$liste = $id;
						} else {
							$liste = $liste." OR ".id_fk_butin_element."=".$id;
						}
					}
				}
				$select->where('id_fk_butin_element_rune is NULL OR id_fk_butin_element_rune = '.$liste);
			} else {
				$select->where('id_fk_butin_element_rune is NULL');
			}
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z, $controleButin = false, $listIdsButin = null) {
		return $this->selectVue($x, $y, $x, $y, $z, $controleButin, $listIdsButin);
	}
}