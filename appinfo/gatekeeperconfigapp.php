<?php
/**
 * ownCloud - 
 *
 * @author Marc DeXeT
 * @copyright 2014 DSI CNRS https://www.dsi.cnrs.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\GateKeeper\AppInfo;
use \OCP\AppFramework\App;

/**
 *
 */
class GateKeeperConfigApp extends App {	

	public function __construct(array $urlParams=array()){
		parent::__construct('gatekeeper', $urlParams);

		$container = $this->getContainer();

		// Hooks
		$container->registerService('GateKeeperHooks', function ($c) {
			return new \OCA\GateKeeper\Hooks\GateKeeperHooks(
				$c->query('GateKeeperService'),
				$c->query('Logger')
				);
		});

		// Service
		$container->registerService('GateKeeperService', function ($c) {
			return new \OCA\GateKeeper\Service\GateKeeperService(
				$c->query('ServerContainer')->getAppConfig()->getValue('gatekeeper', 'mode', 'whitelist' ),
				$c->query('AccessObjectMapper'), 
				$c->query('GroupManager')
				);
		});

		// Service
		$container->registerService('AccessObjectMapper', function ($c) {
			return new \OCA\GateKeeper\Db\AccessObjectMapper(
				$c->query('ServerContainer')->getDb()
				);
		});

		// groupManager
		$container->registerService('GroupManager', function($c) {
			return new \OC\Group\Manager(
					$c->query('ServerContainer')->getUserManager()
				);
		});

		$container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });
	}


	public function getUserSession() {
		return $this->getContainer()->getServer()->getUserSession();
	}
	
	public function getUser() {
		return $this->getContainer()->getServer()->getUserSession()->getUser();
	}

	public function getUserManager() {
		return $this->getContainer()->getServer()->getUserManager();
	}

	public function getGroupManager() {
		return $this->getContainer()->query('GroupManager');	
	}
}