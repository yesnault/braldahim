<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Entoilage extends Bral_Monstres_Competences_Attaque
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

		$malus = 1;
		$nbTours = 1;

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["agilite_bm_monstre"];

		$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->cible["force_base_braldun"]);
		$jetBraldun = $jetBraldun + $this->cible["force_bm_braldun"] + $this->cible["force_bbdf_braldun"];

		if ($jetBraldun > $jetMonstre) {
			$malus = 0;
		}

		if ($malus > 0) {
			Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_PA_MARCHER, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, "Entoilage");
		}
		$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetBraldun);

		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun)
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m" . $this->monstre["id_monstre"] . "] a entoilé [b" . $braldun["id_braldun"] . "]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun)
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] . " (" . $this->monstre["id_monstre"] . ") vous a entoilé :";
		$retour .= PHP_EOL . "Jet du Monstre (jet d'agilité) : " . $jetMonstre;
		$retour .= PHP_EOL . "Jet de résistance (jet de force) : " . $jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL . "Vous avez résisté au jet, vous n'avez pas de malus en déplacement.";
		} else {
			$retour .= PHP_EOL . "Vous n'avez pas résisté au jet.";
			$retour .= PHP_EOL . "Vos déplacements vous demandent " . $malus . " PA supplémentaire.";
		}

		$retour .= PHP_EOL . "Nombre de tours : " . $nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - getDetailsBot - exit");
		return $retour;
	}
}