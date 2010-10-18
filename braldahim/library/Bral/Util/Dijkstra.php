<?

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 *
 * Algo disponible sur http://en.giswiki.net/wiki/Dijkstra%27s_algorithm#Usage_Example
 */
class Bral_Util_Dijkstra {

	var $visited = array();
	var $distance = array();
	var $previousNode = array();
	var $startnode = null;
	var $map = array();
	var $infiniteDistance = 1000;
	var $numberOfNodes = 0;
	var $bestPath = 0;
	var $matrixWidth = 0;

	var $nbCasesLargeur = 0;
	var $nbCases = 0;

	var $xPosition = 0;
	var $yPosition = 0;
	var $tabPalissades = array();

	public function Dijkstra() {}

	public function calcul($nbCases, $xPosition, $yPosition, $zPosition, $zone = null) {

		$this->bestPath = 0;
		$this->nbCasesLargeur = $nbCases + $nbCases + 1;
		$this->nbCases = $nbCases;
		$this->xPosition = $xPosition;
		$this->yPosition = $yPosition;
		$this->zPosition = $zPosition;

		if ($zone == null) {
			Zend_Loader::loadClass("Zone");
			$zoneTable = new Zone();
			$zones = $zoneTable->findByCase($xPosition, $yPosition, $zPosition);

			// La requete ne doit renvoyer qu'une seule case
			if (count($zones) == 1) {
				$zone = $zones[0];
			} else {
				throw new Zend_Exception("Dijkstra::calcul : Nombre de case invalide");
			}
		}

		$this->initTabPalissadesEaux($zone);
		$this->map = $this->initMap();
		$this->numberOfNodes = count($this->map);

		$this->findShortestPath();
	}

