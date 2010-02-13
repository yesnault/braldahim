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
class TypeGroupeMonstre extends Zend_Db_Table {
	protected $_name = 'type_groupe_monstre';
	protected $_primary = "id_type_groupe_monstre";
	
	const ID_TYPE_SOLITAIRE = 1;
	const ID_TYPE_NUEE = 2;
	const ID_TYPE_MEUTE = 3;
	const ID_TYPE_BANDE = 4;
	const ID_TYPE_GIBIER = 5;
	const ID_TYPE_BOSS = 6;
	
	public static function getStaticTypes() {
		$tabTypeGroupe[self::ID_TYPE_SOLITAIRE] = "Solitaire";
		$tabTypeGroupe[self::ID_TYPE_NUEE] = "Nuée";
		$tabTypeGroupe[self::ID_TYPE_MEUTE] = "Meute";
		$tabTypeGroupe[self::ID_TYPE_BANDE] = "Bande";
		$tabTypeGroupe[self::ID_TYPE_GIBIER] = "Gibier";
		$tabTypeGroupe[self::ID_TYPE_BOSS] = "Boss";
		return $tabTypeGroupe;
	}
}
