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
	var $l10n;
	var $denyLogger;
	var $throwExceptionToExit;


	/**
	* @param \OCP\IUser
	* @param bool $isLogged trur if user is logged in
	* @param \OC_L10N $l10n
	* @param GateKeeperService $service
	* @param bool $throwExceptionToExit Throw Exception instead of exit (usefull for unit test)
	*
	*/
	public function __construct($userSession, $isLoggedIn, $service, $l10n, $denyLogger, $throwExceptionToExit = false) {
		$this->service = $service;
		$this->isLoggedIn = $isLoggedIn;
		$this->userSession = $userSession;
		$this->l10n = $l10n;
		$this->denyLogger = $denyLogger;
		
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
				$this->denyLogger->write("uid=".$respons->getUid()
					.",cause=".$respons->getCause()
					.",IP=".$this->getIPAddress()
					.",client=".$remote);

				if ( $remote ) {
					$this->denyOnRemote($respons);
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
		\OC_Template::printErrorPage( $this->getNiceMessage( $respons ));
		$this->doesExit();
	}


	/**
	* Ends dialog when session is in remote mode
	*/
	function denyOnRemote($respons) {
		throw new \Exception("Access is denied. ".$respons->getCause());
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

	/**
	* @param GateKeeperRespons respons
	* @return String a nice message for end user
	*/
	function getNiceMessage($respons) {
		// TODO introduce i10n
		$msg = $this->l10n->t("You do not have access to this service.");
		$admMsg = $this->l10n->t("Please contact your administrator with theses informations: ");
		$fmt = array(
			'group.blacklisted' => "denied with uid=%s IP=%s.",
			'group.exclusion' 	=> "excluded with uid=%s IP=%s.",
			'not.whitelisted' 	=> "not allowed with uid=%s IP=%s.",
			);
		$key = $respons->getCause();
		$sfmt = false;
		if ( isset($fmt[$key])) $sfmt = $fmt[$key];
		if ( $sfmt ) {
			$ctxMsg = sprintf( $this->l10n->t( $sfmt, array( $respons->getUid(), $this->getIPAddress() ) ));
			 
		} else {
			$ctxMsg = 'cause: -'.$key;
		}
		return "$msg $admMsg $ctxMsg";
	}	


	function getIPAddress() {
		$ip = "unknown";
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}