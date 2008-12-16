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
class Lieu extends Zend_Db_Table {
	protected $_name = 'lieu';
	protected $_primary = 'id_lieu';

	public function findByType($type){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findByTypeAndRegion($type, $region){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->from('region', '*')
		->where('region.id_region = ?', $region)
		->where('lieu.id_fk_type_lieu = ?', $type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville')
		->where('lieu.x_lieu >= region.x_min_region')
		->where('lieu.x_lieu <= region.x_max_region')
		->where('lieu.y_lieu >= region.y_min_region')
		->where('lieu.y_lieu <= region.y_max_region');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->where('x_lieu <= ?',$x_max)
		->where('x_lieu >= ?',$x_min)
		->where('y_lieu >= ?',$y_min)
		->where('y_lieu <= ?',$y_max)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('x_lieu = ?',$x)
		->where('y_lieu = ?',$y)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	public function findByTypeAndPosition($type, $x, $y){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lieu', '*, SQRT(((x_lieu - '.$x.') * (x_lieu - '.$x.')) + ((y_lieu - '.$y.') * ( y_lieu - '.$y.'))) as distance')
		->from('type_lieu', '*')
		->from('ville', '*')
		->where('lieu.id_fk_type_lieu = ?',$type)
		->where('lieu.id_fk_type_lieu = type_lieu.id_type_lieu')
		->where('lieu.id_fk_ville_lieu = ville.id_ville')
		->order(array('distance ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}