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
class Echoppe extends Zend_Db_Table
{
	protected $_name = 'echoppe';
	protected $_primary = "id_echoppe";

	function countAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre')
			->where('x_echoppe <= ?', $x_max)
			->where('x_echoppe >= ?', $x_min)
			->where('y_echoppe >= ?', $y_min)
			->where('y_echoppe <= ?', $y_max)
			->where('z_echoppe = ?', $z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
			->from('metier', '*')
			->from('braldun', array('nom_braldun', 'prenom_braldun', 'sexe_braldun', 'id_braldun'))
			->where('x_echoppe <= ?', $x_max)
			->where('x_echoppe >= ?', $x_min)
			->where('y_echoppe >= ?', $y_min)
			->where('y_echoppe <= ?', $y_max)
			->where('z_echoppe = ?', $z)
			->where('braldun.id_braldun = echoppe.id_fk_braldun_echoppe')
			->where('echoppe.id_fk_metier_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
			->from('metier', '*')
			->from('braldun', '*')
			->from('region', '*')
			->where('x_echoppe = ?', $x)
			->where('y_echoppe = ?', $y)
			->where('z_echoppe = ?', $z)
			->where('echoppe.id_fk_metier_echoppe = metier.id_metier')
			->where('id_fk_braldun_echoppe = id_braldun')
			->where('region.x_min_region <= echoppe.x_echoppe')
			->where('region.x_max_region >= echoppe.x_echoppe')
			->where('region.y_min_region <= echoppe.y_echoppe')
			->where('region.y_max_region >= echoppe.y_echoppe');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldun($id_braldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
			->from('metier', '*')
			->from('region', '*')
			->where('id_fk_braldun_echoppe = ?', $id_braldun)
			->where('echoppe.id_fk_metier_echoppe = metier.id_metier')
			->where('region.x_min_region <= echoppe.x_echoppe')
			->where('region.x_max_region >= echoppe.x_echoppe')
			->where('region.y_min_region <= echoppe.y_echoppe')
			->where('region.y_max_region >= echoppe.y_echoppe');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findById($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
			->from('metier', '*')
			->where('id_echoppe = ?', $id)
			->where('echoppe.id_fk_metier_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
