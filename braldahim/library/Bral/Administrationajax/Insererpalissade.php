<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Administrationajax_Insererpalissade extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : insérer une palissade";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Palissade");

		$xyzPalissade = $this->request->get("xyz_palissade");
		if ($xyzPalissade != null) {
			list ($xPalissade, $yPalissade, $zPalissade) = split("h", $xyzPalissade);
			Bral_Util_Controle::getValeurIntVerif($xPalissade);
			Bral_Util_Controle::getValeurIntVerif($yPalissade);
			Bral_Util_Controle::getValeurIntVerif($zPalissade);
		}

		if ($xyzPalissade != null) {
			$this->view->xPalissade = $xPalissade;
			$this->view->yPalissade = $yPalissade;
			$this->view->zPalissade = $zPalissade;
		}

		$tabTypesPalissade = null;
		$tabTypesPalissade["palissade"]["type"] = "palissade";
		$tabTypesPalissade["palissade"]["selected"] = "";
		$tabTypesPalissade["ville"]["type"] = "ville";
		$tabTypesPalissade["ville"]["selected"] = "selected";
		$tabTypesPalissade["balise"]["type"] = "balise";
		$tabTypesPalissade["balise"]["selected"] = "";

		$this->view->typePalissade = $tabTypesPalissade;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		$this->calculPalissade();
	}

	function calculPalissade() {
		
		$xPalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yPalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$zPalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		$pvPalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_4"));
		$armureNaturellePalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_5"));
		$nbJours = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_6"));
		$agilitePalissade = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_7"));
		$estDestructiblePalissade = $this->request->getPost("valeur_8");
				
		$palissadeTable = new Palissade();

		$data = array(
			"x_palissade" => $xPalissade,	 	 	 	 	 	 	
			"y_palissade" => $yPalissade,	 	
			"z_palissade" => $zPalissade,		 	 	 	 	 	 	
			"agilite_palissade" => $agilitePalissade,	 
			"armure_naturelle_palissade" => $armureNaturellePalissade,
			"pv_max_palissade" => $pvPalissade,
			"pv_restant_palissade" => $pvPalissade,	 	 	 	 	
			"date_creation_palissade" => date("Y-m-d H:i:s"),
			"date_fin_palissade" =>  Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), $nbJours),
			"est_destructible_palissade"  => $estDestructiblePalissade, 	 	 	 	 	 	
			"id_fk_donjon_palissade" => null,
		);

		$where = "x_palissade = ".$xPalissade. " AND y_palissade=".$yPalissade;
		$nb = $palissadeTable->delete($where);
		if ($nb == 0) {
			$idPalissade = $palissadeTable->insert($data);
		}
		
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["x_position"] = $xPalissade;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["y_position"] = $yPalissade;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["z_position"] = $zPalissade;

		$this->view->dataPalissade = $data;
	}
	
	function getListBoxRefresh() {
		return array("box_vue");
	}
}