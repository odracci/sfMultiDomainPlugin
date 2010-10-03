<?php

include(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new myTestFunctional(new sfMultiDomainBrowser());

$username = sfConfig::get("app_foo_username");
$password = sfConfig::get("app_foo_password");


$browser->setHost('foo.domain');

$browser->loadData();

$fooDomain = sfMultiDomainTable::getInstance()->findOneByName('foo');
$barDomain = sfMultiDomainTable::getInstance()->findOneByName('bar');

$browser->get('/')
	->isModuleAction('home', 'index', 200, false)
	->with('response')
	->begin()
		->checkElement('h1', 'config: '. $fooDomain->getName())
		->checkElement('h2', 'test: '. $fooDomain->getName() . ' test')
	->end()
;

$browser = new myTestFunctional(new sfMultiDomainBrowser());
$browser->setHost('bar.domain');
$browser->get('/')->
isModuleAction('home', 'index', 200, false)->
with('response')
	->begin()
		->checkElement('h1', 'config: '. $barDomain->getName())
		->checkElement('h2', 'test: '. $barDomain->getName() . ' test')
	->end()
;

$browser = new myTestFunctional(new sfMultiDomainBrowser());
$browser->setHost('error.domain');
$browser->get('/')->
isModuleAction('home', 'index', 500, false);
