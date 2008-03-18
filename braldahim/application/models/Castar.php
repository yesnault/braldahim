<?php

class Castar extends Zend_Db_Table {
	protected $_name = 'castar';
	protected $_primary = array('x_castar', 'y_castar');

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('castar', '*')
		->where('x_castar <= ?',$x_max)
		->where('x_castar >= ?',$x_min)
		->where('y_castar <= ?',$y_max)
		->where('y_castar >= ?',$y_min);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('castar', 'count(*) as nombre, nb_castar as quantiteCastars')
		->where('x_castar = '.$data["x_castar"]. ' AND y_castar = '.$data["y_castar"])
		->group(array('quantiteCastars'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteCastars = $resultat[0]["quantiteCastars"];
			
			if (isset($data["nb_castar"])) {
				$dataUpdate['nb_castar'] = $quantiteCastars + $data["nb_castar"];
				if ($dataUpdate['nb_castar'] < 0) {
					$dataUpdate['nb_castar'] = 0;
				}
			}
			if (isset($dataUpdate)) {
				$where = 'x_castar = '.$data["x_castar"]. ' AND y_castar = '.$data["y_castar"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
