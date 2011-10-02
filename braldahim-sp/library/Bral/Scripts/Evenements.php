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
class Bral_Scripts_Evenements extends Bral_Scripts_Script
{

	public function getType()
	{
		return self::TYPE_DYNAMIQUE;
	}

	public function getEtatService()
	{
		return self::SERVICE_ACTIVE;
	}

	public function getVersion()
	{
		return 2;
	}

	public function calculScriptImpl()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Evenements - calculScriptImpl - enter -");

		$retour = null;
		$this->calculEvenements($retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Evenements - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEvenements(&$retour)
	{

		$retour1 = 'idBraldun;idEvenement;type;date;details;detailsbot' . PHP_EOL;
		$retour2 = '';

		Zend_Loader::loadClass("Evenement");
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdBraldun($this->braldun->id_braldun, 1, 100, -1);

		foreach ($evenements as $p) {

			$details = str_replace('</label>', '<!-- FIN -->', $p["details_evenement"]);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/braldun/?braldun=", '<!-- DEBUT_BRALDUN:', $details);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/monstre/?monstre=", '<!-- DEBUT_MONSTRE:', $details);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/materiel/?materiel=", '<!-- DEBUT_MATERIEL:', $details);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/rune/?rune=", '<!-- DEBUT_RUNE:', $details);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/equipement/?equipement=", '<!-- DEBUT_EQUIPEMENT:', $details);
			$details = str_replace("<label class='alabel' onclick=\"javascript:ouvrirWin('/voir/potion/?potion=", '<!-- DEBUT_POTION:', $details);
			$details = str_replace("');\">", '-- FIN_A -->', $details);

			$detailsBots = str_replace(';', '', $p["details_bot_evenement"]);
			$detailsBots = str_replace("\r", '', $detailsBots);
			$detailsBots = str_replace("\n", '<br />', $detailsBots);
			$detailsBots = trim($detailsBots);

			$retour2 .= $this->braldun->id_braldun . ';' . $p["id_evenement"] . ';' . $p["nom_type_evenement"] . ';' . $p["date_evenement"] . ';';
			$retour2 .= $details . ';';
			$retour2 .= $detailsBots;
			$retour2 .= PHP_EOL;
		}

		$retour .= $retour1;
		$retour .= $retour2;

	}
}