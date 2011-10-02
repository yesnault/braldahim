<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TypeMonstre extends Zend_Db_Table
{
	protected $_name = 'type_monstre';
	protected $_primary = "id_type_monstre";

	const ID_TYPE_DRAGON = 3;
	const ID_TYPE_BALROG = 45;

	public function fetchAllAvecTypeGroupe()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
			->from('type_groupe_monstre', '*')
			->where('type_monstre.id_fk_type_groupe_monstre = type_groupe_monstre.id_type_groupe_monstre')
			->order(array('nom_groupe_monstre ASC', 'nom_type_monstre ASC'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function fetchAllSansGibier()
	{
		Zend_Loader::loadClass("TypeGroupeMonstre");
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
			->where('type_monstre.id_fk_type_groupe_monstre != ?', TypeGroupeMonstre::ID_TYPE_GIBIER);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function fetchAllQuete()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
			->where('est_dans_quete_type_monstre = ?', "oui");
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	public function fetchAllByTypeGroupe($typeGroupeMonstre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_monstre', '*')
			->where('type_monstre.id_fk_type_groupe_monstre = ?', (int)$typeGroupeMonstre);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}


}