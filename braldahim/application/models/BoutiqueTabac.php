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
class BoutiqueTabac extends Zend_Db_Table {
	protected $_name = 'boutique_tabac';
	protected $_primary = array('id_boutique_tabac');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_lieu_boutique_tabac = '.intval($id_lieu))
		->where('boutique_tabac.id_fk_type_boutique_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
	
	function countVenteByDateAndRegion($dateDebut, $dateFin, $idRegion, $idTypeTabac) {
		return $this->countByDateAndRegion($dateDebut, $dateFin, $idRegion, $idTypeTabac, "vente");
	}
	
	function countRepriseByDateAndRegion($dateDebut, $dateFin, $idRegion, $idTypeTabac) {
		return $this->countByDateAndRegion($dateDebut, $dateFin, $idRegion, $idTypeTabac, "reprise");
	}
	
	private function countByDateAndRegion($dateDebut, $dateFin, $idRegion, $idTypeTabac, $type) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_tabac', 'SUM(quantite_feuille_boutique_tabac) as nombre')
		->where('id_fk_region_boutique_tabac = ?', $idRegion)
		->where('date_achat_boutique_tabac >= ?', $dateDebut)
		->where('date_achat_boutique_tabac <= ?', $dateFin)
		->where('action_boutique_tabac = ?', $type)
		->where('id_fk_type_boutique_tabac = ?', $idTypeTabac);
		$sql = $select->__toString();
		$resultat =  $db->fetchAll($sql);
		return $resultat[0]["nombre"];
	}
}
