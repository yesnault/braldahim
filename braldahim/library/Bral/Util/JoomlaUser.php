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
class Bral_Util_JoomlaUser {
	
	/*
	 * Returne true si le joueur est enregistre sur Joomla, false sinon.
	 * 
	 * Mets à jour la table hobbit avec l'id joomla
	 */
	public static function isJoomlaUser(&$user) {
		
		if ($user->id_fk_jos_users_hobbit != null) { // le joueur a d�j� l'id Joomla
			return true;
		}
		
		Zend_Loader::loadClass('JosUsers');
		$josUsersTable = new JosUsers();
		$josUser = $josUsersTable->findByUsername($user->email_hobbit);
		
		if (count($josUser) == 1) { // sauvegarde dans la table Hobbit
			$hobbitTable = new Hobbit();
			$user->id_fk_jos_users_hobbit = $josUser->id;
			$data = array('id_fk_jos_users_hobbit' => $user->id_fk_jos_users_hobbit);
			$where = "id_hobbit=".$user->id_hobbit;
			$hobbitTable->update($data, $where);
			unset($hobbitTable);
			return true;
		} else { // le joueur n'est pas encore enregistré sur Joomla
			return false;
		}
	}
	
	public static function changeUsernameAndMail($user) {
		if ($user->id_fk_jos_users_hobbit == null) {
			return false;
		}
		
		Zend_Loader::loadClass('JosUsers');
		$josUsersTable = new JosUsers();
		
		$data = array(
			'username' => $user->email_hobbit,
			'email' => $user->email_hobbit,
		);
		
		$where = "id=".$user->id_fk_jos_users_hobbit;
		$josUsersTable->update($data, $where);
		unset($josUsersTable);
		
		return true;
	}
	
	public static function setXmlResponseMessagerie(&$xml_response, $id_fk_jos_users_hobbit) {
		if ($id_fk_jos_users_hobbit != null) {
			$josUddeimTable = new JosUddeim();
			$nbNotRead = $josUddeimTable->countByToIdNotRead($id_fk_jos_users_hobbit);
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("action");
			$xml_entry->set_valeur("messagerie");
			$xml_entry->set_data($nbNotRead);
			$xml_response->add_entry($xml_entry);
		}
	}
}


