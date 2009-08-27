<?

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
class GazetteController extends Bral_Controller_InfoJeu {

	function indexAction() {
		parent::indexCommunAction("histoire");
		$this->render();
	}
}