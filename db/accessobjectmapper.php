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

namespace OCA\GateKeeper\Db;

use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;
use \OCA\GateKeeper\AppInfo\GKConstants as GK;


class AccessObjectMapper extends Mapper {


	public function __construct(IDb $db) {
        parent::__construct($db, 'gk_access_object');
    }

    public function getCommonSQL() {
    	return 'SELECT * FROM `'.$this->getTableName().'` WHERE `name`=? AND `kind`=? AND `mode`=?';
    }

    public function modeToInt($mode) {
    	return ($mode) ? GK::WHITELIST_MODE : GK::BLACKLIST_MODE;
    }

    public function isGroupInMode($groupName, $mode) {
    	$sql = $this->getCommonSQL();
		$params = array($groupName, GK::GROUP_KIND, $mode);
		return $this->commonAnswer($sql, $params);
    }

    public function commonAnswer($sql, $params) {
    	$result = $this->execute($sql, $params);
		$row = $result->fetch();
		if ( $row === false || $row === null ) return false;
		return true;
    }

    public function isUidInMode($uid, $mode) {
    	$sql = $this->getCommonSQL();
		$params = array($uid, GK::UID_KIND, $mode);
		return $this->commonAnswer($sql, $params);
    }

    public function isManagerGroup($g) {
    	$sql = $this->getCommonSQL();
		$params = array($groupName, GK::GROUP_KIND, GK::MANAGER_MODE);
		return $this->commonAnswer($sql, $params);
    }

}
