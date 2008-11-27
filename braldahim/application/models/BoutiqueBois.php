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
class BoutiqueBois extends Zend_Db_Table {
	protected $_name = 'boutique_bois';
	protected $_primary = array('id_boutique_bois');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_bois', '*')
		->where('id_fk_lieu_boutique_bois = '.intval($id_lieu));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_bois', 'quantite_rondin_boutique_bois as quantiteRondin')
		->where('id_fk_lieu_boutique_bois = ?',$data["id_fk_lieu_boutique_bois"])
		->where('date_achat_boutique_bois = ?',$data["date_achat_boutique_bois"]);
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$quantiteRodin = $resultat[0]["quantiteRondin"];
			if (isset($data["quantite_rondin_boutique_bois"])) {
				$dataUpdate['quantite_rondin_boutique_bois'] = $quantiteRodin + $data["quantite_rondin_boutique_bois"];
			}
			if (isset($dataUpdate)) {
				$where = 'id_fk_lieu_boutique_bois = '.$data["id_fk_lieu_boutique_bois"];
				$this->update($dataUpdate, $where);
			}
		}
	}
	
	function countVenteByDateAndRegion($dateDebut, $dateFin, $idRegion) {
		return $this->countByDateAndRegion($dateDebut, $dateFin, $idRegion, "vente");
	}
	
	function countRepriseByDateAndRegion($dateDebut, $dateFin, $idRegion) {
		return $this->countByDateAndRegion($dateDebut, $dateFin, $idRegion, "reprise");
	}
	
	private function countByDateAndRegion($dateDebut, $dateFin, $idRegion, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_bois', 'count(*) as nombre')
		->where('id_fk_region_boutique_bois = ?', $idRegion)
		->where('date_achat_boutique_bois >= ?', $dateDebut)
		->where('date_achat_boutique_bois <= ?', $dateFin)
		->where('action_boutique_bois = ?', $type);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
	
}
