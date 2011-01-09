<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeMinerai extends Zend_Db_Table {
	protected $_name = 'echoppe_minerai';
	protected $_primary = array('id_fk_echoppe_echoppe_minerai', 'id_fk_type_echoppe_minerai');

	function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_echoppe_echoppe_minerai = ?', intval($idEchoppe))
		->where('echoppe_minerai.id_fk_type_echoppe_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_minerai', 
		'quantite_brut_arriere_echoppe_minerai as quantiteArriere'
		.', quantite_lingots_echoppe_minerai as quantiteLingots')
		->where('id_fk_type_echoppe_minerai = ?',$data["id_fk_type_echoppe_minerai"])
		->where('id_fk_echoppe_echoppe_minerai = ?',$data["id_fk_echoppe_echoppe_minerai"])
		->group(array('quantiteArriere', 'quantiteLingots'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			$quantiteLingots = $resultat[0]["quantiteLingots"];
			
			if (isset($data["quantite_brut_arriere_echoppe_minerai"])) {
				$quantiteArriere = $quantiteArriere + $data["quantite_brut_arriere_echoppe_minerai"];
			}
			if (isset($data["quantite_lingots_echoppe_minerai"])) {
				$quantiteLingots = $quantiteLingots + $data["quantite_lingots_echoppe_minerai"];
			}
			
			if ($quantiteArriere < 0) $quantiteArriere = 0;
			if ($quantiteLingots < 0) $quantiteLingots = 0;
			
			$dataUpdate = array(
			'quantite_brut_arriere_echoppe_minerai' => $quantiteArriere,
			'quantite_lingots_echoppe_minerai' => $quantiteLingots,
			);
			$where = ' id_fk_type_echoppe_minerai = '.$data["id_fk_type_echoppe_minerai"];
			$where .= ' AND id_fk_echoppe_echoppe_minerai = '.$data["id_fk_echoppe_echoppe_minerai"];
			
			if ($quantiteArriere == 0 && $quantiteLingots == 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
			
			
		}
	}

}
