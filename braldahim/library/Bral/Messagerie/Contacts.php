<?php

class Bral_Messagerie_Contacts {

	function __construct($request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->view->information = null;
		$this->prepareAction();
	}

	public function getNomInterne() {
		return "messagerie_contenu";
	}

	function render() {
		switch($this->request->get("valeur_1")) {
			case "editer" :
			case "nouveau" :
				return $this->view->render("messagerie/contact.phtml");
				break;
			case "supprimer" :
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
			case "editer" :	
				$this->prepareRepondre();
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
		Zend_Loader::loadClass('JosUserlists');
		
		$josUserlistsTable = new JosUserlists();
		$listesContacts = $josUserlistsTable->findByUserId($this->view->user->id_fk_jos_users_hobbit);
		
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
		$this->view->listesContacts = $tabListes;
		
	}
	
	private function prepareNouveau() {
		$this->view->listesContacts = null;
	}

}