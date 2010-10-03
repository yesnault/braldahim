<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Echoppe extends Zend_Db_Table {
	protected $_name = 'echoppe';
	protected $_primary = "id_echoppe";

	function countAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function countVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(id_echoppe) as nombre')
		->where('x_echoppe <= ?',$x_max)
		->where('x_echoppe >= ?',$x_min)
		->where('y_echoppe >= ?',$y_min)
		->where('y_echoppe <= ?',$y_max)
		->where('z_echoppe = ?',$z);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('metier', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'sexe_braldun', 'id_braldun'))
		->where('x_echoppe <= ?',$x_max)
		->where('x_echoppe >= ?',$x_min)
		->where('y_echoppe >= ?',$y_min)
		->where('y_echoppe <= ?',$y_max)
		->where('z_echoppe = ?',$z)
		->where('braldun.id_braldun = echoppe.id_fk_braldun_echoppe' )
		->where('echoppe.id_fk_metier_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByCase($x, $y, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('metier', '*')
		->from('braldun', '*')
		->from('region', '*')
		->where('x_echoppe = ?',$x)
		->where('y_echoppe = ?',$y)
		->where('z_echoppe = ?',$z)
		->where('echoppe.id_fk_metier_echoppe = metier.id_metier')
		->where('id_fk_braldun_echoppe = id_braldun')
		->where('region.x_min_region <= echoppe.x_echoppe')
		->where('region.x_max_region >= echoppe.x_echoppe')
		->where('region.y_min_region <= echoppe.y_echoppe')
		->where('region.y_max_region >= echoppe.y_echoppe');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('metier', '*')
		->from('region', '*')
		->where('id_fk_braldun_echoppe = ?', $id_braldun)
		->where('echoppe.id_fk_metier_echoppe = metier.id_metier')
		->where('region.x_min_region <= echoppe.x_echoppe')
		->where('region.x_max_region >= echoppe.x_echoppe')
		->where('region.y_min_region <= echoppe.y_echoppe')
		->where('region.y_max_region >= echoppe.y_echoppe');
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', '*')
		->from('metier', '*')
		->where('id_echoppe = ?', $id)
		->where('echoppe.id_fk_metier_echoppe = metier.id_metier');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe', 'count(*) as nombre, 
		quantite_rondin_caisse_echoppe as quantiteRondinCaisse, 
		quantite_castar_caisse_echoppe as quantiteCastarCaisse, 
		quantite_peau_caisse_echoppe as quantitePeauCaisse, 
		quantite_rondin_arriere_echoppe as quantiteRondinArriere, 
		quantite_planche_arriere_echoppe as quantitePlancheArriere, 
		quantite_peau_arriere_echoppe as quantitePeauArriere, 
		quantite_cuir_arriere_echoppe as quantiteCuirArriere,
		quantite_fourrure_arriere_echoppe as quantiteFourrureArriere')
		->where('id_echoppe = ?',$data["id_echoppe"])
		->group(array('quantiteRondinArriere', 'quantitePlancheArriere', 'quantitePeauArriere', 'quantiteCuirArriere', 'quantiteFourrureArriere'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteRondinArriere = $resultat[0]["quantiteRondinArriere"];
			$quantitePlancheArriere = $resultat[0]["quantitePlancheArriere"];
			$quantitePeauArriere = $resultat[0]["quantitePeauArriere"];
			$quantiteCuirArriere = $resultat[0]["quantiteCuirArriere"];
			$quantiteFourrureArriere = $resultat[0]["quantiteFourrureArriere"];
			
			$quantiteRondinCaisse = $resultat[0]["quantiteRondinCaisse"];
			$quantiteCastarCaisse = $resultat[0]["quantiteCastarCaisse"];
			$quantitePeauCaisse = $resultat[0]["quantitePeauCaisse"];
			
			$dataUpdate = null;
			
			if (isset($data["quantite_rondin_caisse_echoppe"])) {
				$dataUpdate['quantite_rondin_caisse_echoppe'] = $quantiteRondinCaisse + $data["quantite_rondin_caisse_echoppe"];
			}
			if (isset($data["quantite_castar_caisse_echoppe"])) {
				$dataUpdate['quantite_castar_caisse_echoppe'] = $quantiteCastarCaisse + $data["quantite_castar_caisse_echoppe"];
			}
			if (isset($data["quantite_peau_caisse_echoppe"])) {
				$dataUpdate['quantite_peau_caisse_echoppe'] = $quantitePeauCaisse + $data["quantite_peau_caisse_echoppe"];
			}
			if (isset($data["quantite_rondin_arriere_echoppe"])) {
				$dataUpdate['quantite_rondin_arriere_echoppe'] = $quantiteRondinArriere + $data["quantite_rondin_arriere_echoppe"];
			}
			if (isset($data["quantite_planche_arriere_echoppe"])) {
				$dataUpdate['quantite_planche_arriere_echoppe'] = $quantitePlancheArriere + $data["quantite_planche_arriere_echoppe"];
			}
			if (isset($data["quantite_peau_arriere_echoppe"])) {
				$dataUpdate['quantite_peau_arriere_echoppe'] = $quantitePeauArriere + $data["quantite_peau_arriere_echoppe"];
			}
			if (isset($data["quantite_cuir_arriere_echoppe"])) {
				$dataUpdate['quantite_cuir_arriere_echoppe'] = $quantiteCuirArriere + $data["quantite_cuir_arriere_echoppe"];
			}
			if (isset($data["quantite_fourrure_arriere_echoppe"])) {
				$dataUpdate['quantite_fourrure_arriere_echoppe'] = $quantiteFourrureArriere + $data["quantite_fourrure_arriere_echoppe"];
			}

			
			if ($dataUpdate != null) {
				$where = 'id_echoppe = '.$data["id_echoppe"];
				$this->update($dataUpdate, $where);
			}
		}
	}
}
