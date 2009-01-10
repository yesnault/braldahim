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
class Plante extends Zend_Db_Table {
	protected $_name = 'plante';
	protected $_primary = 'id_plante';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', '*')
		->from('type_plante', '*')
		->where('x_plante <= ?',$x_max)
		->where('x_plante >= ?',$x_min)
		->where('y_plante >= ?',$y_min)
		->where('y_plante <= ?',$y_max)
		->where('plante.id_fk_type_plante = type_plante.id_type_plante');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $id_type = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', 'count(*) as nombre')
		->where('x_plante <= ?',$x_max)
		->where('x_plante >= ?',$x_min)
		->where('y_plante >= ?',$y_min)
		->where('y_plante <= ?',$y_max);
		
		if ($id_type != null) {
			$select->where('id_fk_type_plante = ?',$id_type);
		}
		
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', '*')
		->from('type_plante', '*')
		->where('x_plante = ?',$x)
		->where('y_plante = ?',$y)
		->where('plante.id_fk_type_plante = type_plante.id_type_plante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findLaPlusProche($x, $y, $rayon) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('plante', 'id_plante, y_plante, x_plante, id_fk_type_plante, SQRT(((x_plante - '.$x.') * (x_plante - '.$x.')) + ((y_plante - '.$y.') * ( y_plante - '.$y.'))) as distance')
		->from('type_plante', '*')
		->where('x_plante >= ?', $x - $rayon)
		->where('x_plante <= ?', $x + $rayon)
		->where('y_plante >= ?', $y - $rayon)
		->where('y_plante <= ?', $y + $rayon)
		->where('plante.id_fk_type_plante = type_plante.id_type_plante')
		->order('distance ASC');
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
	
	/**
	 * Supprime les plantes qui sont en ville.
	 */
	function deleteInVille() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*');
		
		$sql = $select->__toString();
		$villes = $db->fetchAll($sql);
		
		foreach($villes as $v) {
			$where = " x_plante >= ". $v["x_min_ville"];
			$where .= " AND x_plante <= ". $v["x_max_ville"];
			$where .= " AND y_plante >= ". $v["y_min_ville"];
			$where .= " AND y_plante <= ". $v["y_max_ville"];
			$this->delete($where);
		}
		
	}
}

