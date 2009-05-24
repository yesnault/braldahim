<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class AdministrationajaxController  extends Bral_Controller_Action {
	public function doactionAction() {
		Bral_Util_Securite::controlAdmin();
		$this->doBralAction("Bral_Administrationajax_Factory");
	}	
}
