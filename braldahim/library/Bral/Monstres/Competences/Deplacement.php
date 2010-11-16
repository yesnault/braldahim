<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Monstres_Competences_Deplacement extends Bral_Monstres_Competences_Competence {

	protected  $estFuite = false;
	
	public function setEstFuite($estFuite) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setEstFuite - (idm:".$this->monstre["id_monstre"].") - enter");

		$this->estFuite = $estFuite;

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - setEstFuite - (idm:".$this->monstre["id_monstre"].") - exit");
		return;
	}

}