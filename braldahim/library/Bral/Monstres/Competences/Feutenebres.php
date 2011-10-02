<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Feutenebres extends Bral_Monstres_Competences_Deplacement
{

	public function actionSpecifique()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - enter - (idm:" . $this->monstre["id_monstre"] . ")");

		// Verification de la cible
		if (!$this->verificationCible()) {
			Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - cible sur même case - exit - (idm:" . $this->monstre["id_monstre"] . ")");
			return; // Cible sur la même case
		}

		// S'il y a une case non creusée autour, on creuse.
		Zend_Loader::loadClass("Tunnel");
		$tunnelTable = new Tunnel();

		$tunnels = $tunnelTable->selectVue($this->monstre["x_monstre"] - 1, $this->monstre["y_monstre"] - 1, $this->monstre["x_monstre"] + 1, $this->monstre["y_monstre"] + 1, $this->monstre["z_monstre"]);

		if (count($tunnels) == 9) {
			Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - il y a deja 9 tunnels autour, pas de feu des tenebres - exit - (idm:" . $this->monstre["id_monstre"] . ")");
			return; // Toutes les cases sont creusées autour
		} else {
			$tabTunnels = null;
			for ($x = $this->monstre["x_monstre"] - 1; $x <= $this->monstre["x_monstre"] + 1; $x++) {
				for ($y = $this->monstre["y_monstre"] - 1; $y <= $this->monstre["y_monstre"] + 1; $y++) {
					$tabTunnels[$x][$y] = null;
				}
			}

			foreach ($tunnels as $t) {
				$tabTunnels[$t["x_tunnel"]][$t["y_tunnel"]] = $t;
			}
		}

		$modif = false;

		for ($x = $this->monstre["x_monstre"] - 1; $x <= $this->monstre["x_monstre"] + 1; $x++) {
			for ($y = $this->monstre["y_monstre"] - 1; $y <= $this->monstre["y_monstre"] + 1; $y++) {
				if ($tabTunnels[$x][$y] === null) {
					$data = array(
						"x_tunnel" => $x,
						"y_tunnel" => $y,
						"z_tunnel" => $this->monstre["z_monstre"],
						"date_tunnel" => date("Y-m-d H:00:00"),
						"est_eboulable_tunnel" => 'oui',
					);

					$tunnelTable->insert($data);
					$modif = true;
					Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - creuse x:" . $x . " y:" . $y . " - (idm:" . $this->monstre["id_monstre"] . ")");
				}
			}
		}

		if ($modif === true) {
			$this->majEvenement();
			Bral_Util_Log::viemonstres()->debug(get_class($this) . " - PA Monstre (" . $this->monstre["id_monstre"] . ") avant action nb=" . $this->monstre["pa_monstre"]);
			$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $this->competence["pa_utilisation_mcompetence"];
			Bral_Util_Log::viemonstres()->debug(get_class($this) . " - PA Monstre (" . $this->monstre["id_monstre"] . ") apres action nb=" . $this->monstre["pa_monstre"]);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - exit - (idm:" . $this->monstre["id_monstre"] . ")");
		return;
	}

	private function verificationCible()
	{
		if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
			// S'il y a une cible,
			$braldunTable = new Braldun();
			$cibles = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $this->monstre["vue_monstre"], $this->monstre["id_fk_braldun_cible_monstre"], false);
			Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - cible en cours - (idm:" . $this->monstre["id_monstre"] . ")");
			if (count($cibles) == 1) {
				$cible = $cibles[0];
				Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - cible en cours trouvee:(idh:" . $cible["id_braldun"] . ") - (idm:" . $this->monstre["id_monstre"] . ")");
				if ($cible["x_braldun"] == $this->monstre["x_monstre"] &&
					$cible["y_braldun"] == $this->monstre["y_monstre"]
				) {
					Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Feutenebres - cible sur la même case, pas de feu des tenebres - exit - (idm:" . $this->monstre["id_monstre"] . ")");
					return false; // Cible sur la même case
				}
			}
		}

		return true;
	}

	private function majEvenement()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m" . $this->monstre["id_monstre"] . "] crache du feu des ténèbres";
		$detailsBot = "";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit");
	}
}