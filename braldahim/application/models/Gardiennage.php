<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Gardiennage extends Zend_Db_Table {
	protected $_name = 'gardiennage';
	protected $_referenceMap    = array(
        'Braldungarde' => array(
            'columns'           => array('id_fk_braldun_gardiennage'),
            'refTableClass'     => 'Braldun',
            'refColumns'        => array('id_braldun')
	),
        'Gardien' => array(
            'columns'           => array('id_fk_gardien_gardiennage'),
            'refTableClass'     => 'Braldun',
            'refColumns'        => array('id_braldun')
	)
	);

	/**
	 * Renvoie tous les gardiens du braldun passe en parametre.
	 */
	function findGardiens($id_braldun_garde) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', 'id_fk_gardien_gardiennage')
		->from('braldun', array('nom_braldun', 'prenom_braldun'))
		->where('gardiennage.id_fk_gardien_gardiennage = braldun.id_braldun AND gardiennage.id_fk_braldun_gardiennage = '.$id_braldun_garde)
		->group('id_fk_gardien_gardiennage', 'nom_braldun', 'prenom_braldun')
		->where("braldun.est_compte_actif_braldun = 'oui'")
		->where("braldun.est_en_hibernation_braldun = 'non'");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findGardiennage30Jours($id_braldun_garde) {
		$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), -30);

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun'))
		->where('gardiennage.id_fk_gardien_gardiennage = braldun.id_braldun')
		->where('gardiennage.id_fk_braldun_gardiennage = '.$id_braldun_garde)
		->where('gardiennage.date_debut_gardiennage >= \''.$dateDebut.'\'');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findGardiennageEnCours($id_braldun_garde) {
		$date_courante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun'))
		->where('gardiennage.id_fk_gardien_gardiennage = braldun.id_braldun')
		->where('gardiennage.id_fk_braldun_gardiennage = '.$id_braldun_garde)
		->where('gardiennage.date_fin_gardiennage >= \''.$date_courante.'\'');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findGardeEnCours($id_braldun_gardien) {
		$date_courante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', '*')
		->from('braldun', array('nom_braldun', 'prenom_braldun', 'email_braldun'))
		->where('gardiennage.id_fk_braldun_gardiennage = braldun.id_braldun')
		->where('gardiennage.id_fk_gardien_gardiennage = ?', $id_braldun_gardien)
		->where("braldun.est_compte_actif_braldun = 'oui'")
		->where("braldun.est_en_hibernation_braldun = 'non'")
		->where('gardiennage.date_fin_gardiennage >= \''.$date_courante.'\'');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}