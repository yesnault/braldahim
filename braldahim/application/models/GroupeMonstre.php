<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class GroupeMonstre extends Zend_Db_Table
{
	protected $_name = 'groupe_monstre';
	protected $_primary = "id_groupe_monstre";

	function findGroupesAJouer($aJouerFlag, $nombreMax, $idTypeGroupe)
	{
		$aJouer = "";
		if ($aJouerFlag == true) {
			$aJouer = " AND date_a_jouer_groupe_monstre <= '" . date("Y-m-d H:i:s") . "'";
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('groupe_monstre', '*')
			->from('type_groupe_monstre', '*')
			->where('groupe_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre')
			->where('groupe_monstre.id_fk_type_groupe_monstre = ' . $idTypeGroupe . $aJouer)
			->order('date_fin_tour_groupe_monstre ASC')
			->limitPage(0, $nombreMax);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('groupe_monstre', 'count(id_groupe_monstre) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

}
