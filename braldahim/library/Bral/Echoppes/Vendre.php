<?php

class Bral_Echoppes_Vendre extends Bral_Echoppes_Echoppe {

	function __construct($nomSystemeAction, $request, $view, $action, $id_echoppe = false) {
		if ($id_echoppe !== false) {
			$this->idEchoppe = $id_echoppe;
		}
		parent::__construct($nomSystemeAction, $request, $view, $action);
	}
	
	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeUnite");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartiePlante");
		
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
		
		$tabEquipementsArriereBoutique = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppeArriereBoutique($id_echoppe);

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				if ($e["type_vente_echoppe_equipement"] == "aucune") {
					$tabEquipementsArriereBoutique[] = array(
					"id_echoppe_equipement" => $e["id_echoppe_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"]
					);
				}
			}
		}
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;
		$this->view->nbEquipementsArriereBoutique = count($tabEquipementsArriereBoutique);
		
		$typeUniteTable = new TypeUnite();
		$typeUniteRowset = $typeUniteTable->fetchall(null, "nom_type_unite");
		$typeUniteRowset = $typeUniteRowset->toArray();
		
		foreach($typeUniteRowset as $t) {
			$unites[] = array("nom_systeme_type_unite" => $t["nom_systeme_type_unite"] ,
							  "nom_type_unite" => $t["nom_type_unite"]);
		}
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchall(null, "nom_type_minerai");
		$typeMineraiRowset = $typeMineraiRowset->toArray();
		
		foreach($typeMineraiRowset as $t) {
			$unites[] = array("nom_systeme_type_unite" => "minerai:".$t["nom_systeme_type_minerai"] ,
							  "nom_type_unite" => "Minerai : ".$t["nom_type_minerai"]);
		}
		
		$typePartiePlanteTable = new TypePartiePlante();
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
			$unites[] = array("nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante1_type_plante"]]["nom_partieplante"] );
			if ($t["id_fk_partieplante2_type_plante"] != "") {
			$unites[] = array("nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante2_type_plante"]]["nom_partieplante"] );
				
			}
			if ($t["id_fk_partieplante3_type_plante"] != "") {
			$unites[] = array("nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante3_type_plante"]]["nom_partieplante"] );
				
			}
			if ($t["id_fk_partieplante4_type_plante"] != "") {
			$unites[] = array("nom_systeme_type_unite" => "plante:".$t["nom_systeme_type_plante"] ,
							  "nom_type_unite" => "Plante : ".$t["nom_type_plante"]. ' '.$partiePlante[$t["id_fk_partieplante4_type_plante"]]["nom_partieplante"] );
				
			}
		}
		
		$this->view->unites = $unites;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
}