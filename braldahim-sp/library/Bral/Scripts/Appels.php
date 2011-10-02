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
class Bral_Scripts_Appels extends Bral_Scripts_Script
{

	public function getType()
	{
		return self::TYPE_APPELS;
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
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Appelss - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculAppels();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Appelss - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculAppels()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Appelss - calculAppelss - enter -");
		$retour = "";
		$this->calculAppelsBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Appelss - calculAppelss - exit -");
		return $retour;
	}

	private function calculAppelsBraldun(&$retour)
	{
		$scriptTable = new Script();

		$nb = $scriptTable->countByIdBraldunAndType($this->braldun->id_braldun, self::NB_TYPE_DYNAMIQUE_MAX);
		$retour .= "TYPE:" . self::TYPE_DYNAMIQUE . ";NB_DYNAMIQUE:" . $nb . ";MAX_AUTORISE:" . self::NB_TYPE_DYNAMIQUE_MAX . PHP_EOL;

		$nb = $scriptTable->countByIdBraldunAndType($this->braldun->id_braldun, self::NB_TYPE_STATIQUE_MAX);
		$retour .= "TYPE:" . self::TYPE_STATIQUE . ";NB_STATIQUE:" . $nb . ";MAX_AUTORISE:" . self::NB_TYPE_STATIQUE_MAX . PHP_EOL;

	}
}