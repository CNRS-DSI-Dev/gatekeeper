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
namespace OCA\GateKeeper\Hooks;

class GateKeeperHooks {

	var $gateKeeperService;
	var $logger;

	public function __construct($gateKeeperService, $session, $logger) {
		$this->service = $gateKeeperService;
		$this->session = $session;
		$this->logger = $logger;
	}


	function onPreLogin (String $uid) {
		$this->session->remove('gk_status');
	}

	function onPostLogin (\OC\User\User $user) {
		$this->logger->info('onPostLogin '.$user);
		$this->session->set('gk_status','login');
	}


	function onPostAddUser (\OC\Group\Group $group, \OC\User\User $user) {
		$this->logger->info('onPostAddUser '.$user);
	}

	function onPostRemoveUser (\OC\Group\Group $group, \OC\User\User $user) {
		$this->logger->info('onPostRemoveUser '.$user);
	}
	
	function registerForUserEvents($userSession) {
		$obj = $this;
		$userSession->listen('\OC\User', 'preLogin', function(string $uid, string $password) use(&$obj) { 
			return $obj->onPreLogin($uid); 
		});

		$userSession->listen('\OC\User', 'postLogin', function($user, $password) use(&$obj) { 
			return $obj->onPostLogin($user); 
		});
	}

	function registerForGroupEvents($groupManager) {
		$obj = $this;
		$groupManager->listen('\OC\Group', 'postAddUser', function ($group, $user) use (&$obj) {
			/**
			 * @var \OC\Group\Group $group
			 */
			$cachedUserGroups = array();
		});
		$groupManager->listen('\OC\Group', 'postRemoveUser', function ($group, $user) use (&$obj) {
			/**
			 * @var \OC\Group\Group $group
			 */
			$cachedUserGroups = array();
		});
	}
}