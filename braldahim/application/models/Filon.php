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
class Filon extends Zend_Db_Table {
	protected $_name = 'filon';
	protected $_primary = 'id_filon';

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', '*')
		->from('type_minerai', '*')
		->where('x_filon <= ?',$x_max)
		->where('x_filon >= ?',$x_min)
		->where('y_filon >= ?',$y_min)
		->where('y_filon <= ?',$y_max)
		->where('filon.id_fk_type_minerai_filon = type_minerai.id_type_minerai');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function countVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', 'count(*) as nombre')
		->where('x_filon <= ?',$x_max)
		->where('x_filon >= ?',$x_min)
		->where('y_filon >= ?',$y_min)
		->where('y_filon <= ?',$y_max);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
	
	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', '*')
		->from('type_minerai', '*')
		->where('x_filon = ?',$x)
		->where('y_filon = ?',$y)
		->where('filon.id_fk_type_minerai_filon = type_minerai.id_type_minerai')
		->order('filon.id_filon');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findLePlusProche($x, $y, $rayon) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filon', 'id_filon, x_filon, y_filon, id_fk_type_minerai_filon, SQRT(((x_filon - '.$x.') * (x_filon - '.$x.')) + ((y_filon - '.$y.') * ( y_filon - '.$y.'))) as distance')
		->from('type_minerai', '*')
		->where('x_filon >= ?', $x - $rayon)
		->where('x_filon <= ?', $x + $rayon)
		->where('y_filon >= ?', $y - $rayon)
		->where('y_filon <= ?', $y + $rayon)
		->where('filon.id_fk_type_minerai_filon = type_minerai.id_type_minerai')
		->order('distance ASC');
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
	
	/**
	 * Supprime les filons qui sont en ville.
	 */
	function deleteInVille() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*');
		
		$sql = $select->__toString();
		$villes = $db->fetchAll($sql);
		
		foreach($villes as $v) {
			$where = " x_filon >= ". $v["x_min_ville"];
			$where .= " AND x_filon <= ". $v["x_max_ville"];
			$where .= " AND y_filon >= ". $v["y_min_ville"];
			$where .= " AND y_filon <= ". $v["y_max_ville"];
			$this->delete($where);
		}
		
	}
}
