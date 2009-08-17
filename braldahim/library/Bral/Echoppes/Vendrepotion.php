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
class Bral_Echoppes_Vendrepotion extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeUnite");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		
		$id_echoppe = $this->request->get("valeur_1");
		
		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}
		
		// on verifie que c'est bien l'echoppe du joueur
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$echoppeOk = false;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				$echoppeOk = true;
				break;
			}
		}
		
		if ($echoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Echoppe interdite=".$id_echoppe);
		}
		
		$tabPotionsArriereBoutique = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($id_echoppe);

		if (count($potions) > 0) {
			Zend_Loader::loadClass("Bral_Util_Potion");
			foreach($potions as $e) {
				if ($e["type_vente_echoppe_potion"] == "aucune") {
					$tabPotionsArriereBoutique[] = array(
					"id_echoppe_potion" => $e["id_echoppe_potion"],
					"nom" => $e["nom_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($e["type_potion"]),
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_echoppe_potion"],
					"commentaire" => $e["commentaire_vente_echoppe_potion"],
					);
				}
			}
		}
		$this->view->potionsArriereBoutique = $tabPotionsArriereBoutique;
		$this->view->nbPotionsArriereBoutique = count($tabPotionsArriereBoutique);
			
		if ($this->view->nbPotionsArriereBoutique > 0) {
			$this->view->vendreOk = true;
		} else {
			$this->view->vendreOk = false;
			return;
		}
		
		$typeUniteTable = new TypeUnite();
		$typeUniteRowset = $typeUniteTable->fetchall(null, "nom_type_unite");
		$typeUniteRowset = $typeUniteRowset->toArray();
		
		foreach($typeUniteRowset as $t) {
			$unites[$t["nom_systeme_type_unite"]] = array("id_type_unite" => $t["id_type_unite"] ,
							  "nom_type_unite" => $t["nom_type_unite"]);
		}
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchall(null, "nom_type_minerai");
		$typeMineraiRowset = $typeMineraiRowset->toArray();
		
		foreach($typeMineraiRowset as $t) {
			$unites["minerai:".$t["id_type_minerai"]] = array("id_type_minerai" => $t["id_type_minerai"],
							  "nom_type_unite" => "Minerai : ".$t["nom_type_minerai"]);
		}
		
		$typePartiePlanteTable = new TypePartieplante();
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall(null, "nom_type_partieplante");
		$typePartiePlanteRowset = $typePartiePlanteRowset->toArray();
		foreach($typePartiePlanteRowset as $t) {
			$partiePlante[$t["id_type_partieplante"]] = array("nom_partieplante" => $t["nom_type_partieplante"],
															  "nom_systeme_partieplante" => $t["nom_systeme_type_partieplante"]);
		}
		
		$typePlanteTable = new TypePlante();
		$typePlanteRowset = $typePlanteTable->fetchall(null, "nom_type_plante");
		$typePlanteRowset = $typePlanteRowset->toArray();
		
		foreach($typePlanteRowset as $t) {
			$unites["plante:".$t["id_type_plante"]."|".$t["id_fk_partieplante1_type_plante"]] = array("id_type_plante" =>  $t["id_type_plante"],
							  "id_type_partieplante" => $t["id_fk_partieplante1_type_plante"],
							  "nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante1_type_plante"]]["nom_partieplante"] );
			
			if ($t["id_fk_partieplante2_type_plante"] != "") {
				$unites["plante:".$t["id_type_plante"]."|".$t["id_fk_partieplante2_type_plante"]] = array("id_type_plante" =>  $t["id_type_plante"],
							  "id_type_partieplante" => $t["id_fk_partieplante2_type_plante"],
							  "nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante2_type_plante"]]["nom_partieplante"] );
				
			}
			
			if ($t["id_fk_partieplante3_type_plante"] != "") {
				$unites["plante:".$t["id_type_plante"]."|".$t["id_fk_partieplante3_type_plante"]] = array("id_type_plante" =>  $t["id_type_plante"],
							  "id_type_partieplante" => $t["id_fk_partieplante3_type_plante"],
							  "nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante3_type_plante"]]["nom_partieplante"] );
				
			}
			
			if ($t["id_fk_partieplante4_type_plante"] != "") {
				$unites["plante:".$t["id_type_plante"]."|".$t["id_fk_partieplante4_type_plante"]] = array("id_type_plante" =>  $t["id_type_plante"],
							  "id_type_partieplante" => $t["id_fk_partieplante4_type_plante"],
							  "nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante4_type_plante"]]["nom_partieplante"] );
				
			}
		}
		
		$this->view->unites = $unites;
		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendreOk == false) {
			throw new Zend_Exception(get_class($this)." Vendre Potion interdit");
		}
		
		$id_potion = $this->request->get("valeur_2");
		
		if ((int) $id_potion."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Potion invalide=".$id_potion);
		} else {
			$id_potion = (int)$id_potion;
		}
		
		$potionOk = false;
		foreach($this->view->potionsArriereBoutique as $e) {
			if ($e["id_echoppe_potion"] == $id_potion) {
				$potionOk = true;
				$this->view->potion = $e;
				break;
			}
		}

		if ($potionOk == false) {
			throw new Zend_Exception(get_class($this)." Potion inconnu=".$id_potion);
		} 
		
		$prix_1 = $this->request->get("valeur_3");
		$unite_1 = $this->request->get("valeur_4");
		$prix_2 = $this->request->get("valeur_5");
		$unite_2 = $this->request->get("valeur_6");
		$prix_3 = $this->request->get("valeur_7");
		$unite_3 = $this->request->get("valeur_8");
		
		if ((int) $prix_1."" != $this->request->get("valeur_3")."") {
			throw new Zend_Exception(get_class($this)." prix 1 invalide=".$id_potion);
		} else {
			$prix_1 = (int)$prix_1;
		}
		if ((int) $prix_2."" != $this->request->get("valeur_5")."") {
			$prix_2 = null;
			$unite_2 = null;
		} else {
			$prix_2 = (int)$prix_2;
		}
		if ((int) $prix_3."" != $this->request->get("valeur_7")."") {
			$prix_3 = null;
			$unite_3 = null;
		} else {
			$prix_3 = (int)$prix_3;
		}
		
		$this->calculPrixEchoppe($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3);
		$this->calculPrixMinerai($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3);
		$this->calculPrixPartiePlante($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3);
	}
	
	private function calculPrixEchoppe($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3) {
		$unite_1_ok = false;
		$unite_2_ok = false;
		$unite_3_ok = false;
		$unite_1_echoppe = null;
		$unite_2_echoppe = null;
		$unite_3_echoppe = null;
		$prix_1_echoppe = null;
		$prix_2_echoppe = null;
		$prix_3_echoppe = null;
		foreach($this->view->unites as $k => $u) {
			if ($unite_1 == $k && mb_substr($unite_1, 0, 6) != "plante" && mb_substr($unite_1, 0, 7) != "minerai") {
				$prix_1_echoppe = $prix_1;
				$unite_1_echoppe = $u["id_type_unite"];
				$unite_1_ok = true;
			}
			if ($unite_2 == $k && mb_substr($unite_2, 0, 6) != "plante" && mb_substr($unite_2, 0, 7) != "minerai") {
				$prix_2_echoppe = $prix_2;
				$unite_2_echoppe = $u["id_type_unite"];
				$unite_2_ok = true;
			}
			if ($unite_3 == $k && mb_substr($unite_3, 0, 6) != "plante" && mb_substr($unite_3, 0, 7) != "minerai") {
				$prix_3_echoppe = $prix_3;
				$unite_3_echoppe = $u["id_type_unite"];
				$unite_3_ok = true;
			}
		}
		
		$commentaire = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_9')));
		
		Zend_Loader::loadClass("EchoppePotion");
		$data = array("prix_1_vente_echoppe_potion" => $prix_1_echoppe,
					  "prix_2_vente_echoppe_potion" => $prix_2_echoppe,
					  "prix_3_vente_echoppe_potion" => $prix_3_echoppe,
					  "unite_1_vente_echoppe_potion" => $unite_1_echoppe,
					  "unite_2_vente_echoppe_potion" => $unite_2_echoppe,
					  "unite_3_vente_echoppe_potion" => $unite_3_echoppe,
					  "type_vente_echoppe_potion" => "publique",
					  "commentaire_vente_echoppe_potion" => $commentaire,
					  "date_echoppe_potion" => date("Y-m-d H:i:s"),
					);
		
		$where = "id_echoppe_potion=".$id_potion; 
		$echoppePotionTable = new EchoppePotion();
		$echoppePotionTable->update($data, $where);
	}
	
	private function calculPrixMinerai($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3) {
		Zend_Loader::loadClass("EchoppePotionMinerai");
		$echoppePotionMineraiTable = new EchoppePotionMinerai();
		
		foreach($this->view->unites as $k => $u) {
			if ($unite_1 == $k && mb_substr($unite_1, 0, 7) == "minerai") {
				$data = array("prix_echoppe_potion_minerai" => $prix_1,
							  "id_fk_type_echoppe_potion_minerai" => $u["id_type_minerai"],
							  "id_fk_echoppe_potion_minerai" => $id_potion);
				$echoppePotionMineraiTable->insertOrUpdate($data);
			}
			if ($unite_2 == $k && mb_substr($unite_2, 0, 7) == "minerai") {
				$data = array("prix_echoppe_potion_minerai" => $prix_2,
							  "id_fk_type_echoppe_potion_minerai" => $u["id_type_minerai"],
							  "id_fk_echoppe_potion_minerai" => $id_potion);
				$echoppePotionMineraiTable->insertOrUpdate($data);
			}
			if ($unite_3 == $k && mb_substr($unite_3, 0, 7) == "minerai") {
				$data = array("prix_echoppe_potion_minerai" => $prix_3,
							  "id_fk_type_echoppe_potion_minerai" => $u["id_type_minerai"],
							  "id_fk_echoppe_potion_minerai" => $id_potion);
				$echoppePotionMineraiTable->insertOrUpdate($data);
			}
		}
	}

	private function calculPrixPartiePlante($id_potion, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3) {
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
		
		foreach($this->view->unites as $k => $u) {
			if ($unite_1 == $k && mb_substr($unite_1, 0, 6) == "plante") {
				$data = array("prix_echoppe_potion_partieplante" => $prix_1,
							  "id_fk_type_echoppe_potion_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_echoppe_potion_partieplante" => $u["id_type_plante"],
							  "id_fk_echoppe_potion_partieplante" => $id_potion);
				$echoppePotionPartiePlanteTable->insertOrUpdate($data);
			}
			if ($unite_2 == $k && mb_substr($unite_2, 0, 6) == "plante") {
				$data = array("prix_echoppe_potion_partieplante" => $prix_2,
							  "id_fk_type_echoppe_potion_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_echoppe_potion_partieplante" => $u["id_type_plante"],
							  "id_fk_echoppe_potion_partieplante" => $id_potion);
				$echoppePotionPartiePlanteTable->insertOrUpdate($data);
			}
			if ($unite_3 == $k && mb_substr($unite_3, 0, 6) == "plante") {
				$data = array("prix_echoppe_potion_partieplante" => $prix_3,
							  "id_fk_type_echoppe_potion_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_echoppe_potion_partieplante" => $u["id_type_plante"],
							  "id_fk_echoppe_potion_partieplante" => $id_potion);
				$echoppePotionPartiePlanteTable->insertOrUpdate($data);
			}
		}
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	function getListBoxRefresh() {
		return array("box_echoppe", "box_echoppes");
	}
}