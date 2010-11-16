<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Creusetunnel extends Bral_Monstres_Competences_Deplacement {

	public function actionSpecifique($estFuite) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Creusetunnel - enter - (idm:".$this->monstre["id_monstre"].")");
		//TODO
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Creusetunnel - exit - (idm:".$this->monstre["id_monstre"].")");
	}
	
	
}