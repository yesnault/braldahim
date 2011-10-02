<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class BatchsController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlBatchsOrAdmin($this->_request);
		Zend_Loader :: loadClass("Bral_Batchs_Factory");
	}

	// 1 action par batch

	function boutiquetabacAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("BoutiqueTabac");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function communautesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Communautes", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function controleAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Controle", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function champsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Champs", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationbosquetsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationBosquets");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationbuissonsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationBuissons");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationfilonsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationMinerais");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationnidsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationNids");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationmonstresAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationMonstres");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationplantesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationPlantes");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function donjonsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Donjons", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function hibernationAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Hibernation");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function braldunsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Bralduns", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function butinsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Butins");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function creationruinesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("CreationRuines", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function hotelAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Hotel", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function motsruniquesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("MotsRuniques", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function palissadesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Palissades");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function routesAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Routes");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function purgeAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Purge");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function scriptspublicsAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("ScriptsPublics", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function souleAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Soule", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function sondageAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Sondage", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}

	function viemonstresAction()
	{
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Viemonstres", $this->view);
		echo $this->view->render("batchs/resultat.phtml");
	}
}

