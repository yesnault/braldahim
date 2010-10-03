<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RecetteCoutMinerai extends Zend_Db_Table {
	protected $_name = 'recette_cout_minerai';
	protected $_primary = array('id_fk_type_equipement_recette_cout_minerai',
								'id_fk_type_recette_cout_minerai',
								'niveau_recette_cout_minerai');

	function findByIdTypeEquipement($id_type_equipement) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_type_equipement_recette_cout_minerai = '.intval($id_type_equipement))
		->where('recette_cout_minerai.id_fk_type_recette_cout_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function findByIdTypeEquipementAndNiveau($id_type_equipement, $niveau) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_type_equipement_recette_cout_minerai = '.intval($id_type_equipement))
		->where('niveau_recette_cout_minerai = '.intval($niveau))
		->where('recette_cout_minerai.id_fk_type_recette_cout_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_cout_minerai', 'count(*) as nombre, quantite_recette_cout_minerai as quantite')
		->where('id_fk_type_recette_cout_minerai = ?',$data["id_fk_type_recette_cout_minerai"])
		->where('niveau_recette_cout_minerai = ?',$data["niveau_recette_cout_minerai"])
		->where('id_fk_type_equipement_recette_cout_minerai = ?',$data["id_fk_type_equipement_recette_cout_minerai"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate = array('quantite_recette_cout_minerai' => $quantite + $data["quantite_recette_cout_minerai"]);
			$where = ' id_fk_type_recette_cout_minerai = '.$data["id_fk_type_recette_cout_minerai"];
			$where = ' niveau_recette_cout_minerai = '.$data["niveau_recette_cout_minerai"];
			$where .= ' AND id_fk_type_equipement_recette_cout_minerai = '.$data["id_fk_type_equipement_recette_cout_minerai"];
			$this->update($dataUpdate, $where);
		}
	}

}
