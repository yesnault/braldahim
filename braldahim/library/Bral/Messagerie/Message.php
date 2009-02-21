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
class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass('Bral_Util_Messagerie');
		Zend_Loader::loadClass('Bral_Util_Mail');
		
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->view->message = null;
		$this->view->information = null;
		$this->refreshMessages = false;
		$this->view->envoiMessage = false;
		$this->prepareAction();
	}

	public function getNomInterne() {
		return "messagerie_contenu";
	}

	function render() {
		switch($this->request->get("valeur_1")) {
			case "envoi" :
				if ($this->view->envoiMessage) {
					return $this->view->render("messagerie/envoi.phtml");
					break;
				}
			case "nouveau" :
			case "repondre" :
			case "repondretous" :
			case "transferer" :
				return $this->view->render("messagerie/nouveau.phtml");
				break;
			case "supprimer" :
			case "supprimerselection" :
			case "message" :
				return $this->view->render("messagerie/message.phtml");
				break;
			default :
				throw new Zend_Exception(get_class($this)."::render invalide :".$this->request->get("valeur_1"));
		}
	}

	public function refreshMessages() {
		return $this->refreshMessages;
	}

	public function getInformations() {
		if ($this->view->envoiMessage == true) {
			return "Votre message est envoy&eacute;";		
		}
	}

	private function prepareAction() {
		$this->view->valeur_1 = $this->request->get("valeur_1");

		switch($this->request->get("valeur_1")) {
			case "envoi" :
				$this->envoiMessage();
				break;
			case "nouveau" :
				$this->prepareNouveau();
				break;
			case "repondre" :	
				$this->prepareRepondre(false, false);
				break;
			case "repondretous" :	
				$this->prepareRepondre(false, true);
				break;
			case "transferer" :
				$this->prepareRepondre(true, false);
				break;
			case "supprimer" :
				$this->prepareSupprimer();
				break;
			case "supprimerselection" :
				$this->prepareSupprimerSelection();
				break;
			case "message" :
				$this->prepareMessage();
				break;
			default :
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->request->get("valeur_1"));
		}
	}

	private function prepareNouveau() {
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		
		$tabHobbit["destinataires"] = "";
		$tabHobbit["aff_js_destinataires"] = "";
		if ($this->request->get('valeur_2') != "") {
			$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($filter->filter(trim($this->request->get('valeur_2'))));
		} 
		
		$tabMessage = array(
			'contenu' => "",
			'destinataires' => $tabHobbit["destinataires"],
			'aff_js_destinataires' => $tabHobbit["aff_js_destinataires"],
			'contacts' => "",
			'aff_js_contacts' => "",
		);
		$this->view->message = $tabMessage;
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_hobbit);
	}

	private function prepareRepondre($transferer, $repondretous) {
		$this->prepareMessage();
		if ($transferer == false) {
			$listeId = $this->view->message["fromid"].",";
			if ($repondretous) {
				$listeId = $listeId.$this->view->message["toids"].",";
				$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($listeId, "valeur_2", $this->view->user->id_hobbit);
			} else {
				$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($listeId);
			}
		} else {
			$tabHobbit = array("destinataires" => "",
				"aff_js_destinataires" => "",
			);
		}
		
		$contenu = "


__________
Message de ".$this->view->message["expediteur"]." le ".date('d/m/y, H:i', $this->view->message["date"])." : 
".$this->view->message["titre"];
		
		$tabMessage = array(
			"contenu" => $contenu,
			"destinataires" => $tabHobbit["destinataires"],
			"aff_js_destinataires" => $tabHobbit["aff_js_destinataires"],
			"contacts" => "",
			"aff_js_contacts" => "",
		);
		$this->view->message = $tabMessage;
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_hobbit);
	}
	
	private function envoiMessage() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Contacts");
		Zend_Loader::loadClass("Zend_Filter_StripTags");

		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_hobbit);
		
		$filter = new Zend_Filter_StripTags();
		$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($filter->filter(trim($this->request->get('valeur_2'))));
		$tabContacts = Bral_Util_Messagerie::constructTabContacts($filter->filter(trim($this->request->get('valeur_4'))), $this->view->user->id_hobbit);

		$tabMessage = array(
			'contenu' => stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_3'))),
			'destinataires' => $tabHobbit["destinataires"],
			'aff_js_destinataires' => $tabHobbit["aff_js_destinataires"],
			"contacts" => $tabContacts["contacts"],
			"aff_js_contacts" => $tabContacts["aff_js_contacts"],
			"userids" => $tabContacts["userids"],
		);
		$this->view->message = $tabMessage;
		
		if ($this->view->listesContacts != null) {
			$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(false);
			$validateurContacts = new Bral_Validate_Messagerie_Contacts(false, $this->view->user->id_hobbit);
			$validContacts = $validateurContacts->isValid($this->view->message["contacts"]);
			$avecContacts = true;
		} else {
			$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
			$validContacts = true;
			$avecContacts = false;
		}
		
		$validateurContenu = new Bral_Validate_StringLength(1, 2500);
		$validDestinataires = $validateurDestinataires->isValid($this->view->message["destinataires"]);
		$validContenu = $validateurContenu->isValid($this->view->message["contenu"]);
		
		if ($validDestinataires && $validContacts) {
			if ((mb_strlen($this->view->message["destinataires"]) < 1)) {
				$destinatairesErreur[] = "Ce champ est obligatoire";
				$this->view->destinatairesErreur = $destinatairesErreur;
				$validDestinataires = false;
			}
		}

		if (($validDestinataires || ($validContacts && $avecContacts) ) && ($validContenu)) {
			$josUddeimTable = new JosUddeim();
			
			$debutContenuMail = "Message de ".$this->view->user->prenom_hobbit." ".$this->view->user->nom_hobbit." (".$this->view->user->id_hobbit.") : ";

			$tabIdDestinatairesDejaEnvoye = array();
			$tabHobbits = array();
			$idDestinatairesTab = array();
			$idDestinatairesListe = null;
			
			if ($this->view->message["destinataires"] != "") {
				$idDestinatairesTab = split(',', $this->view->message["destinataires"]);
				$idDestinatairesListe = $this->view->message["destinataires"];
				$tabHobbits = $tabHobbit["hobbits"];
			}
			
			if ($this->view->message["userids"] != "") {
				$idDestinatairesTab = $idDestinatairesTab + split(',', $this->view->message["userids"]);
				if ($idDestinatairesListe != null) {
					$idDestinatairesListe .= ",";
				}
				$idDestinatairesListe = $idDestinatairesListe . $this->view->message["userids"];
				$tabHobbits = $tabHobbits + $tabContacts["hobbits"];
			}
			
			if ($tabHobbits != null && count($idDestinatairesTab) > 0) {
				foreach ($idDestinatairesTab as $id_hobbit) {
					$data = $this->prepareMessageAEnvoyer($this->view->user->id_hobbit, $id_hobbit, $tabMessage["contenu"], $idDestinatairesListe);
					if (!in_array($id_hobbit, $tabIdDestinatairesDejaEnvoye)) {
						$josUddeimTable->insert($data);
						$tabIdDestinatairesDejaEnvoye[] = $id_hobbit;
						if ($tabHobbits[$id_hobbit]["envoi_mail_message_hobbit"] == "oui") {
							Bral_Util_Mail::envoiMailAutomatique($tabHobbits[$id_hobbit], $this->view->config->mail->message->titre, $debutContenuMail.$tabMessage["contenu"], $this->view);
						}
					}
				}
			}

			$this->view->envoiMessage = true;
		} else {
			if (!$validDestinataires) {
				foreach ($validateurDestinataires->getMessages() as $message) {
					$destinatairesErreur[] = $message;
				}
				$this->view->destinatairesErreur = $destinatairesErreur;
			}

			if (!$validContacts) {
				foreach ($validateurContacts->getMessages() as $message) {
					$contactsErreur[] = $message;
				}
				$this->view->contactsErreur = $contactsErreur;
			}
		}
	}
	
	private function prepareMessageAEnvoyer($idHobbitSource, $idHobbitDestinataire, $contenu, $idDestinatairesListe) {
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
	
	private function prepareMessage() {
		$josUddeimTable = new JosUddeim();
		$message = $josUddeimTable->findById($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));

		$tabMessage = null;
		if ($message != null && count($message) == 1) {
			$message = $message[0];
			
			$idsHobbit[] = $message["toid"];
			$idsHobbit[] = $message["fromid"];
			$idsHobbit = array_merge($idsHobbit, split(',', $message["toids"]));
			if ($idsHobbit != null) {
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->findByIdList($idsHobbit);
				if ($hobbits != null) {
					foreach($hobbits as $h) {
						$tabHobbits[$h["id_hobbit"]] = $h;
					}
				}
			}
			
			$expediteur = "";
			$destinataire = "";
			$destinataires = "";
			if ($tabHobbits != null) {
				if (array_key_exists($message["fromid"], $tabHobbits)) {
					$expediteur = $tabHobbits[$message["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["fromid"]]["nom_hobbit"]. " (".$tabHobbits[$message["fromid"]]["id_hobbit"].")";
				} else {
					$expediteur = " Erreur ".$message["fromid"];
				}
				
				if (array_key_exists($message["toid"], $tabHobbits)) {
					$destinataire = $tabHobbits[$message["toid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["toid"]]["nom_hobbit"]. " (".$tabHobbits[$message["toid"]]["id_hobbit"].")";
				} else {
					$destinataire = " Erreur Hobbit n°".$message["toid"];
				}
				
				$tabDestinataires = split(',', $message["toids"]);
				foreach ($tabDestinataires as $k=>$d) {
					if (array_key_exists($d, $tabHobbits)) {
						$destinataires .= $tabHobbits[$d]["prenom_hobbit"] . " ". $tabHobbits[$d]["nom_hobbit"]. " (".$tabHobbits[$d]["id_hobbit"]."), ";
					} else {
						$destinataires .= " Erreur Hobbit n°:".$d." ";
					}
				}
			}
			
			if ($expediteur == "") {
				$expediteur = " Erreur inconnue";
			}
			
			if ($destinataire == "") {
				$destinataire = " Erreur inconnue";
			}
			
			if ($destinataires == "") {
				$destinataires = " Erreur inconnue";
			} else {
				$destinataires = substr($destinataires, 0, strlen($destinataires) - 2);
			}
			
			$tabMessage = array(
				"id_message" => $message["id"],
				"titre" => $message["message"],
				"date" => $message["datum"],
				'expediteur' => $expediteur,
				'destinataire' => $destinataire,
				'destinataires' => $destinataires,
				"fromid" => $message["fromid"],
				"toid" => $message["fromid"],
				"toids" => $message["toids"],
				"toread" => $message["toread"],
			);
			
			// Flag de lecture
			if ($message["toid"] == $this->view->user->id_hobbit && $message["toread"] == 0) {
				$data = array(
					"toread" => 1,
				);
				$where = "id=".$message["id"];
				$josUddeimTable->update($data, $where);
			}
			unset($josUddeimTable);
			unset($message);
		} else {
			throw new Zend_Exception(get_class($this)."::prepareMessage Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		$this->view->message = $tabMessage;
	}
	
	private function prepareSupprimer() {
		$josUddeimTable = new JosUddeim();
		$message = $josUddeimTable->findById($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));
		if ($message != null && count($message) == 1) {
			$message = $message[0];
			if ($message["fromid"] == $this->view->user->id_hobbit) {
				$data = array(
					"totrashoutbox" => 1,
					"totrashdateoutbox" => time(),
				);
				if ($message["toid"] == $this->view->user->id_hobbit) {
					$data["totrash"] = 1;
					$data["totrashdate"] = time();
					
				}
			} else {
				$data = array(
					"totrash" => 1,
					"totrashdate" => time(),
				);
			}
			$where = "id=".(int)$this->request->get("valeur_2");
			$josUddeimTable->update($data, $where);
			$this->view->information = "Le message est supprim&eacute;";
			$this->refreshMessages = true;
		} else {
			throw new Zend_Exception(get_class($this)."::supprimer Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		unset($josUddeimTable);
		unset($message);
	}
	
	private function prepareSupprimerSelection() {
		$josUddeimTable = new JosUddeim();
		$listMessages = split(',', $this->request->get("valeur_2"));
		$messages = $josUddeimTable->findByIdList($this->view->user->id_hobbit, $listMessages);
		
		if ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["fromid"] == $this->view->user->id_hobbit) {
					$data = array(
						"totrashoutbox" => 1,
						"totrashdateoutbox" => time(),
					);
					if ($message["toid"] == $this->view->user->id_hobbit) {
						$data["totrash"] = 1;
						$data["totrashdate"] = time();
						
					}
				} else {
					$data = array(
						"totrash" => 1,
						"totrashdate" => time(),
					);
				}
				$where = "id=".$message["id"];
				$josUddeimTable->update($data, $where);
				
				$this->refreshMessages = true;
			}
			if (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont supprim&eacute;s";
			} else {
				$this->view->information = "Le message sélectionné est supprim&eacute;";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::supprimerselection Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		unset($josUddeimTable);
		unset($message);
	}
}