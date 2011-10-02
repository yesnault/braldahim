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

	function findAllWithRegion()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_fk_metier_echoppe) as nombre')
			->from('metier', 'nom_masculin_metier')
			->from('region', 'nom_region')
			->where('echoppe.id_fk_metier_echoppe = metier.id_metier')
			->where('region.x_min_region <= echoppe.x_echoppe')
			->where('region.x_max_region >= echoppe.x_echoppe')
			->where('region.y_min_region <= echoppe.y_echoppe')
			->where('region.y_max_region >= echoppe.y_echoppe')
			->group(array('nom_masculin_metier', 'nom_region'));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
