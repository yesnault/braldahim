<?php

class Bral_Echoppes_Voir extends Bral_Echoppes_Echoppe {
	
	function getNomInterne() {
		return "box_echoppe";
	}
	function render() {
		return $this->view->render("echoppes/voir.phtml");
	}
	function prepareCommun() {
		$id_echoppe = (int)$this->request->get("valeur_1");
		
		Zend_Loader::loadClass("Echoppe");
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				if ($this->view->user->sexe_hobbit == 'feminin') {
					$nom_metier = $e["nom_feminin_metier"];
				} else {
					$nom_metier = $e["nom_masculin_metier"];
				}
				$tabEchoppe = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'nom_metier' => $nom_metier,
				'nom_region' => $e["nom_region"],
				);				
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}
		
		$this->view->echoppe = $tabEchoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

}