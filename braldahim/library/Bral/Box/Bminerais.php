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
class Bral_Box_Bminerais extends Bral_Box_Boutique {
	
	public function getTitreOnglet() {
		return "Boutique Minerais";
	}
	
	public function setDisplay($display) {
		$this->view->display = $display;
	}
	
	public function render() {
		$this->preRender();
		$this->prepareArticles();
		return $this->view->render("interface/bminerais.phtml");
	}
	
	private function prepareArticles() {
		Zend_Loader::loadClass('TypeMinerai');
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchAll();
		
		foreach ($typeMineraiRowset as $t) {
			$prixBrut = "todoBrut";
			$prixLingot = "todoLingot";
			
			$tabBrut = array(
				"id_type_minerai" => $t->id_type_minerai, 
				"nom" => $t->nom_type_minerai, 
				"nom_systeme" => $t->nom_systeme_type_minerai, 
				"description" => $t->description_type_minerai,
				"prix" => $prixBrut,
				"nom_type" => "Brut",
				"type" => "brut",
			);
			
			$tabLingot = $tabBrut;
			$tabLingot["prix"] = $prixLingot;
			$tabLingot["nom_type"] = "Lingot";
			$tabLingot["type"] = "lingot";
			$typeMinerais[] = $tabBrut;
			$typeMinerais[] = $tabLingot;
		}
		$this->view->articles = $typeMinerais;
	}
}