	private function initTabPalissadesEaux($zone) {
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Eau');
		Zend_Loader::loadClass('Tunnel');

		$palissadeTable = new Palissade();
		$eauTable = new Eau();
		$tunnelTable = new Tunnel();

		$xMin = $this->xPosition - $this->nbCasesLargeur;
		$xMax = $this->xPosition + $this->nbCasesLargeur;
		$yMin = $this->yPosition - $this->nbCasesLargeur;
		$yMax = $this->yPosition + $this->nbCasesLargeur;

		$palissades = $palissadeTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->zPosition);
		$eaux = $eauTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->zPosition, false);
		$tunnels = null;
		if ($zone["est_mine_zone"] == "oui") {
			$tunnels = $tunnelTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->zPosition);
		}

		$numero = -1;
		for ($j = $this->nbCases; $j >= -$this->nbCases; $j--) {
			for ($i = -$this->nbCases; $i <= $this->nbCases; $i++) {
				$x = $this->xPosition + $i;
				$y = $this->yPosition + $j;
				$numero++;
				$this->tabPalissadesEauxTunnels[$numero] = 1;
				foreach($palissades as $p) {
					if ($p["x_palissade"] == $x && $p["y_palissade"] == $y) {
						$this->tabPalissadesEauxTunnels[$numero] = $this->infiniteDistance;
						break;
					}
				}
				foreach($eaux as $e) {
					if ($e["x_eau"] == $x && $e["y_eau"] == $y) {
						$this->tabPalissadesEauxTunnels[$numero] = $this->infiniteDistance;
						break;
					}
				}
				if ($zone["est_mine_zone"] == "oui") { // dans une mine
					$tunnelOk = false;
					foreach($tunnels as $t) {
						if ($t["x_tunnel"] == $x && $t["y_tunnel"] == $y) { // tunnel trouvé
							$tunnelOk = true;
							break;
						}
					}
					if ($tunnelOk == false) { // si pas de tunnel trouvé => non accessible
						$this->tabPalissadesEauxTunnels[$numero] = $this->infiniteDistance;
					}
				}
				if ($x == $this->xPosition && $y == $this->yPosition) {
					$this->startnode = $numero;
				}
			}
		}
	}

	private function initMap() {

		// Initialisation des distances connues
		$points = array(); // Un point est un tableau entre la case de départ, la case de fin et la distance entre les deux
		for ($i=0; $i<=$this->nbCasesLargeur * $this->nbCasesLargeur-1; $i++) {
			if ($this->tabPalissadesEauxTunnels[$i] == 1) { // Ce n'est pas une palissade, on initialise les distances avec les cases autour
				// La case qui est à gauche
				if ($i % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case en cours est sur un bord gauche
					$points[] = array($i, $i-1, $this->tabPalissadesEauxTunnels[$i-1]);
				}

				// La case qui est à droite
				if (($i+1) % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case est sur un bord droit
					$points[] = array($i, $i+1, $this->tabPalissadesEauxTunnels[$i+1]);
				}

				// Initialisation des distances avec les cases du dessus
				if ($i >= $this->nbCasesLargeur) { // Si on n'est pas sur la première ligne (car il n'y a rien au dessus)

					// La case directement au dessus
					$points[] = array($i, $i-$this->nbCasesLargeur, $this->tabPalissadesEauxTunnels[$i-$this->nbCasesLargeur]);

					// La case au dessus, à gauche
					if ($i % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case est sur un bord gauche
						$points[] = array($i, $i-$this->nbCasesLargeur-1, $this->tabPalissadesEauxTunnels[$i-$this->nbCasesLargeur-1]);
					}

					// La case au dessus à droite
					if (($i+1) % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case est sur un bord droit
						$points[] = array($i, $i-$this->nbCasesLargeur+1, $this->tabPalissadesEauxTunnels[$i-$this->nbCasesLargeur+1]);
					}
				}

				// Initialisation des distances avec les cases au dessous
				if ($i <= $this->nbCasesLargeur*($this->nbCasesLargeur-1)-1) { // Si on n'est pas sur la dernière ligne

					// La case juste en dessous
					$points[] = array($i, $i+$this->nbCasesLargeur, $this->tabPalissadesEauxTunnels[$i+$this->nbCasesLargeur]);

					// La case en dessous à gauche
					if ($i % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case est sur un bord gauche
						$points[] = array($i, $i+$this->nbCasesLargeur-1, $this->tabPalissadesEauxTunnels[$i+$this->nbCasesLargeur-1]);
					}

					// La case en dessous à droite
					if (($i+1) % $this->nbCasesLargeur > 0) { // Pas d'initialisation si la case est sur un bord droit
						$points[] = array($i, $i+$this->nbCasesLargeur+1, $this->tabPalissadesEauxTunnels[$i+$this->nbCasesLargeur+1]);
					}
				}
			} else { // Cas d'une palissade
				$points[] = array($i, $i, $this->infiniteDistance); // Soit une distance infinie, soit pas de distance, au choix ;-)
			}
		}

		$ourMap = array();
		for ($i=0, $m=count($points); $i < $m; $i++) {
			$x = $points[$i][0];
			$y = $points[$i][1];
			$c = $points[$i][2];
			$ourMap[$x][$y] = $c;
			$ourMap[$y][$x] = $c;
		}

		// ensure that the distance from a node to itself is always zero
		// Purists may want to edit this bit out.
		$matrixWidth = $this->nbCasesLargeur*$this->nbCasesLargeur;
		for ($i=0; $i < $matrixWidth; $i++) {
			for ($k=0; $k < $matrixWidth; $k++) {
				if ($i == $k) $ourMap[$i][$k] = 0;
			}
		}

		return $ourMap;
	}

	private function findShortestPath($to = null) {
		for ($i=0;$i<$this->numberOfNodes;$i++) {
			if ($i == $this->startnode) {
				$this->visited[$i] = true;
				$this->distance[$i] = 0;
			} else {
				$this->visited[$i] = false;
				$this->distance[$i] = isset($this->map[$this->startnode][$i])
				? $this->map[$this->startnode][$i]
				: $this->infiniteDistance;
			}
			$this->previousNode[$i] = $this->startnode;
		}

		$maxTries = $this->numberOfNodes;
		$tries = 0;
		while (in_array(false,$this->visited,true) && $tries <= $maxTries) {
			$this->bestPath = $this->findBestPath($this->distance,array_keys($this->visited,false,true));
			if ($to !== null && $this->bestPath === $to) {
				break;
			}
			$this->updateDistanceAndPrevious($this->bestPath);
			$this->visited[$this->bestPath] = true;
			$tries++;
		}
	}

	private function findBestPath($ourDistance, $ourNodesLeft) {
		$bestPath = $this->infiniteDistance;
		$bestNode = 0;
		for ($i = 0,$m=count($ourNodesLeft); $i < $m; $i++) {
			if ($ourDistance[$ourNodesLeft[$i]] < $bestPath) {
				$bestPath = $ourDistance[$ourNodesLeft[$i]];
				$bestNode = $ourNodesLeft[$i];
			}
		}
		return $bestNode;
	}

	private function updateDistanceAndPrevious($obp) {
		for ($i=0;$i<$this->numberOfNodes;$i++) {
			if ((isset($this->map[$obp][$i]))
			&&	(!($this->map[$obp][$i] == $this->infiniteDistance) || ($this->map[$obp][$i] == 0 ))
			&&	(($this->distance[$obp] + $this->map[$obp][$i]) < $this->distance[$i])
			)
			{
				$this->distance[$i] = $this->distance[$obp] + $this->map[$obp][$i];
				$this->previousNode[$i] = $obp;
			}
		}
	}

	private function printMap() {
		$placeholder = ' %' . strlen($this->infiniteDistance) .'d';
		$foo = '';
		for($i=0,$im=count($this->map);$i<$im;$i++) {
			for ($k=0,$m=$im;$k<$m;$k++) {
				$foo.= sprintf($placeholder, isset($this->map[$i][$k]) ? $this->map[$i][$k] : $this->infiniteDistance);
			}
			$foo.= "\n";
		}
		return $foo;
	}

	private function getResults($to = null) {
		$ourShortestPath = array();
		$foo = '';
		for ($i = 0; $i < $this->numberOfNodes; $i++) {
			if ($to !== null && $to !== $i) {
				continue;
			}
			$ourShortestPath[$i] = array();
			$endNode = null;
			$currNode = $i;
			$ourShortestPath[$i][] = $i;
			while ($endNode === null || $endNode != $this->startnode) {
				$ourShortestPath[$i][] = $this->previousNode[$currNode];
				$endNode = $this->previousNode[$currNode];
				$currNode = $this->previousNode[$currNode];
			}
			$ourShortestPath[$i] = array_reverse($ourShortestPath[$i]);
			if ($to === null || $to === $i) {
				if ($this->distance[$i] >= $this->infiniteDistance) {
					$foo .= sprintf("Aucun accès de %d à %d. \n",$this->startnode,$i);
				} else {
					$foo .= sprintf('%d => %d = %d [%d]: (%s).'."\n" ,
					$this->startnode,$i, $this->distance[$i],
					count($ourShortestPath[$i]),
					implode('-',$ourShortestPath[$i]));
				}
				$foo .= str_repeat('-',20) . "\n";
				if ($to === $i) {
					break;
				}
			}
		}
		return $foo;
	}

	// Recopie de getResults en modifiant seulement le retour pour n'avoir que la distance
	public function getDistance($to = null) {
		$ourShortestPath = array();
		$foo = '';
		for ($i = 0; $i < $this->numberOfNodes; $i++) {
			if ($to !== null && $to !== $i) {
				continue;
			}
			$ourShortestPath[$i] = array();
			$endNode = null;
			$currNode = $i;
			$ourShortestPath[$i][] = $i;
			while ($endNode === null || $endNode != $this->startnode) {
				$ourShortestPath[$i][] = $this->previousNode[$currNode];
				$endNode = $this->previousNode[$currNode];
				$currNode = $this->previousNode[$currNode];
			}
			$ourShortestPath[$i] = array_reverse($ourShortestPath[$i]);
			if ($to === null || $to === $i) {
				if ($this->distance[$i] >= $this->infiniteDistance) {
					$foo .= sprintf("%d",$this->infiniteDistance);
				} else {
					$foo .= sprintf('%d',$this->distance[$i]);
				}
				if ($to === $i) {
					break;
				}
			}
		}
		return $foo;
	}
}