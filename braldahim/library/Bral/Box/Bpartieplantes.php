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
class Bral_Box_Bpartieplantes extends Bral_Box_Boutique {
	
	public function getTitreOnglet() {
		return "Boutique Plantes";
	}
	
	public function setDisplay($display) {
		$this->view->display = $display;
	}
	
	public function render() {
		$this->preRender();
		$this->prepareArticles();
		return $this->view->render("interface/bpartieplantes.phtml");
	}
	
	private function prepareArticles() {
		Zend_Loader::loadClass('TypePartieplante');
		Zend_Loader::loadClass('TypePlante');
		
		$typePartiePlanteTable = new TypePartieplante();
		$typePlanteTable = new TypePlante();
		
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall();
		foreach($typePartiePlanteRowset as $p) {
			$tabPartiePlante[$p->id_type_partieplante]["id"] = $p->id_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom"] = $p->nom_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom_systeme"] = $p->nom_systeme_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["description"] = $p->description_type_partieplante;
		}
		
		$articles = null;
		$typePlanteRowset = $typePlanteTable->findAll();
		foreach($typePlanteRowset as $t) {
			$this->ajoutePartie($articles, $t, $tabPartiePlante, "id_fk_partieplante1_type_plante");
			$this->ajoutePartie($articles, $t, $tabPartiePlante, "id_fk_partieplante2_type_plante");
			$this->ajoutePartie($articles, $t, $tabPartiePlante, "id_fk_partieplante3_type_plante");
			$this->ajoutePartie($articles, $t, $tabPartiePlante, "id_fk_partieplante4_type_plante");
		}
		
		$this->view->articles = $articles;
	}
	
	private function ajoutePartie(&$articles, $typePlante, $tabPartiePlante, $key) {
		if ($typePlante[$key] > 0) {
			$nom_partie = $tabPartiePlante[$typePlante[$key]]["nom"];
			$id_partie = $tabPartiePlante[$typePlante[$key]]["id"];
			$prixPartie = 32;
			
			$articles[] = array(
				"id_type_plante" => $typePlante["id_type_plante"],
				"nom" => $typePlante["nom_type_plante"],
				"categorie" => $typePlante["categorie_type_plante"],
				"id_type_partie_plante" => $id_partie,
				"nom_type" => $nom_partie,
				"prix" => $prixPartie,
			);
		}
	}
}
