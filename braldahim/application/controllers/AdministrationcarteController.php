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
class AdministrationcarteController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');

		$this->tailleMapBottom = 40;
		$this->distanceD = 20;

		if (floatval($this->_request->get("coef")) > 0) {
			$this->coefTaille = floatval($this->_request->get("coef"));
		} else {
			$this->coefTaille = 1;
		}

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
				"nom" => $s["prenom_braldun"]. " ".$s["nom_braldun"],
				"id_fk_braldun_session" => $s["id_fk_braldun_session"],
				"id_php_session" => $s["id_php_session"],
				"ip_session" => $s["ip_session"],
				"date_derniere_action_session" => $s["date_derniere_action_session"],
			);
		}

		$this->view->sessions = $sessions;

		$parametres = "?parm=1";
		if (intval($this->_request->get("zones")) == 1) {
			$parametres	.= "&zones=1";
		}
		if (intval($this->_request->get("zonesnids")) == 1) {
			$parametres	.= "&zonesnids=1";
			$parametres	.= "&nids=1";
		}

		if (intval($this->_request->get("bralduns")) == 1) {
			$parametres .= "&bralduns=1";
		}

		if (intval($this->_request->get("routes")) == 1) {
			$parametres .= "&routes=1";
		}

		if (intval($this->_request->get("eaux")) == 1) {
			$parametres .= "&eaux=1";
		}

		if (intval($this->_request->get("filons")) == 1) {
			$parametres .= "&filons=1";
		}

		if (intval($this->_request->get("plantes")) == 1) {
			$parametres .= "&plantes=1";
		}

		if (intval($this->_request->get("bosquets")) == 1) {
			$parametres .= "&bosquets=1";
		}

		if (intval($this->_request->get("buissons")) == 1) {
			$parametres .= "&buissons=1";
		}

		if (intval($this->_request->get("palissades")) == 1) {
			$parametres .= "&palissades=1";
		}

		if (intval($this->_request->get("lieuxmythiques")) == 1) {
			$parametres .= "&lieuxmythiques=1";
		}

		if (intval($this->_request->get("monstres")) == 1 && intval($this->_request->get("zonenidmin")) >= 1 && intval($this->_request->get("zonenidmax")) >= 1) {
			$parametres .= "&monstres=1&zonenidmin=".intval($this->_request->get("zonenidmin"))."&zonenidmax=".intval($this->_request->get("zonenidmax"));
		}

		if (floatval($this->_request->get("coef")) > 0) {
			$parametres .= "&coef=".floatval($this->_request->get("coef"));
		}

		$this->view->parametres = $parametres;
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

		if (intval($this->_request->get("zones")) == 1) {
			$this->dessineZones(&$image);
		}
		if (intval($this->_request->get("zonesnids")) == 1) {
			$this->dessineZonesNids(&$image);
		}

		if (intval($this->_request->get("bralduns")) == 1) {
			$this->dessineBralduns(&$image);
		}

		if (intval($this->_request->get("monstres")) == 1) {
			$this->dessineMonstres(&$image, intval($this->_request->get("zonenidmin")), intval($this->_request->get("zonenidmax")));
		}

		if (intval($this->_request->get("nids")) == 1) {
			$this->dessineNids(&$image);
		}

		if (intval($this->_request->get("routes")) == 1) {
			$this->dessineRoutes(&$image);
		}

		if (intval($this->_request->get("eaux")) == 1) {
			$this->dessineEaux(&$image);
		}

		if (intval($this->_request->get("filons")) == 1) {
			$this->dessineFilons(&$image);
		}

		if (intval($this->_request->get("plantes")) == 1) {
			$this->dessinePlantes(&$image);
		}

		if (intval($this->_request->get("bosquets")) == 1) {
			$this->dessineBosquets(&$image);
		}

		if (intval($this->_request->get("buissons")) == 1) {
			$this->dessineBuissons(&$image);
		}

		if (intval($this->_request->get("palissades")) == 1) {
			$this->dessinePalissades(&$image);
		}

		if (intval($this->_request->get("lieuxmythiques")) == 1) {
			$this->dessineLieuxmythiques(&$image);
		}

		$this->dessineVilles(&$image);

		$this->view->image = $image;
		$this->render();
	}

	function initImageCouleurs(&$image) {
		// Couleurs trouvées sur http://fr.wikipedia.org/wiki/Couleurs_du_Web
		$couleurRouge = array("FFA07A", "DC143C", "FF0000", "B22222", "8B0000");

		$couleurRouge=array("FF0000", "DC143C", "FFA07A", "FA8072", "F08080");
		$couleurBleue=array("00008B","0033FF","4169E1","1E90FF","87CEEB");
		$couleurVert=array("00DD00","00AA00", "009900", "006600", "003300");
		$couleurGrise=array("888888","999999", "AAAAAA", "B9B9B9", "D0D0D0");

		$this->noir = ImageColorAllocate($image, 0, 0, 0);
		$this->blanc = ImageColorAllocate($image, 222, 222, 222);
		$this->gris = ImageColorAllocate($image, 190, 190, 190);
		$this->gris2 = ImageColorAllocate($image, 140, 140, 140);
		$this->vert = ImageColorAllocate($image, 0, 255, 0);
		$this->vert2 = ImageColorAllocate($image, 0, 128, 0);

		sscanf($couleurGrise[1], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_1 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[2], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_2 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[3], "%2x%2x%2x", $red, $green, $blue);
		$this->gris_3 = ImageColorAllocate($image, $red, $green, $blue);
		sscanf($couleurGrise[4], "%2x%2x%2x", $red, $green, $blue);

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

		sscanf($couleurJaune[0], "%2x%2x%2x", $red, $green, $blue);
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
		$this->bleu_4 = ImageColorAllocate($image, $red, $green, $blue);

		$this->tab_rouge = array($this->rouge_0, $this->rouge_1, $this->rouge_2, $this->rouge_3 ,$this->rouge_4, $this->noir);
		$this->tab_bleu=array($this->bleu_0,$this->bleu_1,$this->bleu_2,$this->bleu_3,$this->bleu_4);

		/*$this->tab_bleu=array($this->bleu_0,$this->bleu_1,$this->bleu_2,$this->bleu_3,$this->bleu_4);
		 $this->tab_jaune=array($this->jaune_0,$this->jaune_1,$this->jaune_2,$this->jaune_3,$this->jaune_4);
		 $this->tab_vert=array($this->vert_0,$this->vert_1,$this->vert_2,$this->vert_3,$this->vert_4);
		 $this->tab_gris=array($this->gris_0,$this->gris_1,$this->gris_2,$this->gris_3,$this->gris_4);*/
	}

	private function dessineZones(&$image) {

		Zend_Loader::loadClass('Zone');
		$zonesTable = new Zone();
		$where = "z_zone = 0";
		$zones = $zonesTable->fetchall($where);

		foreach ($zones as $z) {
			$x_deb_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_min_zone"]) / $this->coefTaille;
			$x_fin_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_max_zone"]) / $this->coefTaille;
			$y_deb_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_max_zone"]) / $this->coefTaille;
			$y_fin_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_min_zone"]) / $this->coefTaille;

			$texte = $this->getTexteEnvironnement($z["id_fk_environnement_zone"]);

			switch($z["id_fk_environnement_zone"]) {
				case 1 : // plaine
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->vert_2);
					break;
				case 3 : // marais
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->bleu_2);
					break;
				case 4 : // montagne
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->rouge_2);
					break;
				case 5 : // gazon
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->vert_3);
					break;
				case 6 : // caverne
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->gris2);
					break;
				default:
					imagefilledrectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->rouge_0);
					break;
			}
				
			ImageString($image, 1, $x_deb_map , $y_deb_map, $z["id_zone"]."Z".$z["id_zone"]. " ".$z["x_min_zone"]."/".$z["y_max_zone"]. " ".$texte, $this->noir);
		}
	}

	private function dessineZonesNids(&$image) {

		Zend_Loader::loadClass('ZoneNid');
		$zonesNidsTable = new ZoneNid();
		$where = "z_zone_nid = 0";
		$zonesNids = $zonesNidsTable->fetchall($where);

		foreach ($zonesNids as $z) {
			$x_deb_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_min_zone_nid"]) / $this->coefTaille;
			$x_fin_map =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $z["x_max_zone_nid"]) / $this->coefTaille;
			$y_deb_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_max_zone_nid"]) / $this->coefTaille;
			$y_fin_map =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $z["y_min_zone_nid"]) / $this->coefTaille;

			ImageRectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->gris2);

			$marge = 0;

			if ($z["est_ville_zone_nid"] == "oui") {
				$marge = 10;
			}
			ImageString($image, 2, $x_deb_map + $marge, $y_deb_map + $marge, " ".$z["id_zone_nid"]. ":".$z["x_min_zone_nid"]."/".$z["y_max_zone_nid"], $this->gris2);
		}
	}

	private function getTexteEnvironnement($idEnvironnement) {
		$retour = null;
		switch($idEnvironnement) {
			case 1 : // plaine
				$retour = "plaine";
				break;
			case 3 : // marais
				$retour = "marais";
				break;
			case 4 : // montagne
				$retour = "montagne";
				break;
			case 5 : // gazon
				$retour = "gazon";
				break;
			case 6 : // caverne
				$retour = "caverne";
				break;
			default:
				$retour = "erreur";
				break;
		}
		return $retour;
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

			$x_centre = $this->distanceD + ($this->tailleX * $this->coefTaille / 2 +$v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2) / $this->coefTaille;
			$y_centre = $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $v["y_min_ville"] - ($v["y_max_ville"] - $v["y_min_ville"]) / 2) / $this->coefTaille;

			ImageRectangle($image, $x_deb_map, $y_deb_map, $x_fin_map, $y_fin_map, $this->vert);

			//$rayon = $this->distanceD + (100 * $this->coefTaille / 2) / $this->coefTaille;

			/*for ($rayonTexte = 10; $rayonTexte <= 100; $rayonTexte = $rayonTexte + 10) {
				$rayon = $rayonTexte / $this->coefTaille;

				$texte = "Rayon ".$rayonTexte.":".(($v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2)-$rayonTexte)."/".(($v["y_max_ville"] - ($v["y_max_ville"] - $v["y_min_ville"]) / 2)+$rayonTexte);
				ImageString($image, 1, $x_centre - $rayon + 5 , $y_centre - $rayon + 5, $texte, $this->tab_rouge[0]);
				ImageRectangle($image, $x_centre - $rayon, $y_centre - $rayon, $x_centre + $rayon, $y_centre + $rayon, $this->tab_rouge[0]);
					
				}*/

			/*$coefRayon = 1;
			 $palier = 5;
			 	
			 ImageRectangle($image, $x_centre - $coefRayon*$palier/$this->coefTaille, $y_centre - $coefRayon*$palier/$this->coefTaille, $x_centre + $coefRayon*$palier/$this->coefTaille, $y_centre + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[0]);
			 //ImageString($image, 1, $x_centre - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[0]);
			 */

			/*	$palier = 5;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[0]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[0]);
			 $palier = 10;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[1]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[1]);
			 $palier = 15;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[2]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[2]);
			 $palier = 20;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[3]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[3]);
			 $palier = 25;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[4]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[4]);
			 $palier = 30;
			 ImageRectangle($image, $x_deb_map - $coefRayon*$palier/$this->coefTaille, $y_deb_map - $coefRayon*$palier/$this->coefTaille, $x_fin_map + $coefRayon*$palier/$this->coefTaille, $y_fin_map + $coefRayon*$palier/$this->coefTaille, $this->tab_rouge[5]);
			 ImageString($image, 1, $x_deb_map - $coefRayon*$palier/$this->coefTaille , $y_deb_map - $coefRayon*$palier/$this->coefTaille, ($v["x_min_ville"]-$coefRayon*$palier)."/".($v["y_max_ville"]-$coefRayon*$palier), $this->tab_rouge[5]);
			 */
			ImageString($image, 2, $x_deb_map + 10 , $y_deb_map + 10, $v["nom_ville"]. " ".($v["x_min_ville"] + ($v["x_max_ville"] - $v["x_min_ville"]) / 2)."/".($v["y_min_ville"] + ($v["y_max_ville"] - $v["y_min_ville"]) / 2), $this->noir);
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
		ImageString($image, 1, $this->distanceD + 620, $this->distanceD + $this->tailleY + 20, $nbFilons." Filons", $this->gris2);
	}

	private function dessinePlantes(&$image) {
		Zend_Loader::loadClass('Plante');
		$plantesTable = new Plante();
		$plantes = $plantesTable->fetchall();

		$nbFilons = 0;
		foreach ($plantes as $f) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $f["x_plante"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $f["y_plante"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->vert2);
			$nbFilons++;
		}
		ImageString($image, 1, $this->distanceD + 620, $this->distanceD + $this->tailleY + 30, $nbFilons." Plantes", $this->vert2);
	}

	private function dessineBosquets(&$image) {
		Zend_Loader::loadClass('Bosquet');
		$bosquetsTable = new Bosquet();
		$bosquets = $bosquetsTable->fetchall();

		$nbBosquets = 0;
		foreach ($bosquets as $f) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $f["x_bosquet"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $f["y_bosquet"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->gris2);
			$nbBosquets++;
		}
		ImageString($image, 1, $this->distanceD + 420, $this->distanceD + $this->tailleY + 20, $nbBosquets." Bosquets", $this->gris2);
	}

	private function dessineBuissons(&$image) {
		Zend_Loader::loadClass('Buisson');
		$buissonsTable = new Buisson();
		$buissons = $buissonsTable->fetchall();

		$nbBuissons = 0;
		foreach ($buissons as $f) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $f["x_buisson"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $f["y_buisson"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->gris2);
			$nbBuissons++;
		}
		ImageString($image, 1, $this->distanceD + 420, $this->distanceD + $this->tailleY + 20, $nbBuissons." Buissons", $this->gris2);
	}

	private function dessinePalissades(&$image) {
		Zend_Loader::loadClass('Palissade');
		$palissadesTable = new Palissade();
		$palissades = $palissadesTable->fetchall();

		$nbPalissades = 0;
		foreach ($palissades as $f) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $f["x_palissade"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $f["y_palissade"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->gris2);
			$nbPalissades++;
		}
		ImageString($image, 1, $this->distanceD + 420, $this->distanceD + $this->tailleY + 20, $nbPalissades." Palissades", $this->gris2);
	}

	private function dessineBralduns(&$image) {
		Zend_Loader::loadClass('Braldun');
		$braldunsTable = new Braldun();
		$bralduns = $braldunsTable->fetchall();

		$nbBralduns = 0;
		foreach ($bralduns as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_braldun"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_braldun"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->vert2);
			$nbBralduns++;
		}
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 10, $nbBralduns." Bralduns", $this->vert2);
	}

	private function dessineLieuxmythiques(&$image) {
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('TypeLieu');

		$lieuTable = new Lieu();
		$lieux = $lieuTable->findByType(TypeLieu::ID_TYPE_LIEUMYTHIQUE); // lieux mythiques

		$nbLieux = 0;
		foreach ($lieux as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_lieu"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_lieu"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 5, 5, $this->bleu_0);
			$nbLieux++;
		}
		ImageString($image, 1, $this->distanceD + 420, $this->distanceD + $this->tailleY + 10, $nbLieux." Lieux Mythiques", $this->bleu_0);
	}

	private function dessineRoutes(&$image) {
		Zend_Loader::loadClass('Route');
		$routesTable = new Route();
		$routes = $routesTable->fetchall();

		$nbRoutesVille = 0;
		$nbBalises = 0;
		$nbRoutesEchoppe = 0;
		$nbRoutesVisible = 0;
		$nbRoutesNonVisible = 0;
		foreach ($routes as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_route"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_route"]) / $this->coefTaille;
			if ($h["type_route"] == "route") {
				if ($h["est_visible_route"] == "oui") {
					ImageFilledEllipse($image, $x, $y, 2, 2, $this->rouge_4);
					$nbRoutesVisible++;
				} else {
					ImageFilledEllipse($image, $x, $y, 2, 2, $this->rouge_3);
					$nbRoutesNonVisible++;
				}
			} elseif ($h["type_route"] == "balise") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->vert2);
				$nbBalises++;
			} elseif ($h["type_route"] == "ville") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->rouge_0);
				$nbRoutesVille++;
			} elseif ($h["type_route"] == "echoppe") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->rouge_1);
				$nbRoutesEchoppe++;
			}

		}
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 10, $nbRoutesVille." Paves Ville", $this->rouge_0);
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 20, $nbBalises." Balises", $this->vert2);
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 30, $nbRoutesVisible." Routes visible", $this->rouge_4);
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 40, $nbRoutesNonVisible." Routes non visible", $this->rouge_3);
		ImageString($image, 1, $this->distanceD + 120, $this->distanceD + $this->tailleY + 50, $nbRoutesEchoppe." Routes Echoppe", $this->rouge_1);
	}

	private function dessineEaux(&$image) {
		Zend_Loader::loadClass('Eau');
		$eauxTable = new Eau();
		$eaux = $eauxTable->fetchall();

		$nbEauxGue = 0;
		$nbEauxProfonde = 0;
		$nbEauxLac = 0;
		$nbEauxMer = 0;
		foreach ($eaux as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_eau"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_eau"]) / $this->coefTaille;
			if ($h["type_eau"] == "lac") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->bleu_3);
				$nbEauxLac++;
			} elseif ($h["type_eau"] == "mer") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->bleu_0);
				$nbEauxMer++;
			} elseif ($h["type_eau"] == "peuprofonde") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->bleu_4);
				$nbEauxGue++;
			} elseif ($h["type_eau"] == "profonde") {
				ImageFilledEllipse($image, $x, $y, 2, 2, $this->bleu_2);
				$nbEauxProfonde++;
			}
		}
		ImageString($image, 1, $this->distanceD + 320, $this->distanceD + $this->tailleY + 10, $nbEauxGue." peu profonde", $this->bleu_4);
		ImageString($image, 1, $this->distanceD + 320, $this->distanceD + $this->tailleY + 20, $nbEauxProfonde." Profonde", $this->bleu_2);
		ImageString($image, 1, $this->distanceD + 320, $this->distanceD + $this->tailleY + 30, $nbEauxLac." Lac", $this->bleu_3);
		ImageString($image, 1, $this->distanceD + 320, $this->distanceD + $this->tailleY + 40, $nbEauxMer." Mer", $this->bleu_0);
	}

	private function dessineNids(&$image) {
		Zend_Loader::loadClass('Nid');
		$nidTable = new Nid();
		$nids = $nidTable->fetchall();

		foreach ($nids as $h) {
			$x =  $this->distanceD + ($this->tailleX * $this->coefTaille / 2 + $h["x_nid"]) / $this->coefTaille;
			$y =  $this->distanceD + ($this->tailleY * $this->coefTaille / 2 - $h["y_nid"]) / $this->coefTaille;
			ImageFilledEllipse($image, $x, $y, 2, 2, $this->vert2);
		}
	}

	private function dessineMonstres(&$image, $idZoneNidMin, $idZoneNidMax) {
		Zend_Loader::loadClass('Monstre');
		$monstresTable = new Monstre();

		$monstres = $monstresTable->findByIdZoneNidMinAndIdZoneNidMax($idZoneNidMin, $idZoneNidMax);

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

