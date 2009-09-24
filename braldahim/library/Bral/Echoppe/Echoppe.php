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
abstract class Bral_Echoppe_Echoppe {
	
	protected $reloadInterface = false;
	protected $idEchoppe = null;

	function __construct($nomSystemeAction, $request, $view, $action) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		Zend_Loader::loadClass("Echoppe");
		
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->view->nom_systeme = $this->nom_systeme;
		
		$this->prepareEchoppe();
		
		$this->calculNbPa();
		$this->prepareCommun();
		
		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	private function prepareEchoppe() {
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}
		$echoppe = $echoppeRowset[0];
		$nom = "échoppe";
		if ($echoppe["nom_masculin_metier"]{0} == "A") {
			$nom .= " d'";
		} else {
			$nom .= " de ";
		}
		if ($echoppe["sexe_hobbit"] == "masculin") {
			$nom .= $echoppe["nom_masculin_metier"];
		} else {
			$nom .= $echoppe["nom_feminin_metier"];
		}
		$detail = $nom. " appartenant à ".$echoppe["prenom_hobbit"];
		$detail .= " ".$echoppe["nom_hobbit"];
		$detail .= " n°".$echoppe["id_hobbit"];
		
		$this->echoppe = $echoppe;
		$this->nomEchoppe = $nom;
		$this->detailEchoppe = $detail;
		$this->idEchoppe = $echoppeRowset[0]["id_echoppe"];
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	abstract function getNomInterne();
	abstract function getTitreAction();
	
	public function getIdEchoppeCourante() {
		return false;
	}
	
	public function calculNbPa() {
		if ($this->view->user->pa_hobbit - $this->view->config->game->echoppe->nb_pa_service < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->view->config->game->echoppe->nb_pa_service;
	}
	
	/*
	 * Mise à jour des événements du hobbit : type : compétence.
	 */
	private function majEvenementsEchoppe($detailsBot) {
		$this->idTypeEvenement = $this->view->config->game->evenements->type->echoppe;
		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a utilisé les services de l'".$this->detailEchoppe;
		Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_hobbit);
		
		$detailsBot = "Evènement dans l'échoppe ".$this->nomEchoppe." (".$this->echoppe["x_echoppe"].", ".$this->echoppe["y_echoppe"].", ".$this->echoppe["nom_region"]."). Message adressé au client : ".$detailsBot;
		Bral_Util_Evenement::majEvenements($this->echoppe["id_hobbit"], $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_hobbit);
	}
	
	function render() {
		$this->view->titreAction = $this->getTitreAction();
		switch($this->action) {
			case "ask":
				return $this->view->render("echoppe/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("echoppe/".$this->nom_systeme."_resultat.phtml");
				
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				$this->majEvenementsEchoppe(Bral_Helper_Affiche::copie($this->view->texte));
				$this->majHobbit();
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	private function majHobbit() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
		
		$this->view->user->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_hobbit, $this->view->user->castars_hobbit);
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa ;
		
		$data = array(
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'castars_hobbit' => $this->view->user->castars_hobbit,
			'poids_transporte_hobbit' => $this->view->user->poids_transporte_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
	
}