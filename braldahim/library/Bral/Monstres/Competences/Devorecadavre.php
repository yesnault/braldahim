<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Devorecadavre extends Bral_Monstres_Competences_Attaque
{

	public function calculJetAttaque()
	{
	}

	public function calculDegat($estCritique)
	{
	}

	public function actionSpecifique()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCaseCadavre($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		if ($monstres != null) {
			$cadavre = $monstres[0];
			$where = "id_monstre=" . $cadavre["id_monstre"];
			$data = array('est_depiaute_cadavre' => 'oui');
			$monstreTable->update($data, $where);

			$bonus = Bral_Util_De::get_1d3();
			$nbTours = Bral_Util_De::get_1d3();
			Bral_Util_Effets::ajouteEtAppliqueEffetMonstre($this->monstre, Bral_Util_Effets::CARACT_FORCE, Bral_Util_Effets::TYPE_BONUS, $nbTours, $bonus);
			$this->majEvenement($bonus, $nbTours);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($bonus, $nbTours)
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m" . $this->monstre["id_monstre"] . "] dévore un cadavre";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit");
	}
}