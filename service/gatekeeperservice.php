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

class GateKeeperService {

	var $mode;
	var $session;
	var $accessObjectMapper;
	var $groupManager;
	
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
	}


	/**
	 * @param \OC\User\User $user
	 * @return \OCA\GateKeeper\Service\GateKeeperRespons
	 */
	public function checkUserAllowances($user) {
		$status = $this->session->get('gk_status');
		if ( ! is_null($status) && strcmp($status, 'ok') === 0 ) return GateKeeperRespons::yetGranted();
		if ( ! is_null($status) && strcmp($status, 'ko') === 0 ) return GateKeeperRespons::yetDenied();

		$respons = $this->isUserAllowed($user);
		$status = ( $respons->isAllow() ) ? 'ok': 'ko';
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

		if ( $whiteList && $this->isUidAllowed( $uid) ) {
			return $respons;
		}
		if ( $blackList && ! $this->isUidAllowed( $uid ) ) {
			return $respons->deny('uid.blacklisted', $uid);
		}

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

	public function isUidAllowed($uid) {
		$inMode = $this->accessObjectMapper->isUidInMode($uid, $this->mode);
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