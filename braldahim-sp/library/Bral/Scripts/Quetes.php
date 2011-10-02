<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Quetes.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Scripts_Quetes extends Bral_Scripts_Script
{

	public function getType()
	{
		return self::TYPE_STATIQUE;
	}

	public function getEtatService()
	{
		return self::SERVICE_ACTIVE;
	}

	public function getVersion()
	{
		return 1;
	}

	public function calculScriptImpl()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Quetes - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculQuete();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Quetes - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculQuete()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Quetes - calculQuete - enter -");
		$retour = "";
		$this->calculQueteBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Quetes - calculQuete - exit -");
		return $retour;
	}

	private function calculQueteBraldun(&$retour)
	{
		Zend_Loader::loadClass("Etape");
		Zend_Loader::loadClass("Quete");

		$queteTable = new Quete();
		$quetesRowset = $queteTable->findByIdBraldun($this->braldun->id_braldun);

		if ($quetesRowset != null) {
			foreach ($quetesRowset as $e) {
				$retour .= "QUETE;" . $e["id_quete"] . ';';
				$retour .= $e["date_creation_quete"] . ';';
				$retour .= $e["date_fin_quete"] . ';';
				$retour .= str_replace(PHP_EOL, ", ", $e["gain_quete"]) . ';';
				$retour .= $e["est_initiatique_quete"];
				$retour .= PHP_EOL;
			}
		} else {
			$retour .= "AUCUNE_QUETE";
		}

		$etapeTable = new Etape();
		$etapesRowset = $etapeTable->findByIdBraldun($this->braldun->id_braldun);

		if ($etapesRowset != null) {
			foreach ($etapesRowset as $e) {
				$retour .= "ETAPE;";
				$retour .= $e["id_fk_quete_etape"] . ';';
				if ($e["date_debut_etape"] != '') {
					$retour .= $e["libelle_etape"] . ';';
					$retour .= $e["date_debut_etape"] . ';';
					$retour .= $e["date_fin_etape"] . ';';
					$retour .= $e["est_terminee_etape"] . ';';
					$retour .= $e["objectif_etape"] . ';';
				} else {
					$retour .= ';';
					$retour .= ';';
					$retour .= ';';
					$retour .= $e["est_terminee_etape"] . ';';
					$retour .= ';';
				}
				$retour .= $e["ordre_etape"];
				$retour .= PHP_EOL;
			}
		} else {
			$retour .= "AUCUNE_ETAPE";
		}
	}
}