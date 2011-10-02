<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Monstres_Competences_Prereperage extends Bral_Monstres_Competences_Competence
{

	const SUITE_REPERAGE_STANDARD = "standard";
	const SUITE_REPERAGE_CASE = "reperagecase";
	const SUITE_DEPLACEMENT = "deplacement";
	const SUITE_DISPARITION = "disparition";

	abstract function enchainerAvecReperageStandard();

}