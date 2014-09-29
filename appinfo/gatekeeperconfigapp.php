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
use \OCA\GateKeeper\AppInfo\GKConstants as GK;
use \OCA\GateKeeper\Lib\GKHelper;

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
				$c->query('ServerContainer')->getAppConfig()->getValue('gatekeeper', 'mode' ),
				$c->query('ServerContainer')->getSession(),
				$c->query('AccessObjectMapper'), 
				$c->query('GroupManager'),
				GKHelper::isRemote()
				);
		});

		// Mapper
		$container->registerService('AccessObjectMapper', function ($c) {
			return new \OCA\GateKeeper\Db\AccessObjectMapper(
				$c->query('ServerContainer')->getDb()
				);
		});

		// groupManager
		$container->registerService('GroupManager', function($c) {
			return \OC_Group::getManager();
		});

		// - logger - 
		$container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });

		$container->registerService('Interceptor', function($c) {
            return new \OCA\GateKeeper\AppInfo\Interceptor(
            		$c->query('ServerContainer')->getUserSession(),
            		\OC_User::isLoggedIn(),
            		$c->query('GateKeeperService'),
            		$c->query('L10N')
            	);
        });

		$container->registerService('L10N', function($c) {
			return $c->query('ServerContainer')->getL10N($c->query('AppName'));
		});        


        $container->registerService('SettingsController', function($c) {
            return new \OCA\GateKeeper\Controller\SettingsController(
            		$c->query('Request'),
            		$c->query('ServerContainer')->getAppConfig(),
            		$c->query('AccessObjectMapper'),
            		$c->query('GroupManager')
            	);
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

	public function isGateOpened() {
		 $mode = $this->getContainer()->getServer()->getAppConfig()->getValue('gatekeeper', 'mode' );
		 if ( ! is_null($mode) && strcmp($mode, GK::OPENED_GATE_MODE) != 0  ) {
		 	return true;
		 }
		 return false;
	}
}