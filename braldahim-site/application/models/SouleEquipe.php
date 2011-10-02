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
class SouleEquipe extends Zend_Db_Table
{
	protected $_name = 'soule_equipe';
	protected $_primary = 'id_soule_equipe';


	public function findByIdMatch($idMatch, $ordre = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from('soule_equipe', '*')
			->from('braldun', '*')
			->where('id_fk_match_soule_equipe = ?', (int)$idMatch)
			->where('id_fk_braldun_soule_equipe = id_braldun');

		if ($ordre != null) {
			$select->order($ordre);
		}

		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}
}