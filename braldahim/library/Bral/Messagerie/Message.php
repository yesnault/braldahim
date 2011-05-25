<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
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
			case "marquerlu" :
			case "message" :
				$this->setNomInterne("messagerie_message");
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

		Bral_Util_Messagerie::preparePage($this->request, $this->view);

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
				$this->prepareSupprimer($this->view->filtre);
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
			case "marquerlu" :
				$this->prepareMarquelu($this->view->filtre);
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

		$tabBraldun["destinataires"] = "";
		$tabBraldun["aff_js_destinataires"] = "";
		if ($this->request->get('valeur_2') != "") {
			$tabBraldun = Bral_Util_Messagerie::constructTabBraldun($filter->filter(trim($this->request->get('valeur_2'))));
		}

		$tabContacts["contacts"] = "";
		$tabContacts["aff_js_contacts"] = "";
		$tabContacts["userids"] = "";
		if ($this->request->get('valeur_4') != "") {
			$tabContacts = Bral_Util_Messagerie::constructTabContacts($filter->filter(trim($this->request->get('valeur_4'))), $this->view->user->id_braldun);
		}

		$tabMessage = array(
			'contenu' => "",
			'destinataires' => $tabBraldun["destinataires"],
			'aff_js_destinataires' => $tabBraldun["aff_js_destinataires"],
			"contacts" => $tabContacts["contacts"],
			"aff_js_contacts" => $tabContacts["aff_js_contacts"],
			"userids" => $tabContacts["userids"],
		);
		$this->view->message = $tabMessage;
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_braldun);
	}

	private function prepareRepondre($transferer, $repondretous) {
		$this->prepareMessage();
		if ($transferer == false) {
			$listeId = $this->view->message["fromid"].",";
			if ($repondretous) {
				$listeId = $listeId.$this->view->message["toids"].",";
				$tabBraldun = Bral_Util_Messagerie::constructTabBraldun($listeId, "valeur_2", $this->view->user->id_braldun);
			} else {
				$tabBraldun = Bral_Util_Messagerie::constructTabBraldun($listeId);
			}
		} else {
			$tabBraldun = array("destinataires" => "",
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
			"destinataires" => $tabBraldun["destinataires"],
			"aff_js_destinataires" => $tabBraldun["aff_js_destinataires"],
			"contacts" => "",
			"aff_js_contacts" => "",
		);
		$this->view->message = $tabMessage;
		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_braldun);
	}

	private function envoiMessage() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Contacts");
		Zend_Loader::loadClass("Zend_Filter_StripTags");
		Zend_Loader::loadClass("Bral_Util_BBParser");

		$this->view->listesContacts = Bral_Util_Messagerie::prepareListe($this->view->user->id_braldun);

		$filter = new Zend_Filter_StripTags();
		$tabBraldun = Bral_Util_Messagerie::constructTabBraldun($filter->filter(trim($this->request->get('valeur_2'))));
		$tabContacts = Bral_Util_Messagerie::constructTabContacts($filter->filter(trim($this->request->get('valeur_4'))), $this->view->user->id_braldun);

		$texte = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_3')));
		$texte = str_replace("<br />", PHP_EOL, $texte);

		$tabMessage = array (
			'contenu' => $texte,
			'destinataires' => $tabBraldun["destinataires"],
			'aff_js_destinataires' => $tabBraldun["aff_js_destinataires"],
			"contacts" => $tabContacts["contacts"],
			"aff_js_contacts" => $tabContacts["aff_js_contacts"],
			"userids" => $tabContacts["userids"],
		);
		$this->view->message = $tabMessage;

		if ($this->view->listesContacts != null) {
			$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(false);
			$validateurContacts = new Bral_Validate_Messagerie_Contacts(false, $this->view->user->id_braldun);
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

		$validateurContenu = new Bral_Validate_StringLength(1, 4500);
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

			$debutContenuMail = "Message de ".$this->view->user->prenom_braldun." ".$this->view->user->nom_braldun." (".$this->view->user->id_braldun.") : ";

			$tabIdDestinatairesDejaEnvoye = array();
			$tabBralduns = array();
			$idDestinatairesTab = array();
			$idDestinatairesListe = null;

			if ($this->view->message["destinataires"] != "") {
				$idDestinatairesTab = preg_split("/,/", $this->view->message["destinataires"]);
				$idDestinatairesListe = $this->view->message["destinataires"];
				$tabBralduns = $tabBraldun["bralduns"];
			}

			if ($this->view->message["userids"] != "") {
				$idDestinatairesTab = array_merge($idDestinatairesTab, preg_split("/,/", $this->view->message["userids"]));
				if ($idDestinatairesListe != null) {
					$idDestinatairesListe .= ",";
				}
				$idDestinatairesListe = $idDestinatairesListe . $this->view->message["userids"];
				$tabBralduns = $tabBralduns + $tabContacts["bralduns"];
			}

			if ($tabBralduns != null && count($idDestinatairesTab) > 0) {
				foreach ($idDestinatairesTab as $idBraldun) {
					$data = Bral_Util_Messagerie::prepareMessageAEnvoyer($this->view->user->id_braldun, $idBraldun, $tabMessage["contenu"], $idDestinatairesListe);
					if (!in_array($idBraldun, $tabIdDestinatairesDejaEnvoye)) {
						$messageTable->insert($data);
						$tabIdDestinatairesDejaEnvoye[] = $idBraldun;
						if ($tabBralduns[$idBraldun]["envoi_mail_message_braldun"] == "oui") {
							$texte = $debutContenuMail;
							$texte .= Bral_Util_BBParser::bbcodeStrip($tabMessage["contenu"]);
							Bral_Util_Mail::envoiMailAutomatique($tabBralduns[$idBraldun], $this->view->config->mail->message->titre, $texte, $this->view);
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
		$message = $messageTable->findById($this->view->user->id_braldun, (int)$this->request->get("valeur_2"));

		$tabMessage = null;
		if ($message != null && count($message) == 1) {
			$message = $message[0];

			$idsBraldun[] = $message["toid"];
			$idsBraldun[] = $message["fromid"];
			$idsBraldun = array_merge($idsBraldun, preg_split("/,/", $message["toids"]));
			if ($idsBraldun != null) {
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->findByIdList($idsBraldun);
				if ($bralduns != null) {
					foreach($bralduns as $h) {
						$tabBralduns[$h["id_braldun"]] = $h;
					}
				}
			}

			$expediteur = "";
			$destinataire = "";
			$destinataires = "";
			if ($tabBralduns != null) {
				if (array_key_exists($message["fromid"], $tabBralduns)) {
					$expediteur = Bral_Util_Lien::getJsBraldun($tabBralduns[$message["fromid"]]["id_braldun"], $tabBralduns[$message["fromid"]]["prenom_braldun"] . " ". $tabBralduns[$message["fromid"]]["nom_braldun"]. " (".$tabBralduns[$message["fromid"]]["id_braldun"].")");
				} else {
					$expediteur = " Erreur ".$message["fromid"];
				}

				if (array_key_exists($message["toid"], $tabBralduns)) {
					$destinataire = Bral_Util_Lien::getJsBraldun($tabBralduns[$message["toid"]]["id_braldun"], $tabBralduns[$message["toid"]]["prenom_braldun"] . " ". $tabBralduns[$message["toid"]]["nom_braldun"]. " (".$tabBralduns[$message["toid"]]["id_braldun"].")");
				} else {
					$destinataire = " Erreur Braldûn n°".$message["toid"];
				}

				$tabDestinataires = preg_split("/,/", $message["toids"]);
				foreach ($tabDestinataires as $k=>$d) {
					if (array_key_exists($d, $tabBralduns)) {
						$destinataires .= Bral_Util_Lien::getJsBraldun($tabBralduns[$d]["id_braldun"], $tabBralduns[$d]["prenom_braldun"] . " ". $tabBralduns[$d]["nom_braldun"]. " (".$tabBralduns[$d]["id_braldun"].")");
						$destinataires .= ", ";
					} else {
						$destinataires .= " Erreur Braldûn n°:".$d.", ";
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
			if ($message["toid"] == $this->view->user->id_braldun && $message["toread"] == 0) {
				$data = array(
					"toread" => 1,
				);
				$where = "id=".$message["id"];
				$messageTable->update($data, $where);
			}
			unset($messageTable);
			unset($message);
		} else {
			throw new Zend_Exception(get_class($this)."::prepareMessage Message invalide : idbraldun=".$this->view->user->id_braldun." val=".$this->request->get("valeur_2"));
		}
		$this->view->message = $tabMessage;
	}

	private function prepareSupprimer($filtre = null) {
		if ($this->request->get("valeur_2") == "all" && $filtre != null) {
			$listMessages = Bral_Util_Messagerie::preparesListAllMessages($this->view->user->id_braldun, $filtre);
		} else {
			$listMessages[] = intval($this->request->get("valeur_2"));
		}

		$this->prepareSupprimerSelection($listMessages);
	}

	private function prepareMarquelu($filtre = null) {
		if ($this->request->get("valeur_2") == "all" && $filtre != null) {
			$listMessages = Bral_Util_Messagerie::preparesListAllMessages($this->view->user->id_braldun, $filtre);
		} else {
			$listMessages[] = intval($this->request->get("valeur_2"));
		}

		$this->prepareMarquerLueSelection($listMessages);
	}

	private function prepareSupprimerSelection($listMessages = null) {
		$messageTable = new Message();
		if ($listMessages == null) {
			$listMessages = preg_split("/,/", $this->request->get("valeur_2"));
		}

		$messages = $messageTable->findByIdList($this->view->user->id_braldun, $listMessages);

		if ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["fromid"] == $this->view->user->id_braldun) {
					$data = array(
						"totrashoutbox" => 1,
					);
					if ($message["toid"] == $this->view->user->id_braldun) {
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

			if ($this->request->get("valeur_2") == "all") {
				$this->view->information = "Tous les messages sont supprimés.";
			} elseif (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont supprimés.";
			} else {
				$this->view->information = "Le message sélectionné est supprimé.";
			}
		} else {
			throw new Zend_Exception(get_class($this)."::supprimerselection Message invalide : idbraldun=".$this->view->user->id_braldun." val=".$this->request->get("valeur_2"));
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
		$nbDejaArchives = $messageTable->countByToIdArchived($this->view->user->id_braldun);

		if ($listMessages == null) {
			$listMessages = preg_split("/,/", $this->request->get("valeur_2"));
		}
		$messages = $messageTable->findByIdList($this->view->user->id_braldun, $listMessages);

		$s = "";
		if ($nbDejaArchives >= 1) {
			$s = 's';
		}
		if ($nbDejaArchives >= 200) {
			$this->view->information = "Vous avez ".$nbDejaArchives. " messages archivés.";
			$this->view->information .= " Vous ne pouvez pas archiver plus de message, le message n'est donc pas archivé.";
		} elseif ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["toid"] == $this->view->user->id_braldun) {
					$data = array("archived" => 1);
					$where = "id=".$message["id"];
					$messageTable->update($data, $where);
				}
				$this->refreshMessages = true;
				$nbDejaArchives++;
			}
			$s = "";
			if ($nbDejaArchives > 1) {
				$s = 's';
			}

			if ($this->request->get("valeur_2") == "all") {
				$this->view->information = "Tous les messages sont marqués comme lus.";
			} elseif (count($messages) > 1) {
				$this->view->information = "Les messages sélectionnés sont archivés. Vous avez ".$nbDejaArchives. " message$s archivé$s.";
			} else {
				$this->view->information = "Le message sélectionné est archivé. Vous avez ".$nbDejaArchives." message$s archivé$s.";
			}
		} else {
			//throw new Zend_Exception(get_class($this)."::archiverselection Message invalide : idbraldun=".$this->view->user->id_braldun." val=".$this->request->get("valeur_2"));
			$this->view->information = "Rafraîchissez la liste.";
		}
		unset($messageTable);
		unset($message);
	}

	private function prepareMarquerLueSelection($listMessages = null) {
		$messageTable = new Message();

		if ($listMessages == null) {
			$listMessages = preg_split("/,/", $this->request->get("valeur_2"));
		}

		$messages = $messageTable->findByIdList($this->view->user->id_braldun, $listMessages);

		if ($messages != null && count($messages) >= 1) {
			foreach ($messages as $message) {
				if ($message["toid"] == $this->view->user->id_braldun) {
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
			throw new Zend_Exception(get_class($this)."::marquerlueselection Message invalide : idbraldun=".$this->view->user->id_braldun." val=".$this->request->get("valeur_2"));
		}
		unset($messageTable);
		unset($message);
	}

}