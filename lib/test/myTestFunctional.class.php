<?php

/**
 * Testing class to help with testing
 *
 * Always begin your functional tests with:
 *
 * $browser = new myTestFunctional(new sfBrowser());
 */
class myTestFunctional extends sfTestFunctional {
    /**
     * Override to always load in the doctrine tester
     */
    public function __construct($broswer = null, $lime = null, $options = array()) {
        parent::__construct($broswer, $lime, $options);

        $this->setTester('doctrine', 'sfTesterDoctrine');
    }

    public function setHost($hostname) {
		$this->browser->setHost($hostname);
	}
    
    /**
     * @return myTestFunctional
     */
    public function loadData() {
        Doctrine_Core::loadData(sfConfig::get('sf_data_dir') . '/fixtures');
        return $this;
    }
    
    /**
     * @return myTestFunctional
     */
    public function isModuleAction($module, $action, $statusCode = 200, $debug = false) {
    	$params = array();

    	if (is_array($action)) {
    		$params['module'] = $module;
    		$params = array_merge($params, $action);
    	}
    	else {
    		$params['module'] = $module;
    		$params['action'] = $action;
    	}
    	$this->testModuleAction($params, $statusCode, $debug)->end();
        
		if ($debug) {
            $this->with('response')->begin()
            ->debug()
            ->end();
        }
        $this->with('response')->begin()->
			isStatusCode($statusCode)->
        end();

        return $this;
    }

   public function testModuleAction($params, $statusCode = 200, $debug = false) {
        $test = $this->with('request')->begin();
		foreach ($params as $name => $val) {
			$test = $test->isParameter($name, $val);
		}
        return $test;
    }
    
    /**
     * @return myTestFunctional
     */
    public function login($username = 'admin', $password = 'admin', $debug = false, $statusCode = 302) {
        $this
        ->info(sprintf('Logging in with %s/%s ', $username, $password))
        ->get('/login')
        ->setField('signin[username]', $username)
        ->setField('signin[password]', $password)
        ->click('sign in');

        if ($debug) {
            $this->with('response')->begin()
            ->debug()
            ->end();
        }
		$this->with('response')->begin()->
			isStatusCode($statusCode)->
		end();
		if ($statusCode == 302) {
			return $this->followRedirect();
		}
		else {
			return $this;
		}
    }

    /**
     * @return myTestFunctional
     */
    public function logout() {
        return $this
        ->get('/logout')
        ->isModuleAction('sfGuardAuth', 'signout', 302)
        ->followRedirect()
        ->with('user')->begin()
        ->isAuthenticated(false)
        ->end()
        ;
    }

    /**
     * @return myTestFunctional
     */
	public function checkForm($module, $action, $click, $name, $data, 
				$hasError = false, $statusCode = 200, $debugResponse = false, $debugForm = false) {
		$this->click($click, array($name => $data))->
			isModuleAction($module, $action, $statusCode, $debugResponse);
		if ($debugForm) {
			$this->with('form')->begin()->debug()->end();
		}
		
		return $this->
			with('form')->begin()->
				hasErrors($hasError)->
			end();
	}
}