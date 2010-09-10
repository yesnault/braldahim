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

	public static function setXmlResponseMessagerie(&$xml_response, $id_braldun) {
		if (Zend_Registry::get("estMobile")) { 
			// n'est pas utilisé sur la version mobile
			return;
		}
		$messageTable = new Message();
		$nbNotRead = $messageTable->countByToIdNotRead($id_braldun);
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("action");
		$xml_entry->set_valeur("messagerie");
		$xml_entry->set_data($nbNotRead);
		$xml_response->add_entry($xml_entry);
	}

	public static function constructTabBraldun($tabDestinataires, $valeur = "valeur_2", $sansIdBraldun = -1, $afficheSupprimer = true) {
		Zend_Loader::loadClass("Bral_Util_Lien");
		$braldunTable = new Braldun();
		$idDestinatairesTab = preg_split("/,/", $tabDestinataires);

		$bralduns = $braldunTable->findByIdList($idDestinatairesTab);

		if ($bralduns == null) {
			return null;
		}
			
		$destinataires = "";
		$aff_js_destinataires = "";
		$tabBralduns = null;

		if ($afficheSupprimer) {
			$afficheLien = false;
		} else {
			$afficheLien = true;
		}

		foreach($bralduns as $h) {

			if (in_array($h["id_braldun"],$idDestinatairesTab) && ($sansIdBraldun == -1 || $sansIdBraldun != $h["id_braldun"])) {
				if ($destinataires == "") {
					$destinataires = $h["id_braldun"];
				} else {
					$destinataires = $destinataires.",".$h["id_braldun"];
				}
				if ($afficheSupprimer) $aff_js_destinataires .= '<span id="m_'.$valeur.'_'.$h["id_braldun"].'">';
				$aff_js_destinataires .= Bral_Util_Lien::getJsBraldun($h["id_braldun"], $h["prenom_braldun"].' '.$h["nom_braldun"].' ('.$h["id_braldun"].')', $afficheLien);
				if ($afficheSupprimer)  {
					$aff_js_destinataires .= ' <img src="/public/images/supprimer.gif" ';
					$aff_js_destinataires .= ' onClick="javascript:supprimerElement(\'aff_'.$valeur.'_dest\',\'m_'.$valeur.'_'.$h["id_braldun"].'\', \''.$valeur.'_dest\', \''.$h["id_braldun"].'\')" />';
					$aff_js_destinataires .= '</span>';
				} else {
					$aff_js_destinataires .= "<br>";
				}

				$tabBralduns[$h["id_braldun"]] = $h;
			}
		}
		$tab = array(
			"bralduns" => $tabBralduns,
			"destinataires" => $destinataires,
			"aff_js_destinataires" => $aff_js_destinataires,
		);
		return $tab;
	}

	public static function constructTabContacts($tabContacts, $idBraldun, $valeur = "valeur_4_contacts") {
		Zend_Loader::loadClass('MessagerieContacts');

		$tab = array("contacts" => "", "aff_js_contacts" => "", "userids" => "", "bralduns" => null);

		if ($tabContacts == null || $tabContacts == "") {
			return $tab;
		}

		$messagerieContactsTable = new MessagerieContacts();
		$idContactsTab = preg_split("/,/", $tabContacts);

		$contactsTab = $messagerieContactsTable->findByIdsList($idContactsTab, $idBraldun);
		if ($contactsTab == null) {
			return $tab;
		}
			
		$contacts = "";
		$aff_js_contacts = "";
		$userIds = "";
		$bralduns = "";
		$tabBralduns = null;

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
			$tab = preg_split("/,/", $c["userids"]);
			foreach($tab as $t) {
				$tabBralduns[] = $t;
			}
		}

		$userIdsControlles = "";

		if ($tabBralduns != null) {
			$braldunTable = new Braldun();
			$braldunsRowset = $braldunTable->findByIdList($tabBralduns);
			foreach($braldunsRowset as $h) {
				$bralduns[$h["id_braldun"]] = $h;
				if ($userIdsControlles != "") {
					$userIdsControlles .= ",";
				}
				$userIdsControlles .= $h["id_braldun"];
			}
		}

		$tab = array(
			"bralduns" => $bralduns,
			"aff_js_contacts" => $aff_js_contacts,
			"userids" => $userIdsControlles,
			"contacts" => $contacts,
		);
		return $tab;
	}

	public static function prepareListe($idBraldun, $prepareBralduns = false) {
		Zend_Loader::loadClass("MessagerieContacts");
		$messagerieContactsTable = new MessagerieContacts();
		$listesContacts = $messagerieContactsTable->findByUserId($idBraldun);

		$tabListes = null;
		if ($listesContacts != null && count($listesContacts) > 0) {
			$idsBraldun = null;
			foreach($listesContacts as $l) {
				$tab = array(
					'id' => $l["id"],
					'nom' => $l["name"],
					'description' => $l["description"],
					'userids' => $l["userids"],
				);

				if ($prepareBralduns) {
					$tab["bralduns"] = Bral_Util_Messagerie::constructTabBraldun($l["userids"], "listecontact", -1, false);
				}
				$tabListes[] = $tab;
			}
		}

		return $tabListes;
	}

	public static function prepareMessageAEnvoyer($idBraldunSource, $idBraldunDestinataire, $contenu, $idDestinatairesListe) {
		return array ('fromid' => $idBraldunSource,
					  'toid' => $idBraldunDestinataire,
						'toids' => $idDestinatairesListe,
						'message' => $contenu,
						'date_message' => date("Y-m-d H:i:s"),
						'toread' => 0,
						'totrash' => 0,
						'totrashoutbox' => 0,
						'archived' => 0,
		);
	}

	public static function envoiMessageAutomatique($idBraldunSource, $idBraldunDestinataire, $contenu, $view) {
		Zend_Loader::loadClass("Message");
		$data = Bral_Util_Messagerie::prepareMessageAEnvoyer($idBraldunSource, $idBraldunDestinataire, $contenu, $idBraldunDestinataire);
		$messageTable = new Message();
		$messageTable->insert($data);

		$envoiEmail = "non";
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->findById($idBraldunDestinataire);
		if ($braldunRowset != null) {
			$braldun = $braldunRowset->toArray();
			if ($braldun["envoi_mail_evenement_braldun"] == "oui") {
				$envoiEmail = "oui";
			}
		}

		if ($envoiEmail == "oui") {
			$config = Zend_Registry::get('config');
			Zend_Loader::loadClass("Bral_Util_Mail");
			Bral_Util_Mail::envoiMailAutomatique($braldun, $config->mail->message->titre, $contenu, $view);
		}
	}

	public static function preparesListAllMessages($idBraldun, $filtre) {

		$messageTable = new Message();
		$config = Zend_Registry::get('config');

		if ($filtre == $config->messagerie->message->type->envoye) {
			$select = $messageTable->getSelectByFromId($idBraldun);
		} else if ($filtre == $config->messagerie->message->type->supprime) {
			$select = $messageTable->getSelectByToOrFromIdSupprime($idBraldun);
		} else if ($filtre == $config->messagerie->message->type->archive) {
			$select = $messageTable->getSelectByToIdArchive($idBraldun);
		} else { // reception
			$select = $messageTable->getSelectByToId($idBraldun, null);
		}

		$messages = $messageTable->getAllWithSelect($select);
		$tabRetour = null;
		if (count($messages) > 0) {
			foreach($messages as $m) {
				$tabRetour[] = $m["id"];
			}
		}

		return $tabRetour;
	}

	public static function prepareMessages($idBraldun, &$paginator, $filtre, $page, $nbMax, $toread = null) {
		Zend_Loader::loadClass("Bral_Util_Lien");

		$messageTable = new Message();
		$config = Zend_Registry::get('config');

		if ($filtre == $config->messagerie->message->type->envoye) {
			$select = $messageTable->getSelectByFromId($idBraldun);
		} else if ($filtre == $config->messagerie->message->type->supprime) {
			$select = $messageTable->getSelectByToOrFromIdSupprime($idBraldun);
		} else if ($filtre == $config->messagerie->message->type->archive) {
			$select = $messageTable->getSelectByToIdArchive($idBraldun);
		} else { // reception
			$select = $messageTable->getSelectByToId($idBraldun, $toread);
		}

		Zend_Loader::loadClass('Zend_Paginator');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setPageRange(2);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($nbMax);

		$idsBraldun = "";
		$tabBralduns = null;
		$tabMessages = null;

		if (count($paginator) > 0) {
			foreach ($paginator as $m) {
				$idsBraldun[$m["toid"]] = $m["toid"];
				$idsBraldun[$m["fromid"]] = $m["fromid"];
			}

			if ($idsBraldun != null) {
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->findByIdList($idsBraldun);
				if ($bralduns != null) {
					foreach($bralduns as $h) {
						$tabBralduns[$h["id_braldun"]] = $h;
					}
				}
			}

			foreach ($paginator as $m) {
				$expediteur = "";
				$destinataire = "";
				if ($tabBralduns != null) {
					if (array_key_exists($m["toid"], $tabBralduns)) {
						//$destinataire = Bral_Util_Lien::getJsBraldun($tabBralduns[$m["toid"]]["id_braldun"], $tabBralduns[$m["toid"]]["prenom_braldun"] . " ". $tabBralduns[$m["toid"]]["nom_braldun"]. " (".$tabBralduns[$m["toid"]]["id_braldun"].")");
						$destinataire = $tabBralduns[$m["toid"]]["prenom_braldun"] . " ". $tabBralduns[$m["toid"]]["nom_braldun"]. " (".$tabBralduns[$m["toid"]]["id_braldun"].")";
					} else {
						$destinataire = " Erreur ".$m["toid"];
					}

					if (array_key_exists($m["fromid"], $tabBralduns)) {
						//$expediteur = Bral_Util_Lien::getJsBraldun($tabBralduns[$m["fromid"]]["id_braldun"], $tabBralduns[$m["fromid"]]["prenom_braldun"] . " ". $tabBralduns[$m["fromid"]]["nom_braldun"]. " (".$tabBralduns[$m["fromid"]]["id_braldun"].")");
						$expediteur = $tabBralduns[$m["fromid"]]["prenom_braldun"] . " ". $tabBralduns[$m["fromid"]]["nom_braldun"]. " (".$tabBralduns[$m["fromid"]]["id_braldun"].")";
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
					"date" => $m["date_message"],
					"expediteur" => $expediteur,
					"destinataire" => $destinataire,
					"toread" => $m["toread"],
				);
			}
		}
		return $tabMessages;
	}

	public static function preparePage($request, &$view) {
		$view->page = 1;

		$view->filtre = null;
		
		if ($request->get("valeur_4") != "") {
			$view->filtre =  Bral_Util_Controle::getValeurIntVerifSansException($request->get("valeur_4"));
		} 
		
		if ($view->filtre == null) {
			$view->filtre = $view->config->messagerie->message->type->reception;
		}

		switch($request->get("valeur_1")) {
			case "envoi" :
			case "nouveau" :
			case "repondre" :
			case "repondretous" :
				$view->page = 1;
				break;
			case "transferer" :
			case "archiver" :
			case "supprimer" :
			case "supprimerselection" :
			case "archiverselection" :
			case "marquerlueselection" :
			case "message" :
			case "page" :
				$view->page =  Bral_Util_Controle::getValeurIntVerif($request->get("valeur_3"));
				break;
			default:
				$view->page = 1;
				break;
		}

		if ($view->page < 1) {
			$view->page = 1;
		}
	}
}