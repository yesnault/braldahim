<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Zone extends Zend_Db_Table
{
	protected $_name = 'zone';
	protected $_primary = 'id_zone';

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
			->from('environnement', '*')
			->where('x_min_zone <= ?', $x_max)
			->where('x_max_zone >= ?', $x_min)
			->where('y_min_zone <= ?', $y_max)
			->where('y_max_zone >= ?', $y_min)
			->where('z_zone = ?', $z)
			->where('zone.id_fk_environnement_zone = environnement.id_environnement');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdDonjon($idDonjon)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
			->where('id_fk_donjon_zone = ?', $idDonjon);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', '*')
			->from('environnement', '*')
			->where('x_min_zone <= ?', $x)
			->where('x_max_zone >= ?', $x)
			->where('y_min_zone <= ?', $y)
			->where('y_max_zone >= ?', $y)
			->where('z_zone = ?', $z)
			->where('zone.id_fk_environnement_zone = environnement.id_environnement');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function fetchAllAvecEnvironnement($where = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('environnement', '*')
			->from('zone', '*')
			->where('zone.id_fk_environnement_zone = environnement.id_environnement')
			->order('zone.id_zone');
		if ($where != null) {
			$select->where($where);
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function findByIdEnvironnementList($listId, $avecSoule)
	{
		$liste = "";
		$nomChamp = "id_fk_environnement_zone";
		if (count($listId) < 1) {
			$liste = "";
		} else {
			foreach ($listId as $id) {
				if ((int)$id . "" == $id . "") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste . " OR " . $nomChamp . "=" . $id;
					}
				}
			}
		}

		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('zone', '*')
				->where($nomChamp . '=' . $liste);
			if (!$avecSoule) {
				$select->where("est_soule_zone = ?", "non");
			}
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}

	public function countByIdEnvironnement($idEnvironnement)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone', 'count(*) as nombre')
			->where('id_fk_environnement_zone = ?', intval($idEnvironnement));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}