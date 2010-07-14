<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Quete.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Contrat extends Zend_Db_Table {
	protected $_name = 'contrat';
	protected $_primary = array('id_contrat');

	function findEnCours() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('contrat', array('count(*) as nombre', 'id_fk_cible_braldun_contrat', 'etat_contrat', 'type_contrat'))
		->where('date_fin_contrat is null')
		->where('etat_contrat like ?', 'en cours')
		->group(array('id_fk_cible_braldun_contrat', 'etat_contrat', 'type_contrat'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
