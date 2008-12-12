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
class Gardiennage extends Zend_Db_Table {
    protected $_name = 'gardiennage';
	protected $_referenceMap    = array(
        'Hobbitgarde' => array(
            'columns'           => array('id_fk_hobbit_gardiennage'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id_hobbit')
        ),
        'Gardien' => array(
            'columns'           => array('id_fk_gardien_gardiennage'),
            'refTableClass'     => 'Hobbit',
            'refColumns'        => array('id_hobbit')
        )
	);
	
	/**
	 * Renvoie tous les gardiens du hobbit passe en parametre.
	 */
    function findGardiens($id_hobbit_garde) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', 'id_fk_gardien_gardiennage')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit'))
		->where('gardiennage.id_fk_gardien_gardiennage = hobbit.id_hobbit AND gardiennage.id_fk_hobbit_gardiennage = '.$id_hobbit_garde)
		->group('id_fk_gardien_gardiennage', 'nom_hobbit', 'prenom_hobbit')
		->where("hobbit.est_compte_actif_hobbit = 'oui'")
		->where("hobbit.est_en_hibernation_hobbit = 'non'");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
    }
    
    function findGardiennageEnCours($id_hobbit_garde) {
    	$date_courante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', '*')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit'))
		->where('gardiennage.id_fk_gardien_gardiennage = hobbit.id_hobbit')
		->where('gardiennage.id_fk_hobbit_gardiennage = '.$id_hobbit_garde)
		->where('gardiennage.date_fin_gardiennage >= \''.$date_courante.'\'');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
    function findGardeEnCours($id_hobbit_gardien) {
    	$date_courante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('gardiennage', '*')
		->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'email_hobbit'))
		->where('gardiennage.id_fk_hobbit_gardiennage = hobbit.id_hobbit')
		->where('gardiennage.id_fk_gardien_gardiennage = '.$id_hobbit_gardien)
		->where("hobbit.est_compte_actif_hobbit = 'oui'")
		->where("hobbit.est_en_hibernation_hobbit = 'non'")
		->where('gardiennage.date_fin_gardiennage >= \''.$date_courante.'\'');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
}