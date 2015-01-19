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

	public function __construct($gateKeeperService, $logger) {
		$this->gateKeeperService = $gateKeeperService;
		$this->logger = $logger;
	}


	/**
	* remove gk_status from sessions's scope if it's not remote
	*/
	function onPreLogin ( $uid) {
		$this->gateKeeperService->startCycle($uid);
	}

	function onLogout(){
		$this->gateKeeperService->endCycle();
	}

	
	function registerForUserEvents($userSession) {
		$obj = $this;
		$userSession->listen('\OC\User', 'preLogin', function( $uid,  $password) use(&$obj) { 
			return $obj->onPreLogin($uid); 
		});

		$userSession->listen('\OC\User', 'preRememberedLogin', function( $uid )  use (&$obj) {
			return $obj->onPreLogin($uid); 
		});

		$userSession->listen('\OC\User', 'logout', function() use(&$obj) { 
			return $obj->onLogout(); 
		});		
	}

}