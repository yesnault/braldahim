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
class Bral_Messagerie_Message extends Bral_Messagerie_Messagerie {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass('Bral_Util_Messagerie');
		Zend_Loader::loadClass('Bral_Util_Mail');
		Zend_Loader::loadClass('Bral_Util_Lien');
		Zend_Loader::loadClass('Bral_Helper_Messagerie');

		parent::__construct($request, $view, $action);

		$this->view->message = null;
		$this->view->information = null;
		$this->view->estQueteEvenement = false;
		$this->refreshMessages = false;
		$this->view->envoiMessage = false;
		$this->prepareAction();
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
			case "archiver" :
			case "supprimerselection" :
			case "archiverselection" :
			case "marquerlueselection" :
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
			return "Votre message est envoyé";
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
			case "archiver" :
				$this->prepareArchiver();
				break;
			case "supprimer" :
				$this->prepareSupprimer();
				break;
			case "supprimerselection" :
				$this->prepareSupprimerSelection();
				break;
			case "archiverselection" :
				$this->prepareArchiverSelection();
				break;
			case "marquerlueselection" :
				$this->prepareMarquerLueSelection();
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

		$contenu = PHP_EOL.PHP_EOL.PHP_EOL;
		$contenu .= "__________".PHP_EOL;
		$contenu .= "Message de ".$this->view->message["expediteur"]." ";
		$contenu .= "le ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y, H:i', $this->view->message["date"])." : ".PHP_EOL;
		$contenu .= $this->view->message["titre"];

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

