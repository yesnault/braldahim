<?php
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(), APPLICATION_PATH . '/../application/models/'
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once "Zend/Loader.php";

class StackTest extends Zend_Test_PHPUnit_ControllerTestCase
{

	public function setUp() {
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
	}

	public function testPushAndPop()
	{
		$stack = array();
		$this->assertEquals(0, count($stack));

		array_push($stack, 'foo');
		$this->assertEquals('foo', $stack[count($stack)-1]);
		$this->assertEquals(1, count($stack));

		$this->assertEquals('foo', array_pop($stack));
		$this->assertEquals(0, count($stack));

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($nbCases, $this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], null, true);

	}
}