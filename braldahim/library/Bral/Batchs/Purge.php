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
class Bral_Batchs_Purge extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - calculBatchImpl - enter -");
		$retour = null;

		$retour .= $this->purgeBatch();
		$retour .= $this->purgeCadavres();
		$retour .= $this->purgeElementMinerai();
		$retour .= $this->purgeElementPartiePlante();
		$retour .= $this->purgeElementMunition();
		$retour .= $this->purgeElementPotion();
		$retour .= $this->purgeElementRune();
		$retour .= $this->purgeElementEquipement();
		$retour .= $this->purgeElementEvenement();
		$retour .= $this->purgeMessages();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - calculBatchImpl - exit -");
		return $retour;
	}

	private function purgeBatch() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeBatch - enter -");

		Zend_Loader::loadClass('Batch');

		$retour = "";
		$batchTable = new Batch();

		$date = date("Y-m-d H:i:s");
		$add_day = - $this->config->batchs->purge->table->batch->nbjours->ok;

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$where = $batchTable->getAdapter()->quoteInto('date_debut_batch <= ?',  $dateFin);
		$nb = $batchTable->delete($where. " and etat_batch like 'OK'");
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " Ok:nb delete:".$nb. " date:".$dateFin;

		$add_day = - $this->config->batchs->purge->table->batch->nbjours->tous;

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$where = $batchTable->getAdapter()->quoteInto('date_debut_batch <= ?',  $dateFin);
		$nb = $batchTable->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - tous - nb:".$nb." - where:".$where);
		$retour .= " Tous:nb delete:".$nb. " date:".$dateFin;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeBatch - exit -");
		return $retour;
	}

	private function purgeCadavres() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeCadavres - enter -");

		Zend_Loader::loadClass('Monstre');

		$retour = "";
		$monstreTable = new Monstre();

		$date = date("Y-m-d H:i:s");
		$where = $monstreTable->getAdapter()->quoteInto('date_fin_cadavre_monstre <= ?',  $date);
		$nb = $monstreTable->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " Cadavres:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeCadavres - exit -");
		return $retour;
	}

	private function purgeElementMinerai() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementMinerai - enter -");

		Zend_Loader::loadClass('ElementMinerai');

		$retour = "";
		$elementMinerai = new ElementMinerai();

		$date = date("Y-m-d H:i:s");
		$where = $elementMinerai->getAdapter()->quoteInto('date_fin_element_minerai <= ?',  $date);
		$nb = $elementMinerai->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltMinerai:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementMinerai - exit -");
		return $retour;
	}

	private function purgeElementPartiePlante() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementPartiePlante - enter -");

		Zend_Loader::loadClass('ElementPartieplante');

		$retour = "";
		$elementPartieplante = new ElementPartieplante();

		$date = date("Y-m-d H:i:s");
		$where = $elementPartieplante->getAdapter()->quoteInto('date_fin_element_partieplante <= ?',  $date);
		$nb = $elementPartieplante->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltPartiePlante:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementPartiePlante - exit -");
		return $retour;
	}

	private function purgeElementMunition() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementMunition - enter -");

		Zend_Loader::loadClass('ElementMunition');

		$retour = "";
		$elementMunition = new ElementMunition();

		$date = date("Y-m-d H:i:s");
		$where = $elementMunition->getAdapter()->quoteInto('date_fin_element_munition <= ?',  $date);
		$nb = $elementMunition->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltMunition:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementMunition - exit -");
		return $retour;
	}

	private function purgeElementPotion() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementPotion - enter -");

		Zend_Loader::loadClass('ElementPotion');

		$retour = "";
		$elementPotion = new ElementPotion();

		$date = date("Y-m-d H:i:s");
		$where = $elementPotion->getAdapter()->quoteInto('date_fin_element_potion <= ?',  $date);
		$nb = $elementPotion->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltPotion:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementPotion - exit -");
		return $retour;
	}

	private function purgeElementRune() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementRune - enter -");

		Zend_Loader::loadClass('ElementRune');

		$retour = "";
		$elementRune = new ElementRune();

		$date = date("Y-m-d H:i:s");
		$where = $elementRune->getAdapter()->quoteInto('date_fin_element_rune <= ?',  $date);
		$nb = $elementRune->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltRune:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementRune - exit -");
		return $retour;
	}

	private function purgeElementEquipement() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementEquipement - enter -");

		Zend_Loader::loadClass('ElementEquipement');

		$retour = "";
		$elementEquipement = new ElementEquipement();

		$date = date("Y-m-d H:i:s");
		$where = $elementEquipement->getAdapter()->quoteInto('date_fin_element_equipement <= ?',  $date);
		$nb = $elementEquipement->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " EltEquipement:nb delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementEquipement - exit -");
		return $retour;
	}

	private function purgeElementEvenement() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementEvenement - enter -");

		Zend_Loader::loadClass('Evenement');

		$retour = "";
		$evenement = new Evenement();

		$date = date("Y-m-d H:i:s");
		$add_day = - 30;
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);

		$where = $evenement->getAdapter()->quoteInto('date_evenement <= ?',  $dateFin);
		$where .= " AND id_fk_type_evenement = ".$this->config->game->evenements->type->deplacement;
		$nb = $evenement->delete($where);

		$add_day = - 90;
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);

		$where = $evenement->getAdapter()->quoteInto('date_evenement <= ?',  $dateFin);
		$where .= " AND (id_fk_type_evenement = ".$this->config->game->evenements->type->service;
		$where .= " OR id_fk_type_evenement = ".$this->config->game->evenements->type->competence;
		$where .= " OR id_fk_type_evenement = ".$this->config->game->evenements->type->transbahuter;
		$where .= " )";
		$nb = $evenement->delete($where);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		$retour = " Evt delete:".$nb. " date:".$date;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeElementEvenement - exit -");
		return $retour;
	}



	private function purgeMessages() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeMessages - enter -");

		Zend_Loader::loadClass('Message');

		$retour = "";
		$messageTable = new Message();

		//TODO

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeMessages - exit -");
		return $retour;
	}
}