			if ($validContacts == false || mb_strlen($this->view->message["contacts"] < 1)) {
				$avecContacts = false;
			}
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
			$messageTable = new Message();

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
				$idDestinatairesTab = array_merge($idDestinatairesTab, split(',', $this->view->message["userids"]));
				if ($idDestinatairesListe != null) {
					$idDestinatairesListe .= ",";
				}
				$idDestinatairesListe = $idDestinatairesListe . $this->view->message["userids"];
				$tabHobbits = $tabHobbits + $tabContacts["hobbits"];
			}

			if ($tabHobbits != null && count($idDestinatairesTab) > 0) {
				foreach ($idDestinatairesTab as $idHobbit) {
					$data = Bral_Util_Messagerie::prepareMessageAEnvoyer($this->view->user->id_hobbit, $idHobbit, $tabMessage["contenu"], $idDestinatairesListe);
					if (!in_array($idHobbit, $tabIdDestinatairesDejaEnvoye)) {
						$messageTable->insert($data);
						$tabIdDestinatairesDejaEnvoye[] = $idHobbit;
						if ($tabHobbits[$idHobbit]["envoi_mail_message_hobbit"] == "oui") {
							Bral_Util_Mail::envoiMailAutomatique($tabHobbits[$idHobbit], $this->view->config->mail->message->titre, $debutContenuMail.$tabMessage["contenu"], $this->view);
						}
					}
				}

				Zend_Loader::loadClass("Bral_Util_Quete");
				$this->view->estQueteEvenement = Bral_Util_Quete::etapeContacterParents($this->view->user, $idDestinatairesTab);
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

	private function prepareMessage() {
		$messageTable = new Message();
		$message = $messageTable->findById($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));

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
					$expediteur = Bral_Util_Lien::getJsHobbit($tabHobbits[$message["fromid"]]["id_hobbit"], $tabHobbits[$message["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["fromid"]]["nom_hobbit"]. " (".$tabHobbits[$message["fromid"]]["id_hobbit"].")");
				} else {
					$expediteur = " Erreur ".$message["fromid"];
				}

				if (array_key_exists($message["toid"], $tabHobbits)) {
					$destinataire = Bral_Util_Lien::getJsHobbit($tabHobbits[$message["toid"]]["id_hobbit"], $tabHobbits[$message["toid"]]["prenom_hobbit"] . " ". $tabHobbits[$message["toid"]]["nom_hobbit"]. " (".$tabHobbits[$message["toid"]]["id_hobbit"].")");
				} else {
					$destinataire = " Erreur Hobbit n°".$message["toid"];
				}

				$tabDestinataires = split(',', $message["toids"]);
				foreach ($tabDestinataires as $k=>$d) {
					if (array_key_exists($d, $tabHobbits)) {
						$destinataires .= Bral_Util_Lien::getJsHobbit($tabHobbits[$d]["id_hobbit"], $tabHobbits[$d]["prenom_hobbit"] . " ". $tabHobbits[$d]["nom_hobbit"]. " (".$tabHobbits[$d]["id_hobbit"].")");
						$destinataires .= ", ";
					} else {
						$destinataires .= " Erreur Hobbit n°:".$d.", ";
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
				"date" => $message["date_message"],
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
				$messageTable->update($data, $where);
			}
			unset($messageTable);
			unset($message);
		} else {
			throw new Zend_Exception(get_class($this)."::prepareMessage Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		$this->view->message = $tabMessage;
	}

	private function prepareSupprimer() {
		$listMessages[] = intval($this->request->get("valeur_2"));
		$this->prepareSupprimerSelection($listMessages);
	}

	private function prepareSupprimerSelection($listMessages = null) {
		$messageTable = new Message();
		if ($listMessages == null) {
			$listMessages = split(',', $this->request->get("valeur_2"));
		}
		$messages = $messageTable->findByIdList($this->view->user->id_hobbit, $listMessages);

		if ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["fromid"] == $this->view->user->id_hobbit) {
					$data = array(
						"totrashoutbox" => 1,
					);
					if ($message["toid"] == $this->view->user->id_hobbit) {
						$data["totrash"] = 1;

					}
				} else {
					$data = array(
						"totrash" => 1,
					);
				}
				$where = "id=".$message["id"];
				$messageTable->update($data, $where);

				$this->refreshMessages = true;
			}
			if (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont supprimés.";
			} else {
				$this->view->information = "Le message sélectionné est supprimé.";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::supprimerselection Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		unset($messageTable);
		unset($message);
	}

	private function prepareArchiver() {
		$listMessages[] = intval($this->request->get("valeur_2"));
		$this->prepareArchiverSelection($listMessages);
	}

	private function prepareArchiverSelection($listMessages = null) {
		$messageTable = new Message();
		$nbDejaArchives = $messageTable->countByToIdArchived($this->view->user->id_hobbit);

		if ($listMessages == null) {
			$listMessages = split(',', $this->request->get("valeur_2"));
		}
		$messages = $messageTable->findByIdList($this->view->user->id_hobbit, $listMessages);

		$s = "";
		if ($nbDejaArchives >= 1) {
			$s = 's';
		}
		if ($nbDejaArchives >= 200) {
			$this->view->information = "Vous avez ".$nbDejaArchives. " messages archivés.";
			$this->view->information .= " Vous ne pouvez pas archiver plus de message, le message n'est donc pas archivé.";
		} elseif ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["toid"] == $this->view->user->id_hobbit) {
					$data = array("archived" => 1);
					$where = "id=".$message["id"];
					$messageTable->update($data, $where);
				}
				$this->refreshMessages = true;
			}
			if (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont archivés. Vous avez ".($nbDejaArchives + 1). " message$s archivé$s.";
			} else {
				$this->view->information = "Le message sélectionné est archivé. Vous avez ".($nbDejaArchives + 1)." message$s archivé$s.";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::archiverselection Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		unset($messageTable);
		unset($message);
	}

	private function prepareMarquerLueSelection() {
		$messageTable = new Message();
		$listMessages = split(',', $this->request->get("valeur_2"));
		$messages = $messageTable->findByIdList($this->view->user->id_hobbit, $listMessages);

		if ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["toid"] == $this->view->user->id_hobbit) {
					$data = array("toread" => 1);
					$where = "id=".$message["id"];
					$messageTable->update($data, $where);
				}
				$this->refreshMessages = true;
			}
			if (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont marqués comme lus.";
			} else {
				$this->view->information = "Le message sélectionné est marqué comme lu.";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::marquerlueselection Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
		unset($messageTable);
		unset($message);
	}

}