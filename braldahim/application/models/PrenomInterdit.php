<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class PrenomInterdit extends Zend_Db_Table {
	protected $_name = 'prenom_interdit';
	protected $_primary = array('id_prenom_interdit');

	public function countByPrenom($prenom){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('prenom_interdit', 'count(*) as nombre')
		->where('lcase(texte_prenom_interdit) like ?', (string)mb_strtolower(trim($prenom)));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
