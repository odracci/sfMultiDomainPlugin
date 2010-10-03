<?php

/**
 * sfMultiDomainPlugin configuration.
 *
 * @package     sfMultiDomainPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfMultiDomainPluginConfiguration extends sfPluginConfiguration {
	const VERSION = '0.0.1-DEV';

	/**
	 * @see sfPluginConfiguration
	 */
	public function initialize() {
		$this->dispatcher->connect('request.filter_parameters', array($this, 'detectDomain'));
	}
	
	public function detectDomain(sfEvent $event, $parameters) {
		$request = $event->getSubject();
		$domain = $request->getHost();

		if (strlen($domain)) {
			$data = $this->getDomain($domain);
			$context = $this->configuration->getActive();
			$this->initConfig($context->getConfigCache(), $data['name']);
			sfConfig::set('md_domain', $data['name']);
			sfConfig::set('md_domain_id', $data['id']);
		}

		return $parameters;
	}
	
	private function getDomain($domain) {
		//sf_cache
		$param = array('cache_dir' => sfConfig::get('sf_cache_dir').'/domains',
			'lifetime' => 86400,
			'automatic_cleaning_factor' => 0,
			'prefix' => sfConfig::get('sf_app_dir').'/domains',
		);
		
		$class = sfConfig::get('app_domains_cache_class', 'sfFileCache');
		$param = sfConfig::get('app_domains_cache_param', $param);
		
		$cacheManager = new $class($param);
		
		if (sfConfig::get('sf_cache') && $cacheManager->has($domain)) {
			$data = unserialize($cacheManager->get($domain));
		}
		else {
			$domainObj = sfMultiDomainTable::getInstance()->findOneByHost($domain);
			if (!$domainObj) {
				throw new sfError404Exception('Domain not found: ' . $domain);
			}
			$data = array('id' => $domainObj->getId(), 'name' => $domainObj->getName());
			if (sfConfig::get('sf_cache')) {
				$cacheManager->set($domain, serialize($data));
			}
		}
		return $data;
	}
	
	private function initConfig($configCache, $site) {
		$configFile = sfConfig::get('app_domains_conf_dir') .'/' . $site.'.yml';
		var_dump($configFile);
		$configCache->registerConfigHandler($configFile, 'sfDefineEnvironmentConfigHandler', array('prefix' => 'md_'));
		
		if ($file = $configCache->checkConfig($configFile, true)) {
			include($file);
		}
	}
}
