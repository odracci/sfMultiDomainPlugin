<?php
class sfMultiDomainBrowser extends sfBrowser {
	public function setHost($hostname) {
		$this->hostname = $hostname;
	}
}