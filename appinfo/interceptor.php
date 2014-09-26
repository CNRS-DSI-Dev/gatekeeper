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
use OCP\ISession;
use OCA\GateKeeper\Lib\GKHelper;

class Interceptor {

	var $service;
	var $userSession;
	var $throwExceptionToExit;

	/**
	* @param \OCP\IUser
	* @param bool $isLogged trur if user is logged in
	* @param GateKeeperService $service
	* @param bool $throwExceptionToExit Throw Exception instead of exit (usefull for unit test)
	*
	*/
	public function __construct($userSession, $isLoggedIn, $service, $throwExceptionToExit = false) {
		$this->service = $service;
		$this->isLoggedIn = $isLoggedIn;
		$this->userSession = $userSession;
		
	}

	/**
	* Methode to call to run interceptor()
	*/
	function run() {
		$user = $this->userSession->getUser();
		
		
		if ( is_null($user) ) {
			return;
		}

		if ( $this->isLoggedIn || $remote ) {
			$respons = $this->service->checkUserAllowances($user);
			
			if ( $respons->isDenied() ){
				$uid = $user->getUID();

				//=============================
				$this->userSession->logout();
				//=============================

				$remote = GKHelper::isRemote();
				if ( $remote ) {
					$this->denyOnRemote($uid, $respons);
				} else if ( ! $respons->isEmitted() ) {
					$this->denyOnWeb($respons);
				}
			}
		}
	}

	/**
	* Ends dialog when session is in full web
	*/
	function denyOnWeb($respons) {
		\OC_Template::printErrorPage($this->getNiceMessage($respons));
		$this->doesExit();
	}


	/**
	* Ends dialog when session is in remote mode
	*/
	function denyOnRemote($uid, $respons) {
		throw new \Exception("Access is denied. ".$this->getNiceMessage($respons));
	}


	/**
	* @param GateKeeperRespons respons
	* @return String a nice message for end user
	*/
	function getNiceMessage($respons) {
		// TODO introduce i10n
		$fmt = array(
			'uid.blacklisted' 	=> "You do not have access to this service. Please contact your administrator with theses informations: uid=%s.",
			'group.blacklisted' => "You do not have access to this service. Please contact your administrator with theses informations: uid=%s,group=%s.",
			'not.whitelisted' 	=> "Access to this service is restricted. Please contact your administrator with this information: uid=%s.",
			);
		$key = $respons->getCause();
		$sfmt = false;
		if ( isset($fmt[$key])) $sfmt = $fmt[$key];
		if ( $sfmt ) {
			return sprintf($sfmt, $respons->getUid(), $respons->getGroup());
		} else {
			return 'cause: -'.$key;
		}
	}

	/**
	* Replace exit() by a more unit-test friendly excpetion
	*/
	function doesExit(){
		if ($this->throwExceptionToExit ) {
			throw new \Exception('exit');
		} else {
			exit();
		}
	}	

}