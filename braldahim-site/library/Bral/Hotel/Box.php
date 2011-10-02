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
abstract class Bral_Hotel_Box
{

	protected $loadWithBoxes = true;

	function __construct($request, $view)
	{
		$this->request = $request;
		$this->view = $view;
	}

	abstract function getNomInterne();

	abstract function render();

	abstract function getPreparedView();
}