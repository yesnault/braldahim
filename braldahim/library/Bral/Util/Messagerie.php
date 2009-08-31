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

	public static function constructTabHobbit($tabDestinataires, $valeur = "valeur_2", $sansIdHobbit = -1) {
		Zend_Loader::loadClass("Bral_Util_Lien");
		$hobbitTable = new Hobbit();
		$idDestinatairesTab = split(',', $tabDestinataires);

		$hobbits = $hobbitTable->findByIdList($idDestinatairesTab);

		if ($hobbits == null) {
			return null;
		}
			
		$destinataires = "";
		$aff_js_destinataires = "";
		$tabHobbits = null;

		foreach($hobbits as $h) {

			if (in_array($h["id_hobbit"],$idDestinatairesTab) && ($sansIdHobbit == -1 || $sansIdHobbit != $h["id_hobbit"])) {
				if ($destinataires == "") {
					$destinataires = $h["id_hobbit"];
				} else {
					$destinataires = $destinataires.",".$h["id_hobbit"];
				}
				$aff_js_destinataires .= '<span id="m_'.$valeur.'_'.$h["id_hobbit"].'">';
				$aff_js_destinataires .= Bral_Util_Lien::getJsHobbit($h["id_hobbit"], $h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')');
				$aff_js_destinataires .= ' <img src="/public/images/supprimer.gif" ';
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
			$tab = split(',', $c["userids"]);
			foreach($tab as $t) {
				$tabHobbits[] = $t;
			}
		}

		$userIdsControlles = "";

		if ($tabHobbits != null) {
			$hobbitTable = new Hobbit();
			$hobbitsRowset = $hobbitTable->findByIdList($tabHobbits);
			foreach($hobbitsRowset as $h) {
				$hobbits[$h["id_hobbit"]] = $h;
				if ($userIdsControlles != "") {
					$userIdsControlles .= ",";
				}
				$userIdsControlles .= $h["id_hobbit"];
			}
		}

		$tab = array(
			"hobbits" => $hobbits,
			"aff_js_contacts" => $aff_js_contacts,
			"userids" => $userIdsControlles,
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

	public static function prepareMessageAEnvoyer($idHobbitSource, $idHobbitDestinataire, $contenu, $idDestinatairesListe) {
		return array ('fromid' => $idHobbitSource,
					  'toid' => $idHobbitDestinataire,
						'toids' => $idDestinatairesListe,
						'message' => $contenu,
						'datum' => time(),
						'toread' => 0,
						'totrash' => 0,
						'totrashoutbox' => 0,
						'disablereply' => 0,
						'archived' => 0,
						'cryptmode' => 0,
		);
	}

	public static function envoiMessageAutomatique($idHobbitSource, $idHobbitDestinataire, $contenu) {
		Zend_Loader::loadClass("JosUddeim");
		$data = Bral_Util_Messagerie::prepareMessageAEnvoyer($idHobbitSource, $idHobbitDestinataire, $contenu, $idHobbitDestinataire);
		$josUddeimTable = new JosUddeim();
		$josUddeimTable->insert($data);
		
		/** TODO
		if ($view != null && $envoiEmail) {
			Bral_Util_Mail::envoiMailAutomatique($idHobbitDestinataire, $this->view->config->mail->message->titre, $contenu, $view);
		}
		*/
	}

	public static function prepareMessages($idHobbit, $filtre = null, $page = null, $nbMax = null, $toread = null) {
		Zend_Loader::loadClass("Bral_Util_Lien");

		$josUddeimTable = new JosUddeim();
		$config = Zend_Registry::get('config');

		if ($filtre == $config->messagerie->message->type->envoye) {
			$messages = $josUddeimTable->findByFromId($idHobbit, $page, $nbMax);
		} else if ($filtre == $config->messagerie->message->type->supprime) {
			$messages = $josUddeimTable->findByToOrFromIdSupprime($idHobbit, $page, $nbMax);
		} else { // reception
			$messages = $josUddeimTable->findByToId($idHobbit, $page, $nbMax, $toread);
		}

		$idsHobbit = "";
		$tabHobbits = null;
		$tabMessages = null;

		if ($messages != null) {
			foreach ($messages as $m) {
				if ($filtre == $config->messagerie->message->type->envoye) {
					$fieldId = "toid";
				} else {
					$fieldId = "fromid";
				}
				$idsHobbit[$m["toid"]] = $m["toid"];
				$idsHobbit[$m["fromid"]] = $m["fromid"];
			}
				
			if ($idsHobbit != null) {
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->findByIdList($idsHobbit);
				if ($hobbits != null) {
					foreach($hobbits as $h) {
						$tabHobbits[$h["id_hobbit"]] = $h;
					}
				}
			}
				
			foreach ($messages as $m) {
				$expediteur = "";
				$destinataire = "";
				if ($tabHobbits != null) {
					if (array_key_exists($m["toid"], $tabHobbits)) {
						$destinataire = Bral_Util_Lien::getJsHobbit($tabHobbits[$m["toid"]]["id_hobbit"], $tabHobbits[$m["toid"]]["prenom_hobbit"] . " ". $tabHobbits[$m["toid"]]["nom_hobbit"]. " (".$tabHobbits[$m["toid"]]["id_hobbit"].")");
					} else {
						$destinataire = " Erreur ".$m["toid"];
					}
						
					if (array_key_exists($m["fromid"], $tabHobbits)) {
						$expediteur = Bral_Util_Lien::getJsHobbit($tabHobbits[$m["fromid"]]["id_hobbit"], $tabHobbits[$m["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$m["fromid"]]["nom_hobbit"]. " (".$tabHobbits[$m["fromid"]]["id_hobbit"].")");
					} else {
						$expediteur = " Erreur ".$m["fromid"];
					}
				}
				if ($expediteur == "") {
					$expediteur = " Erreur inconnue";
				}
				if ($destinataire == "") {
					$destinataire = " Erreur inconnue";
				}

				$tabMessages[] = array(
					"id_message" => $m["id"],
					"titre" => $m["message"],
					"date" => $m["datum"],
					"expediteur" => $expediteur,
					"destinataire" => $destinataire,
					"toread" => $m["toread"],
				);
			}
		}
		return $tabMessages;
	}

}