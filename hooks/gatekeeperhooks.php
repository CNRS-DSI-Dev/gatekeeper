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
use OCA\GateKeeper\Lib\GKHelper;

class GateKeeperHooks {

	var $gateKeeperService;
	var $logger;
	var $session;

	public function __construct($gateKeeperService, $session, $logger) {
		$this->gateKeeperService = $gateKeeperService;
		$this->session = $session;
		$this->logger = $logger;
	}


	/**
	* remove gk_status from sessions's scope if it's not remote
	*/
	function onPreLogin ( $uid) {
		$remote = GKHelper::isRemote();
		//$this->session->remove('gk_status');
		$this->gateKeeperService->startCycle($uid);
		\OCP\Util::writeLog('gatekeeperHOOKS::onPreLogin', $uid.' will log in'.(($remote) ? ' in remote mode': ''), \OCP\Util::INFO);
	}

	function onLogout(){
		$remote = GKHelper::isRemote();
		//$this->session->remove('gk_status');
		$this->gateKeeperService->endCycle();
	}

	function onPostLogin (\OC\User\User $user) {
		//$this->logger->info('onPostLogin '.$user);		
	}


	function onPostAddUser (\OC\Group\Group $group, \OC\User\User $user) {
		$remote = GKHelper::isRemote();
		$this->logger->info('onPostAddUser '.$user->getUID());
		\OCP\Util::writeLog('onPostAddUser', $user->getUID().' will log in'.(($remote) ? ' in remote mode': ''), \OCP\Util::INFO);
		//$this->session->remove('gk_status');
	}

	function onPostRemoveUser (\OC\Group\Group $group, \OC\User\User $user) {
		$remote = GKHelper::isRemote();
		$this->logger->info('onPostRemoveUser '.$user->getUID());
		\OCP\Util::writeLog('onPostRemoveUser', $user->getUID().' will log in'.(($remote) ? ' in remote mode': ''), \OCP\Util::INFO);
		//$this->session->remove('gk_status');
	}
	
	function registerForUserEvents($userSession) {
		$obj = $this;
		$userSession->listen('\OC\User', 'preLogin', function( $uid,  $password) use(&$obj) { 
			return $obj->onPreLogin($uid); 
		});

		$userSession->listen('\OC\User', 'preRememberedLogin', function( $uid )  use ($obj) {
			return $obj->onPreLogin($uid); 
		});

		$userSession->listen('\OC\User', 'postLogin', function($user, $password) use(&$obj) { 
			return $obj->onPostLogin($user); 
		});

		$userSession->listen('\OC\User', 'logout', function() use(&$obj) { 
			return $obj->onLogout(); 
		});		
	}

	function registerForGroupEvents($groupManager) {
		$obj = $this;
		$groupManager->listen('\OC\Group', 'postAddUser', function ($group, $user) use (&$obj) {
			return $obj->onPostAddUser($group, $user);
		});
		$groupManager->listen('\OC\Group', 'postRemoveUser', function ($group, $user) use (&$obj) {
			return $obj->onPostRemoveUser($group, $user);
		});
	}
}