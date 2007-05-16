<?
class AuthController extends Zend_Controller_Action { 
    function init() { 
        $this->initView(); 
        Zend_Loader::loadClass('Hobbit'); 
        $this->view->baseUrl = $this->_request->getBaseUrl(); 
        $this->view->user = Zend_Auth::getInstance()->getIdentity(); 
    } 
	
    function indexAction() { 
       $this->_redirect('/'); 
    }

	function loginAction() { 
		// si le joueur est connecte, on le deconnecte !
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/auth/logout'); 
		}
		
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
            // Suppression de la sessions courante
            Zend_Auth::getInstance()->clearIdentity();
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
            $authAdapter->setTableName('hobbit'); 
            $authAdapter->setIdentityColumn('nom_hobbit'); 
            $authAdapter->setCredentialColumn('password_hobbit'); 
             
            // Set the input credential values to authenticate against 
            $authAdapter->setIdentity($username); 
            $authAdapter->setCredential(md5($password)); 
             
            // do the authentication  
            $auth = Zend_Auth::getInstance(); 
            $result = $auth->authenticate($authAdapter); 
            if ($result->isValid()) {
            	
            	$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit'); 
            	if ($hobbit->est_compte_actif_hobbit == "oui") {
	                // success : store database row to auth's storage system 
	                // (not the password though!) 
	                $auth->getStorage()->write($hobbit); 
					// activation du tour
	                Zend_Auth::getInstance()->getIdentity()->activation = ($f->filter($this->_request->getPost('auth_activation')) == 'oui');
	                // Gardiennage
	                Zend_Auth::getInstance()->getIdentity()->gardiennage = ($f->filter($this->_request->getPost('auth_gardiennage')) == 'oui');
	                Zend_Auth::getInstance()->getIdentity()->gardeEnCours = false;
	                
	                if (Zend_Auth::getInstance()->getIdentity()->gardiennage === true) {
	                	$this->_redirect('/gardiennage/'); 
	                } else {
	                	$this->_redirect('/interface/'); 
	                }
            	} else {
            		$this->view->message = "Ce compte n'est pas actif";
            		Zend_Auth::getInstance()->clearIdentity();
            	}
            } else { 
                // failure: clear database row from session 
                $this->view->message = "Echec d'authentification"; 
            } 
        } 
        $this->view->title = "Authentification"; 
        $this->render();   
    } 
    
    function logoutAction() { 
        Zend_Auth::getInstance()->clearIdentity(); 
        $this->_redirect('/'); 
    } 
} 
