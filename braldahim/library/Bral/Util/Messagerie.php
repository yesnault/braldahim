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
class Bral_Util_Messagerie {

	private function __construct() {}

	public static function setXmlResponseMessagerie(&$xml_response, $id_hobbit) {
		$josUddeimTable = new JosUddeim();
		$nbNotRead = $josUddeimTable->countByToIdNotRead($id_hobbit);
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("action");
		$xml_entry->set_valeur("messagerie");
		$xml_entry->set_data($nbNotRead);
		$xml_response->add_entry($xml_entry);
	}
	
	public static function constructTabHobbit($tab_destinataires, $valeur = "valeur_2") {
		$hobbitTable = new Hobbit();
		$idDestinatairesTab = split(',', $tab_destinataires);
		
		$hobbits = $hobbitTable->findByIdList($idDestinatairesTab);
		
		if ($hobbits == null) {
			return null;
		}
			
		$destinataires = "";
		$aff_js_destinataires = "";
		$tabHobbits = null;

		foreach($hobbits as $h) {
			if (in_array($h["id_hobbit"],$idDestinatairesTab)) {
				if ($destinataires == "") {
					$destinataires = $h["id_hobbit"];
				} else {
					$destinataires = $destinataires.",".$h["id_hobbit"];
				}
				$aff_js_destinataires .= '<span id="m_'.$valeur.'_'.$h["id_hobbit"].'">';
				$aff_js_destinataires .= $h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')  <img src="/public/images/supprimer.gif" ';
				$aff_js_destinataires .= ' onClick="javascript:supprimerElement(\'aff_'.$valeur.'_dest\',\'m_'.$valeur.'_'.$h["id_hobbit"].'\', \''.$valeur.'_dest\', \''.$h["id_hobbit"].'\')" />';
				$aff_js_destinataires .= '</span>';
				
				$tabHobbits[$h["id_hobbit"]] = $h;
			}
		}
		$tab = array(
			"hobbits" => $tabHobbits,
			"destinataires" => $destinataires,
			"aff_js_destinataires" => $aff_js_destinataires,
		);
		return $tab;
	}
	
	public static function constructTabContacts($tabContacts, $idHobbit, $valeur = "valeur_4_contacts") {
		Zend_Loader::loadClass('JosUserlists');
		
		$tab = array("contacts" => "", "aff_js_contacts" => "", "userids" => "", "hobbits" => null);
		
		if ($tabContacts == null || $tabContacts == "") {
			return $tab;
		}
		
		$josUserListsTable = new JosUserlists();
		$idContactsTab = split(',', $tabContacts);
		
		$contactsTab = $josUserListsTable->findByIdsList($idContactsTab, $idHobbit);
		
		if ($contactsTab == null) {
			return $tab;
		}
			
		$contacts = "";
		$aff_js_contacts = "";
		$userIds = "";
		$hobbits = "";
		$tabHobbits = null;
		
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
			$tabHobbits[] = $c["userids"];
		}
		
		if ($tabHobbits != null) {
			$hobbitTable = new Hobbit();
			$hobbitsRowset = $hobbitTable->findByIdList($tabHobbits);
			foreach($hobbitsRowset as $h) {
				$hobbits[$h["id_hobbit"]] = $h;
			}
		}
		
		$tab = array(
			"hobbits" => $hobbits,
			"aff_js_contacts" => $aff_js_contacts,
			"userids" => $userIds,
			"contacts" => $contacts,
		);
		return $tab;
	}
	
	public static function prepareListe($id_hobbit) {
		Zend_Loader::loadClass("JosUserlists");
		$josUserlistsTable = new JosUserlists();
		$listesContacts = $josUserlistsTable->findByUserId($id_hobbit);
		
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