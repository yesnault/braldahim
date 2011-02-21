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
class Bral_Util_Poids {

	const POIDS_CASTARS = 0.001;

	// la peau et la viande ont le meme poids. Si cela change, çà impactera depiauter, methode preCalculPoids()
	const POIDS_PEAU = 0.4;
	const POIDS_VIANDE = 0.4;

	const POIDS_RATION = 0.25;
	const POIDS_CUIR = 0.4;
	const POIDS_FOURRURE = 0.4;
	const POIDS_PLANCHE = 2;
	const POIDS_RONDIN = 3;
	const POIDS_RUNE = 0.05;
	const POIDS_POTION = 0.3;
	const POIDS_MINERAI = 0.6;
	const POIDS_POIGNEE_GRAINES = 0.01;
	const POIDS_LINGOT = 1;
	const POIDS_PARTIE_PLANTE_BRUTE = 0.002;
	const POIDS_PARTIE_PLANTE_PREPAREE = 0.003;
	const POIDS_MUNITION = 0.04;
	const POIDS_TABAC = 0;
	const POIDS_BIERE = 0.3;

	function __construct() {
	}
}