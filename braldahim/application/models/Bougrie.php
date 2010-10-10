<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bougrie extends Zend_Db_Table {

	const MAX_NOTE = 10;

	protected $_name = 'bougrie';
	protected $_primary = array('id_bougrie');

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_bougrie = ?',(int)$id);
		return $this->fetchRow($where);
	}

	function findAleatoire() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bougrie', 'count(*) as nombre');
		$sql = $select->__toString();
		$res = $db->fetchAll($sql);

		if ($res[0]["nombre"] > 0) {
			$de = Bral_Util_De::get_de_specifique(1, $res[0]["nombre"]);
			$de = 27;
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('bougrie', '*')
			->where('id_bougrie = ?', intval($de));
			$sql = $select->__toString();

			$res =  $db->fetchAll($sql);
			return $res[0];
		} else {
			return null;
		}
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bougrie', 'count(*) as nombre')
		->where('id_bougrie = ?',$data["id_bougrie"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 1 && $resultat[0]["nombre"] == 0) { // insert
			$this->insert($data);
		} else { // update
			$dataUpdate['texte_bougrie'] = $data["texte_bougrie"];

			$where = ' id_bougrie = '.$data["id_bougrie"];
			$this->update($dataUpdate, $where);
		}
	}

}
