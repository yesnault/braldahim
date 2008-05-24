<?php

class Bral_Util_JoomlaUser {
	
	/*
	 * Returne true si le joueur est enregistre sur Joomla, false sinon.
	 * 
	 * Mets à jour la table hobbit avec l'id joomla
	 */
	public static function isJoomlaUser(&$user) {
		
		if ($user->id_fk_jos_users_hobbit != null) { // le joueur a déjà l'id Joomla
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
}


