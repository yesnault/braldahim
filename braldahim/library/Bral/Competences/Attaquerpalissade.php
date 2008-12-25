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
class Bral_Competences_Attaquerpalissade extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Attaque'); 	
		Zend_Loader::loadClass('Palissade');

		$this->view->attaquerPalissadeOk = false;
	
		$this->distance = 1;
		$this->view->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->view->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->view->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->view->y_max = $this->view->user->y_hobbit + $this->distance;
		
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$defautChecked = false;
		
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			 $change_level = true;
			 for ($i = -$this->distance; $i <= $this->distance; $i ++) {
			 	$x = $this->view->user->x_hobbit + $i;
			 	$y = $this->view->user->y_hobbit + $j;
			 	
			 	$display = $x;
			 	$display .= " ; ";
			 	$display .= $y;
			 	
				$valid = false;
				$palissade = null;
				$est_destructible = null;
			 	
			 	foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"]) {
						$est_destructible = $p["est_destructible_palissade"];
						if ($est_destructible == "oui") {
							$valid = true;
							$palissade = $p;
						}
						break;
					}
				}

			 	if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
			 		$this->view->attaquerPalissadeOk = true;
			 	} else {
			 		$default = "";
			 	}

			 	$tab[] = array ("x_offset" => $i,
				 	"y_offset" => $j,
				 	"default" => $default,
				 	"display" => $display,
				 	"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid,
			 		"est_destructible" => $est_destructible,
			 	);	
				
			 	if ($this->request->get("valeur_1") != null) { // attaque palissade en cours
				 	$x_y = $this->request->get("valeur_1");
					list ($offset_x, $offset_y) = split("h", $x_y);
					if ($offset_x == $i && $offset_y == $j && $valid == true) {
						$this->view->palissade = $palissade;
					}
			 	}
		
			 	$tabValidation[$i][$j] = $valid;
			 	
				if ($change_level) {
					$change_level = false;
				}
			 }
		}
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		if ($this->view->attaquerPalissadeOk == false) {
			throw new Zend_Exception(get_class($this)." Attaquer Palissade interdit");
		}

		// on verifie que l'on peut attaquer une palissade sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade X impossible : ".$offset_x);
		}
		
		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade Y impossible : ".$offset_y);
		}
		
		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade XY impossible : ".$offset_x.$offset_y);
		}
		
		if ($this->view->palissade == null) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade Null");
		}
		
		$this->setEvenementQueSurOkJet1(false);
		$this->calculAttaquerPalissade();
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculAttaquerPalissade() {
		
		$tabDegats = Bral_Util_Attaque::calculDegatAttaqueNormale($this->view->user);
		$this->view->degats = $tabDegats["noncritique"];
		
		$this->view->detruire = false;
		$this->view->degatsInfliges = $this->view->degats - $this->view->palissade["armure_naturelle_palissade"];
		
		if ($this->view->degatsInfliges < 0) {
			$this->view->degatsInfliges = 0;
		}
		
		$this->view->palissade["pv_restant_palissade"] = $this->view->palissade["pv_restant_palissade"] - $this->view->degatsInfliges;
		if ($this->view->palissade["pv_restant_palissade"] <= 0) {
			$this->view->palissade["pv_restant_palissade"] = 0;
			$this->view->detruire = true;
		}
		
		$palissadeTable = new Palissade();
		
		if ($this->view->detruire) {
			$where = "id_palissade=".intval($this->view->palissade["id_palissade"]);
			$palissadeTable->delete($where);
		} else {
			if ($this->view->degatsInfliges > 0) {
				$data = array(
					"pv_restant_palissade" => $this->view->palissade["pv_restant_palissade"],
				);
				
				$where = "id_palissade=".intval($this->view->palissade["id_palissade"]);
				$palissadeTable->update($data, $where);
			}
		}
		
		Bral_Util_Attaque::calculStatutEngage(&$this->view->user);
		unset($palissadeTable);
	}
	
	protected function calculPx() {
		$this->view->calcul_px_generique = false;
		if ($this->view->degatsInfliges > 0) {
			$this->view->nb_px = 1;
		} else {
			$this->view->nb_px = 0;
		}
		$this->view->nb_px_perso = $this->view->nb_px;
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue"));
	}
}
