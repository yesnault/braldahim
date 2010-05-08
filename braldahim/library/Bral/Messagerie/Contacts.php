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
class Bral_Messagerie_Contacts extends Bral_Messagerie_Messagerie {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass('Bral_Util_Messagerie');
		Zend_Loader::loadClass('MessagerieContacts');

		parent::__construct($request, $view, $action);

		$this->view->information = null;
		$this->prepareAction();
	}

	function render() {
		switch($this->request->get("valeur_1")) {
			case "editer" :
			case "voir" :
			case "nouveau" :
			case "supprimer" :
				return $this->view->render("messagerie/contact.phtml");
				break;
			case "liste" :
				return $this->view->render("messagerie/contacts.phtml");
				break;
			default :
				throw new Zend_Exception(get_class($this)."::render invalide :".$this->request->get("valeur_1"));
		}
	}

	private function prepareAction() {
		$this->view->valeur_1 = $this->request->get("valeur_1");

		switch($this->request->get("valeur_1")) {
			case "nouveau" :
				$this->prepareNouveau();
				break;
			case "voir" :
				$this->prepareVoir();
				break;
			case "editer" :
				$this->prepareEditer();
				break;
			case "supprimer" :
				$this->prepareSupprimer();
				break;
			case "liste" :
				$this->prepareListe();
				break;
			default :
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->request->get("valeur_1"));
		}
	}

	private function prepareListe() {
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_braldun, true);
	}

	private function prepareNouveau() {
		$contactsListe = array("id" => -1,
			"nom" => "",
			"description" => "" ,
			'destinataires' => "",
			'aff_js_destinataires' => "",
		);

		$this->view->contactsListe = $contactsListe;
	}

	private function prepareVoir() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass('Zend_Filter_StripTags');

		Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_2'));
		$filter = new Zend_Filter_StripTags();
		$id = $filter->filter(trim($this->request->get('valeur_2')));
		$this->view->contactsListe = $this->prepareContactsListe($id);
	}

	private function prepareEditer() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass('Zend_Filter_StripTags');

		$erreur = false;

		$filter = new Zend_Filter_StripTags();

		$messagerieContactsTable = new MessagerieContacts();
		Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_2'));
		$id = $filter->filter(trim($this->request->get('valeur_2')));

		$destinataires = $filter->filter(trim($this->request->get('valeur_3')));
			
		$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
		$validDestinataires = $validateurDestinataires->isValid($destinataires);
		$destinataires = Bral_Util_Messagerie::constructTabBraldun($destinataires, "valeur_3");

		if ($validDestinataires) {
			$nom = $filter->filter(trim($this->request->get('valeur_4')));
			$nom = Bral_Util_String::stripNonValideStrict($nom);
			$description = stripslashes($filter->filter(trim($this->request->get('valeur_5'))));
			$validateurNom = new Bral_Validate_StringLength(1, 40);
			$validateurDescription = new Bral_Validate_StringLength(1, 200);
			if (count($destinataires["bralduns"]) == 1) {
				$braldun = current($destinataires["bralduns"]);
				$nom = $braldun["prenom_braldun"]. " ". $braldun["nom_braldun"];
				if ($description == null || $description == "") {
					$description = "Contact unique : ".$nom;
				}
			}
				
			$validNom = $validateurNom->isValid($nom);
			$validDescription = $validateurDescription->isValid($description);
		} else {
			$validNom = true;
			$validDescription = true;
		}

		if ($validDestinataires && $validNom && $validDescription) {
			$data = array(
				"userid" => $this->view->user->id_braldun,
				"name" => $nom,
				"description" => $description,
				"userids" => $destinataires["destinataires"],
			);

			if ($id == -1) { // nouveau
				$id = $messagerieContactsTable->insert($data);
				$this->view->information = "La liste ". $nom ." est créée";
			} else { // update
				$where = " userid = ". $this->view->user->id_braldun; // secu
				$where .= " AND id=".intval($id);
				$messagerieContactsTable->update($data, $where);
				$this->view->information = "La liste ". $nom ." est modifiée";
			}
		} else {
			$erreur = true;
		}

		if ($erreur) {
			if (!$validNom) {
				foreach ($validateurNom->getMessages() as $message) {
					$nomErreur[] = $message;
				}
				$this->view->nomErreur = $nomErreur;
			}

			if (!$validDescription) {
				foreach ($validateurDescription->getMessages() as $message) {
					$descriptionErreur[] = $message;
				}
				$this->view->descriptionErreur = $descriptionErreur;
			}

			if (!$validDestinataires) {
				foreach ($validateurDestinataires->getMessages() as $message) {
					$destinatairesErreur[] = $message;
				}
				$this->view->destinatairesErreur = $destinatairesErreur;
			}

			$contactsListe = array("id" => -1,
				"nom" => $nom,
				"description" => $description ,
				'destinataires' => $destinataires["destinataires"],
				'aff_js_destinataires' => $destinataires["aff_js_destinataires"],
			);

			$this->view->contactsListe = $contactsListe;
			return;
		} else {
			$this->view->contactsListe = $this->prepareContactsListe($id);
		}
	}

	private function prepareContactsListe($id) {
		$messagerieContactsTable = new MessagerieContacts();
		$rowset = $messagerieContactsTable->findByIdList($id, $this->view->user->id_braldun);
		if ($rowset == null) {
			throw new Zend_Exception("Bral_Messagerie_Contacts::prepareEditer Valeur invalide : id=".$id. " id2=".$this->view->user->id_braldun);
		}

		$rowset = $rowset->toArray();

		$destinataires = Bral_Util_Messagerie::constructTabBraldun($rowset["userids"], "valeur_3");

		$contactsListe = array(
			"id" => $rowset["id"],
			"nom" => $rowset["name"],
			"description" => $rowset["description"],
			'destinataires' => $destinataires["destinataires"],
			'aff_js_destinataires' => $destinataires["aff_js_destinataires"],
		);

		return $contactsListe;
	}

	private function prepareSupprimer() {
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();

		$messagerieContactsTable = new MessagerieContacts();
		Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_2'));
		$id = $filter->filter(trim($this->request->get('valeur_2')));

		$where = " userid = ". $this->view->user->id_braldun; // secu
		$where .= " AND id=".intval($id);
		$messagerieContactsTable->delete($where);
		$this->view->information = "La liste est supprimée";
	}
}