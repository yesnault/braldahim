<?php

class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass('Bral_Util_Messagerie');
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
			case "transferer" :
				return $this->view->render("messagerie/nouveau.phtml");
				break;
			case "supprimer" :
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
				$this->prepareRepondre();
				break;
			case "transferer" :
				$this->prepareRepondre(true);
				break;
			case "supprimer" :
				$this->prepareSupprimer();
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
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_fk_jos_users_hobbit);
	}

	private function prepareRepondre($transferer = false) {
		$this->prepareMessage();
		if ($transferer == false) {	
			$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($this->view->message["fromid"].",", true);
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
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_fk_jos_users_hobbit);
	}
	
	private function envoiMessage() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass('Zend_Filter_StripTags');

		$filter = new Zend_Filter_StripTags();
		$tabHobbit = Bral_Util_Messagerie::constructTabHobbit($filter->filter(trim($this->request->get('valeur_2'))));
		
		$tabContacts = Bral_Util_Messagerie::constructTabContacts($filter->filter(trim($this->request->get('valeur_4'))), $this->view->user->id_fk_jos_users_hobbit);

		$tabMessage = array(
			'contenu' => stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_3'))),
			'destinataires' => $tabHobbit["destinataires"],
			'aff_js_destinataires' => $tabHobbit["aff_js_destinataires"],
			"contacts" => $tabContacts["contacts"],
			"aff_js_contacts" => $tabContacts["aff_js_contacts"],
		);
		$this->view->message = $tabMessage;

		$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
		$validateurContenu = new Bral_Validate_StringLength(1, 2500);

		$validDestinataires = $validateurDestinataires->isValid($this->view->message["destinataires"]);
		$validContenu = $validateurContenu->isValid($this->view->message["contenu"]);

		if (($validDestinataires) && ($validContenu)) {
			$josUddeimTable = new JosUddeim();
			
			$idDestinatairesTab = split(',', $this->view->message["destinataires"]);
			foreach ($idDestinatairesTab as $id_fk_jos_users_hobbit) {
			
				$data = array (
					'fromid' => $this->view->user->id_fk_jos_users_hobbit,
					'toid' => $id_fk_jos_users_hobbit,
					'message' => $tabMessage["contenu"],
					'datum' => time(),
					'toread' => 0,
					'totrash' => 0,
					'totrashoutbox' => 0,
					'disablereply' => 0,
					'archived' => 0,
					'cryptmode' => 0,
				);
				$josUddeimTable->insert($data);
			}
			
			$idContactsTab = split(',', $this->view->message["contacts"]);
			foreach ($idContactsTab as $id_fk_jos_users_hobbit) {
				$data = array (
					'fromid' => $this->view->user->id_fk_jos_users_hobbit,
					'toid' => $id_fk_jos_users_hobbit,
					'message' => $tabMessage["contenu"],
					'datum' => time(),
					'toread' => 0,
					'totrash' => 0,
					'totrashoutbox' => 0,
					'disablereply' => 0,
					'archived' => 0,
					'cryptmode' => 0,
				);
				$josUddeimTable->insert($data);
			}

			$this->view->envoiMessage = true;
		} else {
			if (!$validDestinataires) {
				foreach ($validateurDestinataires->getMessages() as $message) {
					$destinatairesErreur[] = $message;
				}
				$this->view->destinatairesErreur = $destinatairesErreur;
			}
		}
	}
	
	private function prepareMessage() {
		$josUddeimTable = new JosUddeim();
		$message = $josUddeimTable->findById($this->view->user->id_fk_jos_users_hobbit, (int)$this->request->get("valeur_2"));

		$tabMessage = null;
		if ($message != null && count($message) == 1) {
			$message = $message[0];
			
			$idsHobbit[] = $message["toid"];
			$idsHobbit[] = $message["fromid"];
			
			if ($idsHobbit != null) {
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->findByIdFkJosUsersList($idsHobbit);
				if ($hobbits != null) {
					foreach($hobbits as $h) {
						$tabHobbits[$h["id_fk_jos_users_hobbit"]] = $h;
					}
				}
			}
			
			$expediteur = "";
			if ($tabHobbits != null) {
				if (array_key_exists($message["fromid"], $tabHobbits)) {
					$expediteur = $tabHobbits[$message["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["fromid"]]["nom_hobbit"]. " (".$tabHobbits[$message["fromid"]]["id_hobbit"].")";
				} else {
					$expediteur = " Erreur ".$message["fromid"];
				}
				
				if (array_key_exists($message["fromid"], $tabHobbits)) {
					$destinataire = $tabHobbits[$message["toid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["toid"]]["nom_hobbit"]. " (".$tabHobbits[$message["toid"]]["id_hobbit"].")";
				} else {
					$destinataire = " Erreur ".$message["toid"];
				}
			}
			
			if ($expediteur == "") {
				$expediteur = " Erreur inconnue";
			}
			
			if ($destinataire == "") {
				$destinataire = " Erreur inconnue";
			}
			
			$tabMessage = array(
				"id_message" => $message["id"],
				"titre" => $message["message"],
				"date" => $message["datum"],
				'expediteur' => $expediteur,
				'destinataire' => $destinataire,
				"fromid" => $message["fromid"],
				"toid" => $message["fromid"],
				"toread" => $message["toread"],
			);
			
			// Flag de lecture
			if ($message["toid"] == $this->view->user->id_fk_jos_users_hobbit && $message["toread"] == 0) {
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
		$message = $josUddeimTable->findById($this->view->user->id_fk_jos_users_hobbit, (int)$this->request->get("valeur_2"));
		if ($message != null && count($message) == 1) {
			$message = $message[0];
			if ($message["fromid"] == $this->view->user->id_fk_jos_users_hobbit) {
				$data = array(
					"totrashoutbox" => 1,
					"totrashdateoutbox" => time(),
				);
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
}