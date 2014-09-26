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

namespace OCA\GateKeeper\Service;
use \OCA\GateKeeper\AppInfo\GKConstants as GK;
use OCA\GateKeeper\Lib\GKHelper;

class GateKeeperService {

	var $mode;
	var $session;
	var $accessObjectMapper;
	var $groupManager;
	var $remote;
	var $delay = 30;
	
	public function __construct($mode, $session, $accessObjectMapper, $groupManager) {
		$intMode = 0;
		if ( is_string($mode) ) {
			$intMode = GK::modeToInt($mode);
		} else if ( ! GK::checkModeInt($mode) ){
			throw new \Exception("Mode after check $mode is not valid", 1);	
		} else {
			$intMode = $mode;
		}
		if ( ! $intMode  ) {
			throw new \Exception("Mode $mode is not valid", 1);	
		}	
		$this->mode = $intMode;
		$this->session = $session;
		$this->accessObjectMapper = $accessObjectMapper;
		$this->groupManager = $groupManager;
		$this->cache = array();
		$this->remote = GKHelper::isRemote();
	}



	public function hasToRefresh() {
		$refresh = true;
		if ( $this->remote ) {
			$now = time();
			\OCP\Util::writeLog('gatekeeper::hasToRefresh', 'now='.$now, \OCP\Util::DEBUG);
			$timestamp = $this->session->get('gk_remote_ts');
			// creation
			if ( is_null($timestamp)) {
				$this->session->set('gk_remote_ts', $now);
			} else if ( $now - $timestamp < $this->delay ) {
				$refresh = false;
			} else {
				\OCP\Util::writeLog('gatekeeper::hasToRefresh', 'TRUE '.($now - $timestamp).', ts='.$timestamp, \OCP\Util::DEBUG);
				$this->session->set('gk_remote_ts', $now);
			}
		}
		return $refresh;
	}

	public function startCycle($uid) {
		if ( $this->hasToRefresh()) {
			$this->session->remove('gk_status');
		}
	}

	public function endCycle() {
		if ( $this->hasToRefresh()) {
			$this->session->remove('gk_status');
		}
	}	

	/**
	 * @param \OC\User\User $user
	 * @return \OCA\GateKeeper\Service\GateKeeperRespons
	 */
	public function checkUserAllowances($user) {
		$status = $this->session->get('gk_status');
		\OCP\Util::writeLog('gatekeeper::checkUserAllowances','ANTE status='.$status, \OCP\Util::DEBUG);
		if ( ! $this->hasToRefresh() ) {
			if ( ! is_null($status) && $status == 'ok' ) return GateKeeperRespons::yetGranted();
			if ( ! is_null($status) && $status == 'ko' ) return GateKeeperRespons::yetDenied();
		}
		$respons = $this->isUserAllowed($user);
		$status = ( $respons->isAllow() ) ? 'ok': 'ko';
		\OCP\Util::writeLog('gatekeeper::checkUserAllowances','POST status='.$status, \OCP\Util::DEBUG);
		$this->session->set('gk_status', $status);
		return $respons;
		
	}



	/**
	 * @param \OC\User\User $user
	 * @return \OCA\GateKeeper\Service\GateKeeperRespons
	 */
	public function isUserAllowed($user) {
		$respons = new GateKeeperRespons();

		$groupIds = $this->groupManager->getUserGroupIds($user);

		$whiteList = $this->isModeAllow();
		$blackList = ! $whiteList;
		$uid = $user->getUID() ;

		if ( ! is_null($groupIds) && ! empty($groupIds) ) {
			foreach ($groupIds as $g) {
				if ( $whiteList && $this->isGroupAllowed($g) ) {
					return $respons;
				}
				if ( $blackList && ! $this->isGroupAllowed($g) ) {
					return $respons->deny('group.blacklisted', $uid, $g);
				}
			}
		}
		if ( $whiteList ) {
			return $respons->deny('not.whitelisted',$uid);
		}
		return $respons;
	}


	public function isModeAllow() {
		return $this->mode === GK::WHITELIST_MODE_INT;
	}

	public function isGroupAllowed($groupName) {
		$inMode = $this->accessObjectMapper->isGroupInMode($groupName, $this->mode);
		return $this->answer($inMode);
	}

	public function answer($inMode) {
		if ( $this->isModeAllow() ) {
			return $inMode;
		} else {
			return ! $inMode;
		}
	}

	public function isGateKeeperManager($user) {
		$groupIds = $this->groupManager->getUserGroupIds( $user );
		foreach ($groupIds as $g) {
			if ( ! array_key_exists($g, $this->cache)) {
				$this->cache[$g] = $this->accessObjectMapper->isManagerGroup($g);
			}
			if ( $this->cache[$g] ) {
				return true;
			}
		}
		return false;
	}
}