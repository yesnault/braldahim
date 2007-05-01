<?
class AuthController extends Zend_Controller_Action  
{ 
    function init() 
    { 
        $this->initView(); 
        Zend_Loader::loadClass('Hobbit'); 
        $this->view->baseUrl = $this->_request->getBaseUrl(); 
        $this->view->user = Zend_Auth::getInstance()->getIdentity(); 
    } 
	
    function indexAction() 
    { 
       $this->_redirect('/'); 
    }

	function loginAction() 
    { 
        $this->view->message = ''; 
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') { 
            // collect the data from the user 
            Zend_Loader::loadClass('Zend_Filter_StripTags'); 
            $f = new Zend_Filter_StripTags(); 
            $username = $f->filter($this->_request->getPost('nom')); 
            $password = $f->filter($this->_request->getPost('password')); 
         
            // setup Zend_Auth adapter for a database table 
            Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable'); 
            $dbAdapter = Zend_Registry::get('dbAdapter'); 
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
            $authAdapter->setTableName('hobbit'); 
            $authAdapter->setIdentityColumn('nom_hobbit'); 
            $authAdapter->setCredentialColumn('password_hobbit'); 
             
            // Set the input credential values to authenticate against 
            $authAdapter->setIdentity($username); 
            $authAdapter->setCredential($password); 
             
            // do the authentication  
            $auth = Zend_Auth::getInstance(); 
            $result = $auth->authenticate($authAdapter); 
            if ($result->isValid()) { 
                // success : store database row to auth's storage system 
                // (not the password though!) 
                $data = $authAdapter->getResultRowObject(null,'password_hobbit'); 
                $auth->getStorage()->write($data); 
                $this->_redirect('/'); 
            } else { 
                // failure: clear database row from session 
                $this->view->message = "Echec d'authentification"; 
            } 
        } 
        $this->view->title = "Authentification"; 
        $this->render();   
    } 
    
    function logoutAction() 
    { 
        Zend_Auth::getInstance()->clearIdentity(); 
        $this->_redirect('/'); 
    } 
    
    
} 
