<?php

class Bral_Util_Messagerie {

	private function __construct() {}

	public static function constructTabHobbit($tab_destinataires, $valeur = "valeur_2") {
		$hobbitTable = new Hobbit();
		$idDestinatairesTab = split(',', $tab_destinataires);
		
		$hobbits = $hobbitTable->findByIdFkJosUsersList($idDestinatairesTab);
		
		if ($hobbits == null) {
			return null;
		}
			
		$destinataires = "";
		$aff_js_destinataires = "";

		foreach($hobbits as $h) {
			if (in_array($h["id_fk_jos_users_hobbit"],$idDestinatairesTab)) {
				if ($destinataires == "") {
					$destinataires = $h["id_fk_jos_users_hobbit"];
				} else {
					$destinataires = $destinataires.",".$h["id_fk_jos_users_hobbit"];
				}
				$aff_js_destinataires .= '<span id="m_'.$valeur.'_'.$h["id_hobbit"].'">';
				$aff_js_destinataires .= $h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')  <img src="/public/images/supprimer.gif" ';
				$aff_js_destinataires .= ' onClick="javascript:supprimerElement(\'aff_'.$valeur.'_dest\',\'m_'.$valeur.'_'.$h["id_hobbit"].'\', \''.$valeur.'_dest\', '.$h["id_fk_jos_users_hobbit"].')" />';
				$aff_js_destinataires .= '</span>';
			}
		}
		$tab = array("destinataires" => $destinataires,
			"aff_js_destinataires" => $aff_js_destinataires,
		);
		return $tab;
	}
	
	public static function constructTabContacts($tabContacts, $idFkJosUsersHobbit, $valeur = "valeur_4_contacts") {
		Zend_Loader::loadClass('JosUserlists');
		
		$tab = array("contacts" => "", "aff_js_contacts" => "", "userids" => "");
		
		if ($tabContacts == null || $tabContacts == "") {
			return $tab;
		}
		
		$josUserListsTable = new JosUserlists();
		$idContactsTab = split(',', $tabContacts);
		
		$contactsTab = $josUserListsTable->findByIdsList($idContactsTab, $idFkJosUsersHobbit);
		
		if ($contactsTab == null) {
			return $tab;
		}
			
		$contacts = "";
		$aff_js_contacts = "";
		$userIds = "";
		
		foreach($contactsTab as $c) {
			if (in_array($c["id"], $idContactsTab)) {
				if ($contacts == "") {
					$contacts = $c["id"];
				} else {
					$contacts = $contacts.",".$c["id"];
				}
				$aff_js_contacts .= '<span id="m_'.$valeur.'_'.$c["id"].'">';
				$aff_js_contacts .= $c["name"]. ' <img src="/public/images/supprimer.gif" ';
				$aff_js_contacts .= ' onClick="javascript:supprimerElement(\'aff_'.$valeur.'\',\'m_'.$valeur.'_'.$c["id"].'\', \''.$valeur.'\', '.$c["id"].')" />';
				$aff_js_contacts .= '</span>';
			}
			
			if ($userIds != "") {
				$userIds .= ",";
			}
			$userIds .= $c["userids"];
		}
		
		$tab = array("contacts" => $contacts,
			"aff_js_contacts" => $aff_js_contacts,
			"userids" => $userIds,
		);
		return $tab;
	}
	
	public static function prepareListe($id_fk_jos_users_hobbit) {
		Zend_Loader::loadClass("JosUserlists");
		$josUserlistsTable = new JosUserlists();
		$listesContacts = $josUserlistsTable->findByUserId($id_fk_jos_users_hobbit);
		
		$tabListes = null;
		if ($listesContacts != null && count($listesContacts) > 0) {
			$idsHobbit = null;
			foreach($listesContacts as $l) {
				$tabListes[] = array(
					'id' => $l["id"],
					'nom' => $l["name"],
					'description' => $l["description"]
				);
			}
		}
		
		return $tabListes;
	}
}