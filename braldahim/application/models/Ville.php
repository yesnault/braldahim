<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Ville extends Zend_Db_Table
{
	protected $_name = 'ville';
	protected $_primary = 'id_ville';

	const ID_VILLE_CRISSBROUQUE = 1;
	const ID_VILLE_VILLAGERIANE = 2;
	const ID_VILLE_PRISSCROLE = 3;
	const ID_VILLE_BARDUQUE = 4;
	const ID_VILLE_LILOURAQUE = 5;
	const ID_VILLE_MALEACRUDE = 6;
	const ID_VILLE_NIRLOUQUE = 7;
	const ID_VILLE_GORGLOURTE = 8;
	const ID_VILLE_CORNERUQUE = 9;
	const ID_VILLE_FICHETROUSSE = 10;
	const ID_VILLE_KIRLADREME = 11;
	const ID_VILLE_JORLACRUTE = 12;
	const ID_VILLE_SARLUTE = 13;
	const ID_VILLE_CALEARTE = 14;
	const ID_VILLE_KROTRASQUE = 15;

	function selectVue($x_min, $y_min, $x_max, $y_max)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*')
			->from('region', '*')
			->where('x_min_ville <= ?', $x_max)
			->where('x_max_ville >= ?', $x_min)
			->where('y_min_ville <= ?', $y_max)
			->where('y_max_ville >= ?', $y_min)
			->where('ville.id_fk_region_ville = region.id_region');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y)
	{
		return $this->selectVue($x, $y, $x, $y);
	}

	public function findById($id)
	{
		$where = $this->getAdapter()->quoteInto('id_ville = ?', (int)$id);
		return $this->fetchRow($where);
	}

	function findAllWithRegion()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', '*')
			->from('region', '*')
			->where('ville.id_fk_region_ville = region.id_region')
			->order(array('id_region ASC', 'id_ville ASC'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findLaPlusProche($x, $y)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ville', 'nom_ville, id_ville, x_min_ville, x_max_ville, y_min_ville, y_max_ville, SQRT(((x_min_ville - ' . $x . ') * (x_min_ville - ' . $x . ')) + ((y_min_ville - ' . $y . ') * ( y_min_ville - ' . $y . '))) as distance')
			->order('distance ASC');

		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}
}