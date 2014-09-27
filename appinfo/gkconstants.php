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

class GKConstants {
	
	const WHITELIST_GROUP_TYPE 	= 1;
	const BLACKLIST_GROUP_TYPE 	= 2;
	const EXCLUSION_GROUP_TYPE = 3;
	const MANAGER_MODE_INT	= 9;
	const OPENED_GATE_MODE_INT= 8;
	const WHITELIST_MODE = 'whitelist';
	const BLACKLIST_MODE = 'blacklist';
	const OPENED_GATE_MODE = 'opened';
	const MINIMAL_MODE = 'exclusion_only';
	const EXCLUSION_GROUP_LABEL = 'exclusion';


	public static function checkMode($mode) {
		if ($mode == self::WHITELIST_MODE	 
			|| $mode == self::BLACKLIST_MODE 
			|| $mode == self::OPENED_GATE_MODE
			|| $mode == self::MINIMAL_MODE ) { 
			return true;
		}
		return false;
	}

	public static function checkGroupTypeLabel($mode) {
		if ($mode == self::WHITELIST_MODE	 
			|| $mode == self::BLACKLIST_MODE 
			|| $mode == self::EXCLUSION_GROUP_LABEL ) { 
			return true;
		}
		return false;
	}

	public static function checkGroupType($mode) {
		if ($mode == self::WHITELIST_GROUP_TYPE 
			|| $mode == self::BLACKLIST_GROUP_TYPE 
			|| $mode == self::EXCLUSION_GROUP_TYPE ) { 
			return $mode;
		}
		return false;
	}	

	static function modeToInt($mode) {
		if ( self::checkGroupType($mode)) return $mode;
		if( $mode == self::WHITELIST_MODE) $intMode = self::WHITELIST_GROUP_TYPE;
		if( $mode == self::BLACKLIST_MODE) $intMode = self::BLACKLIST_GROUP_TYPE;
		if( $mode == self::EXCLUSION_GROUP_LABEL) $intMode = self::EXCLUSION_GROUP_TYPE;
		if( $mode == self::OPENED_GATE_MODE) $intMode = 0;
		return $intMode;
	}
}