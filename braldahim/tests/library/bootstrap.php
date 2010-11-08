$rootPath = realpath(dirname(__DIR__));
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', $rootPath . '/application');
}
if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', 'testing');
}
set_include_path(implode(PATH_SEPARATOR, array(
    '.',
    $rootPath . ';C:/ZendFramework-1.10.2/library/',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Bral_');