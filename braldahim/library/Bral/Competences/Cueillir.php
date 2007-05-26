<?php

class Bral_Competences_Cueillir extends Bral_Competences_Competence {

	private $_tabPlantes = null;
	function prepareCommun() {
		Zend_Loader::loadClass('Plante');
		$tabPlantes = null;
		$this->view->planteOk = false;

		$planteTable = new Plante();
		$plantes = $planteTable->findCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($plantes) > 0) {
			$this->view->planteOk = true;
		}

		foreach ($plantes as $p) {
			$this->_tabPlantes[] = array("id_plante" => $p["id_plante"],
			 "nom_type" => $p["nom_type_plante"],
			 "categorie" => $p["categorie_type_plante"],
			 "id_fk_type_plante" => $p["id_fk_type_plante"],
			 "partie_1_plante" => $p["partie_1_plante"],
			 "partie_2_plante" => $p["partie_2_plante"],
			 "partie_3_plante" => $p["partie_3_plante"],
			 "partie_4_plante" => $p["partie_4_plante"],
			 );
		}
		$this->view->plantes = $this->_tabPlantes;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass('LabanPlante');
		Zend_Loader::loadClass('Hobbit');
		$idPlante = intval($this->request->get("valeur_1"));
		// Verification de la plante
		$planteOk = false;
		foreach ($this->_tabPlantes as $p) {
			if ($p["id_plante"] == $idPlante) {
				$planteOk = true;
				$plante = $p;
				break;
			}
		}

		if ($planteOk === false) {
			throw new Zend_Exception(get_class($this)." Plante invalide : ".$idPlante);
		}

		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;

		$labanPlanteTable = new LabanPlante();
		$data = array(
		'id_laban_plante' => $idPlante,
		'id_fk_type_laban_plante' => $plante["id_fk_type_plante"],
		'id_hobbit_laban_plante' => $this->view->user->id_hobbit,
		'partie_1_laban_plante' => $plante["partie_1_plante"],
		'partie_2_laban_plante' => $plante["partie_2_plante"],
		'partie_3_laban_plante' => $plante["partie_3_plante"],
		'partie_4_laban_plante' => $plante["partie_4_plante"],
		);
		
		$labanPlanteTable->insert($data);
		
		$planteTable = new Plante();
		$where = "id_plante=".$idPlante;
		$planteTable->delete($where);
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$data = array(
		'pa_hobbit' => $this->view->user->pa_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_lieu", "box_laban");
	}
}
