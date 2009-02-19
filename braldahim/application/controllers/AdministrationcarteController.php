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
class AdministrationcarteController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		
		$this->tailleMapBottom = 40;
		$this->distanceD = 20;
		$this->coefTaille = 2;
		$this->tailleX = (-$this->view->config->game->x_min + $this->view->config->game->x_max) / $this->coefTaille;
		$this->tailleY = (-$this->view->config->game->y_min + $this->view->config->game->y_max) / $this->coefTaille;
	}
	
	function indexAction() {
		$this->render();
	}
	
	function carteAction() {
		Zend_Loader::loadClass('Session');
		
		$session = new Session();
		$sessionsRowset = $session->findAll();
		
		$sessions = null;
		foreach($sessionsRowset as $s) {
			$sessions[] = array(
				"nom" => $s["prenom_hobbit"]. " ".$s["nom_hobbit"],
				"id_fk_hobbit_session" => $s["id_fk_hobbit_session"],
				"id_php_session" => $s["id_php_session"],
				"ip_session" => $s["ip_session"],
				"date_derniere_action_session" => $s["date_derniere_action_session"],
				);
		}
		
		$this->view->sessions = $sessions;
		
		$this->render();
	}
	
	function imageAction() {
		$image = ImageCreate($this->tailleX + $this->distanceD * 2, $this->tailleY + $this->distanceD * 2 + $this->tailleMapBottom);
		
		$this->initImageCouleurs(&$image);
		
		// Fond de l'image en gris => atteint uniquement la règle
		ImageFilledRectangle($image, 0, 0, $this->tailleX + $this->distanceD * 2, $this->tailleY + $this->distanceD * 2 + $this->tailleMapBottom, $this->gris);
		
		// Contour en noir
		// 1 : taille du contour
		ImageFilledRectangle($image, $this->distanceD - 1, $this->distanceD - 1, $this->tailleX + 1 + $this->distanceD, $this->tailleY + 1 + $this->distanceD, $this->noir);
		
		//Puis on initialise le fond du terrain à blanc
		ImageFilledRectangle($image, $this->distanceD, $this->distanceD, $this->tailleX + $this->distanceD, $this->tailleY + $this->distanceD, $this->blanc);
		
		$this->dessineZones(&$image);
		//$this->dessineFilons(&$image);
		$this->dessineVilles(&$image);
		$this->dessineHobbits(&$image);
		$this->dessineMonstres(&$image);
		
		$this->view->image = $image;
		$this->render();
	}
	
	function initImageCouleurs(&$image) {
		// Couleurs trouvées sur http://fr.wikipedia.org/wiki/Couleurs_du_Web
		$couleurRouge = array("FFA07A", "DC143C", "FF0000", "B22222", "8B0000");	
		
		/*$couleurJaune=array("EEE8AA", "F0E68C", "BDB76B", "FFD700", "FFFF00");
		$couleurBleue=array("0033FF","336699","97058B","690261","3C0137");
		$couleurVert=array("00DD00","00AA00", "009900", "006600", "003300");
		$couleurGrise=array("888888","999999", "AAAAAA", "B9B9B9", "D0D0D0");*/
	
		$this->noir = ImageColorAllocate($image, 0, 0, 0);
		$this->blanc = ImageColorAllocate($image, 222, 222, 222); 
		$this->gris = ImageColorAllocate($image, 190, 190, 190);
		$this->gris2 = ImageColorAllocate($image, 140, 140, 140);
		$this->vert = ImageColorAllocate($image, 0, 255, 0);  
		$this->vert2 = ImageColorAllocate($image, 0, 128, 0);  
		
		/*sscanf($couleurGrise[1], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[2], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[3], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[4], "%2x%2x%2x", $red, $green, $blue);*/
	
		sscanf($couleurRouge[0], "%2x%2x%2x", $red, $green, $blue);
		$this->rouge_0 = ImageColorAllocate($image, $red, $green, $blue); 
		sscanf($couleurRouge[1], "%2x%2x%2x", $red, $green, $blue);
		$this->rouge_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurRouge[2], "%2x%2x%2x", $red, $green, $blue);
		$this->rouge_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurRouge[3], "%2x%2x%2x", $red, $green, $blue);
		$this->rouge_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurRouge[4], "%2x%2x%2x", $red, $green, $blue);
		$this->rouge_4 = ImageColorAllocate($image, $red, $green, $blue);
		
		/*sscanf($couleurJaune[0], "%2x%2x%2x", $red, $green, $blue);
		$this->jaune_0 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurJaune[1], "%2x%2x%2x", $red, $green, $blue);
		$this->jaune_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurJaune[2], "%2x%2x%2x", $red, $green, $blue);
		$this->jaune_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurJaune[3], "%2x%2x%2x", $red, $green, $blue);
		$this->jaune_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurJaune[4], "%2x%2x%2x", $red, $green, $blue);
		$this->jaune_4 = ImageColorAllocate($image, $red, $green, $blue);
		
		sscanf($couleurVert[0], "%2x%2x%2x", $red, $green, $blue);
		$this->vert_0 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurVert[1], "%2x%2x%2x", $red, $green, $blue);
		$this->vert_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurVert[2], "%2x%2x%2x", $red, $green, $blue);
		$this->vert_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurVert[3], "%2x%2x%2x", $red, $green, $blue);
		$this->vert_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurVert[4], "%2x%2x%2x", $red, $green, $blue);
		$this->vert_4 = ImageColorAllocate($image, $red, $green, $blue);
		
		sscanf($couleurBleue[0], "%2x%2x%2x", $red, $green, $blue);
		$this->bleu_0 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurBleue[1], "%2x%2x%2x", $red, $green, $blue);
		$this->bleu_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurBleue[2], "%2x%2x%2x", $red, $green, $blue);
		$this->bleu_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurBleue[3], "%2x%2x%2x", $red, $green, $blue);
		$this->bleu_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurBleue[4], "%2x%2x%2x", $red, $green, $blue);
		$this->bleu_4 = ImageColorAllocate($image, $red, $green, $blue);*/
	
		$this->tab_rouge = array($this->rouge_0, $this->rouge_1, $this->rouge_2, $this->rouge_3 ,$this->rouge_4);
		
		/*$this->tab_bleu=array($this->bleu_0,$this->bleu_1,$this->bleu_2,$this->bleu_3,$this->bleu_4);
		$this->tab_jaune=array($this->jaune_0,$this->jaune_1,$this->jaune_2,$this->jaune_3,$this->jaune_4);
		$this->tab_vert=array($this->vert_0,$this->vert_1,$this->vert_2,$this->vert_3,$this->vert_4);
		$this->tab_gris=array($this->gris_0,$this->gris_1,$this->gris_2,$this->gris_3,$this->gris_4);*/
	}
	
	private function dessineZones(&$image) {
		
		Zend_Loader::loadClass('Zone');
		$zonesTable = new Zone();
		$zones = $zonesTable->fetchall();
		
		foreach ($zones as $z) {
			$x_deb_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_min_zone"]) / $this->coefTaille;
			$x_fin_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_max_zone"]) / $this->coefTaille;
			$y_deb_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_max_zone"]) / $this->coefTaille;
			$y_fin_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_min_zone"]) / $this->coefTaille;
			
			ImageRectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->gris2);
			ImageString($image, 1, $x_deb_map , $y_deb_map, "zone ".$z["id_zone"], $this->gris2);
		}
	}
	
	private function dessineVilles(&$image) {
		Zend_Loader::loadClass('Ville');
		$villesTable = new Ville();
		$villes = $villesTable->fetchall();
		
		$nbVilles = 0;
		foreach ($villes as $v) {
			$x_deb_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $v["x_min_ville"]) / $this->coefTaille;
			$x_fin_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $v["x_max_ville"]) / $this->coefTaille;
			$y_deb_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $v["y_max_ville"]) / $this->coefTaille;
			$y_fin_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $v["y_min_ville"]) / $this->coefTaille;
			
			$coefRayon = 4;
			
			ImageRectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->vert);
			$palier = 5;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[0]);
			$palier = 10;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[1]);
			$palier = 15;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[2]);
			$palier = 20;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[3]);
			$palier = 25;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[4]);
			$palier = 30;
			ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[5]);
			ImageString($image, 1, $x_deb_map , $y_deb_map, $v["nom_ville"]. " ".($v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2)."/".($v["y_min_ville"] + ($v["y_max_ville"] - $v["y_min_ville"]) / 2), $this->noir);
			$nbVilles++;
		}
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 2, $nbVilles." Villes", $this->noir);
	}
	
	private function dessineFilons(&$image) {
		Zend_Loader::loadClass('Filon');
		$filonsTable = new Filon();
		$filons = $filonsTable->fetchall();
		
		$nbFilons = 0;
		foreach ($filons as $f) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $f["x_filon"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $f["y_filon"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->gris2);
			$nbFilons++;
		}
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 20, $nbFilons." Filons", $this->gris2);
	}
	
	private function dessineHobbits(&$image) {
		Zend_Loader::loadClass('Hobbit');
		$hobbitsTable = new Hobbit();
		$hobbits = $hobbitsTable->fetchall();
		
		$nbHobbits = 0;
		foreach ($hobbits as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_hobbit"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_hobbit"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->vert2);
			$nbHobbits++;
		}
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 10, $nbHobbits." Hobbits", $this->vert2);
	}
	
	private function dessineMonstres(&$image) {
		Zend_Loader::loadClass('Monstre');
		$monstresTable = new Monstre();
		$monstres = $monstresTable->fetchall();
		
		$tab[0] = 0;
		$tab[1] = 0;
		$tab[2] = 0;
		$tab[3] = 0;
		$tab[4] = 0;
		$tab[5] = 0;
		foreach ($monstres as $m) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $m["x_monstre"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $m["y_monstre"]) / $this->coefTaille;
			
			$niveau = floor($m["niveau_monstre"] / 5);
			$couleur = $this->tab_rouge[$niveau];
			ImageFilledEllipse($image, $x, $y, 4, 4, $couleur);
			$tab[$niveau]++;
		}
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 2, $tab[0]." Monstres N < 5", $this->tab_rouge[0]);
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 10, $tab[1]." Monstres N < 10", $this->tab_rouge[1]);
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 20, $tab[2]." Monstres N < 15", $this->tab_rouge[2]);
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 30, $tab[3]." Monstres N < 20", $this->tab_rouge[3]);
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 40, $tab[4]." Monstres N < 25", $this->tab_rouge[4]);
		ImageString($image, 1, $this->distanceD, $this->distanceD + $this->tailleY + 50, $tab[5]." Monstres N < 30", $this->tab_rouge[5]);
	}
}

