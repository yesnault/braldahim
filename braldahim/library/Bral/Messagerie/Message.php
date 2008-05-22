<?php

class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass("Message");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->view->message = null;
		$this->view->information = " ";
		$this->refreshMessages = false;
		$this->view->envoiMessage = false;
		$this->prepareAction();
	}

	public function getNomInterne() {
		return "messagerie_message";
	}

	function render() {
		switch($this->request->get("valeur_1")) {
			case "envoi" :
			case "nouveau" :
//			case "repondre" :
				return $this->view->render("messagerie/nouveau.phtml");
				break;
//			case "archiver" :
//			case "supprimer" :
//			case "annuler" :
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
//			case "repondre" :
//				$this->prepareRepondre();
//				break;
//			case "archiver" :
//				$this->prepareArchiver();
//				break;
//			case "supprimer" :
//				$this->prepareSupprimer();
//				break;
			case "message" :
				$this->prepareMessage(false);
				break;
			default :
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->request->get("valeur_1"));
		}
	}

	private function prepareNouveau() {
		// rien à faire ici
	}

	private function envoiMessage() {
		// TODO
	}
	
	
/*	private function prepareRepondre() {
		$this->prepareMessage(true);
		$this->view->message["titre"] = "RE:".$this->view->message["titre"];
		$this->view->message["copies"] = $this->view->message["destinataires"] . $this->view->message["copies"];
	}

	private function prepareArchiver() {
		$messageTable = new Message();
		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_5"));
		if ($message != null) {
			$data = array('id_fk_type_message' => $this->view->config->messagerie->message->type->archive);
			$where = "id_message=".(int)$this->request->get("valeur_5");
			$messageTable->update($data, $where);
			$this->view->information = "Le message est archiv&eacute;";
			$this->refreshMessages = true;
		} else {
			throw new Zend_Exception(get_class($this)."::archiver Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_5"));
		}
	}

	private function prepareSupprimer() {
		$messageTable = new Message();
		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_5"));
		if ($message != null) {
			$data = array('id_fk_type_message' => $this->view->config->messagerie->message->type->supprime);
			$where = "id_message=".(int)$this->request->get("valeur_5");
			$messageTable->update($data, $where);
			$this->view->information = "Le message est supprim&eacute;";
			$this->refreshMessages = true;
		} else {
			throw new Zend_Exception(get_class($this)."::supprimer Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_5"));
		}
	}

	private function constructTabHobbit($isPourCopie,$tab_destinataires, $tab_copies, $tab_expediteur) {
		$hobbitTable = new Hobbit();
		
		if ($isPourCopie === true && strlen($tab_destinataires) > 0 && strlen($tab_copies) > 0) {
			$tab_copies = $tab_destinataires.",".$tab_copies;
			$tab_destinataires = $tab_expediteur;
		}
		
		$idDestinatairesTab = split(',', $tab_destinataires);
		if ($tab_expediteur != null) {
			$idExpediteurTab = split(',', $tab_expediteur);
		}
		if ($tab_copies != null) {
			$idCopiesTab = split(',', $tab_copies);
			$idTab1 = array_merge($idDestinatairesTab, $idCopiesTab);
		} else {
			$idCopiesTab = null;
			$idTab1 = $idDestinatairesTab;
		}
		if ($tab_expediteur != null) {
			$idTab = array_merge($idTab1, $idExpediteurTab);
		} else {
			$idTab = $idTab1;
		}
		$hobbits = $hobbitTable->findByIdList($idTab);
		if ($hobbits == null) {
			return null;
		}
		$expediteur = "";
		$aff_expediteur = "";
		$aff_js_expediteur = "";
		$destinataires = "";
		$aff_destinataires = "";
		$aff_js_destinataires = "";
		$copies = "";
		$aff_copies = "";
		$aff_js_copies = "";
		foreach($hobbits as $h) {
			if (in_array($h["id_hobbit"],$idDestinatairesTab)) {
				if ($destinataires == "") {
					$destinataires = $h["id_hobbit"];
				} else {
					$destinataires = $destinataires.",".$h["id_hobbit"];
				}
				$aff_js_destinataires = '<span id="m_valeur_7_'.$h["id_hobbit"].'">'.$h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')  <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\'aff_valeur_7\',\'m_valeur_7_'.$h["id_hobbit"].'\', \'valeur_7\', '.$h["id_hobbit"].')" /></span>';
				$aff_destinataires = $aff_destinataires.$h["prenom_hobbit"]." ".$h["nom_hobbit"].' ('.$h["id_hobbit"].') ';
			}
			if (($idCopiesTab != null) && (in_array($h["id_hobbit"],$idCopiesTab)) && (!in_array($h["id_hobbit"],$idDestinatairesTab))) {
				if ($copies == "") {
					$copies = $h["id_hobbit"];
				} else {
					$copies = $copies.",".$h["id_hobbit"];
				}
				$aff_js_copies = '<span id="m_valeur_8_'.$h["id_hobbit"].'">'.$h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')  <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\'aff_valeur_8\',\'m_valeur_8_'.$h["id_hobbit"].'\', \'valeur_8\', '.$h["id_hobbit"].')" /></span>';
				$aff_copies = $aff_copies.$h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].') ';
			}
			if ($tab_expediteur != null) {
				if (in_array($h["id_hobbit"],$idExpediteurTab)) {
					$expediteur = $h["id_hobbit"];
					$aff_expediteur = $h["prenom_hobbit"] ." ". $h["nom_hobbit"] . " (".$h["id_hobbit"].") ";
					$aff_js_expediteur = $h["prenom_hobbit"].' '.$h["nom_hobbit"].' ('.$h["id_hobbit"].')  <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\'aff_valeur_7\',\'m_valeur_7_'.$h["id_hobbit"].'\', \'valeur_7\', '.$h["id_hobbit"].')" />';
				}
			}
		}
		$tab = array("destinataires" => $destinataires,
			"aff_destinataires" => $aff_destinataires,
			"aff_js_destinataires" => $aff_js_destinataires,
			"copies" => $copies,
			"aff_copies" => $aff_copies,
			"aff_js_copies" => $aff_js_copies,
			"expediteur" => $expediteur,
			"aff_expediteur" => $aff_expediteur,
			"aff_js_expediteur" => $aff_js_expediteur
		);
		return $tab;
	}

	private function prepareMessage($isPourCopie) {
		$messageTable = new Message();

		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_5"));
		$tabMessage = null;
		if (count($message) == 1) {
			$m = $message[0];
			$tabHobbit = $this->constructTabHobbit($isPourCopie, $m["destinataires_message"], $m["copies_message"], $m["expediteur_message"]);

			if ($m["date_lecture_message"] == null) {
				$data = array('date_lecture_message' => date("Y-m-d H:i:s"));
				$where = "id_message=".$m["id_message"];
				$messageTable->update($data, $where);
			}
			$tabMessage = array(
				'id_message' => $m["id_message"],
				'titre' => $m["titre_message"],
				'date_envoi' => $m["date_envoi_message"],
				'expediteur' => $tabHobbit["expediteur"],
				'aff_expediteur' => $tabHobbit["aff_expediteur"],
				'aff_js_expediteur' => $tabHobbit["aff_js_expediteur"],
				'destinataires' => $tabHobbit["destinataires"],
				'aff_destinataires' => $tabHobbit["aff_destinataires"],
				'aff_js_destinataires' => $tabHobbit["aff_js_destinataires"],
				'copies' => $tabHobbit["copies"],
				'aff_js_copies' => $tabHobbit["aff_js_copies"],
				'aff_copies' => $tabHobbit["aff_copies"],
				'contenu' => $m["contenu_message"],
			);
		} else {
			throw new Zend_Exception(get_class($this)."::prepareMessage Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_5"));
		}
		$this->view->message = $tabMessage;
	}

	private function envoiMessage() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass('Zend_Filter_StripTags');

		$filter = new Zend_Filter_StripTags();
		$tabHobbit = $this->constructTabHobbit(false,trim($filter->filter(trim($this->request->get('valeur_7')))), trim($filter->filter(trim($this->request->get('valeur_8')))), null);

		$tabMessage = array(
			'titre' => $filter->filter(trim(stripslashes($this->request->get('valeur_9')))),
			'contenu' => stripslashes($this->request->get('valeur_10')),
			'expediteur' => $tabHobbit["expediteur"],
			'aff_expediteur' => $tabHobbit["aff_expediteur"],
			'destinataires' => $tabHobbit["destinataires"],
			'aff_destinataires' => $tabHobbit["aff_destinataires"],
			'aff_js_destinataires' => $tabHobbit["aff_js_destinataires"],
			'copies' => $tabHobbit["copies"],
			'aff_js_copies' => $tabHobbit["aff_js_copies"],
			'aff_copies' => $tabHobbit["aff_copies"],
		);
		$this->view->message = $tabMessage;

		$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
		$validateurCopies = new Bral_Validate_Messagerie_Destinataires(false);
		$validateurTitre = new Bral_Validate_StringLength(1, 80);
		$validateurContenu = new Bral_Validate_StringLength(1, 65000);

		$validDestinataires = $validateurDestinataires->isValid($this->view->message["destinataires"]);
		$validCopies = $validateurCopies->isValid($this->view->message["copies"]);
		$validTitre = $validateurTitre->isValid($this->view->message["titre"]);
		$validContenu = $validateurContenu->isValid($this->view->message["contenu"]);

		if (($validTitre) && ($validDestinataires) && ($validCopies) && ($validContenu)) {
			$messageTable = new Message();
			$data = array(
				'id_fk_hobbit_message' => $this->view->user->id_hobbit,
				'id_fk_type_message' => $this->view->config->messagerie->message->type->envoye,
				'date_envoi_message' => date("Y-m-d H:i:s"),
				'date_lecture_message' => null,
				'expediteur_message' => $this->view->user->id_hobbit,
				'destinataires_message' => $this->view->message["destinataires"],
				'copies_message' =>  $this->view->message["copies"],
				'titre_message' => $this->view->message["titre"],
				'contenu_message' => $this->view->message["contenu"],
			);
			
			$messageTable->insert($data);
			$idDestinatairesTab = split(',', $this->view->message["destinataires"]);
			$idEnvoye = array();
			foreach ($idDestinatairesTab as $id) {
				if (!in_array((int)$id, $idEnvoye)) {
					$data["id_fk_hobbit_message"] = (int)$id;
					$data["id_fk_type_message"] = $this->view->config->messagerie->message->type->reception;
					$messageTable->insert($data);
					$idEnvoye[] = (int)$id;
				}
			}

			$this->view->envoiMessage = true;
			$this->refreshMessages = true;
		} else {
			if (!$validDestinataires) {
				foreach ($validateurDestinataires->getMessages() as $message) {
					$destinatairesErreur[] = $message;
				}
				$this->view->destinatairesErreur = $destinatairesErreur;
			}

			if (!$validCopies) {
				foreach ($validateurCopies->getMessages() as $message) {
					$copiesErreur[] = $message;
				}
				$this->view->copiesErreur = $copiesErreur;
			}

			if (!$validTitre) {
				$this->view->titreErreur = "Le titre doit comporter entre 1 et 80 caract&egrave;res !";
			}
			$this->activerWysiwyg = true;
		}
	}
*/
}