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
    	return 'SELECT * FROM `'.$this->getTableName().'` WHERE `name`=? AND `mode`=?';

    }

    public function modeToInt($mode) {
    	return ($mode) ? GK::WHITELIST_GROUP_TYPE : GK::BLACKLIST_GROUP_TYPE;
    }

    public function isGroupInMode($groupName, $mode) {
    	$sql = $this->getCommonSQL();
		$params = array($groupName, $mode);
		return $this->commonAnswer($sql, $params);
    }

    public function commonAnswer($sql, $params) {
    	$result = $this->execute($sql, $params);
		$row = $result->fetch();
		if ( $row === false || $row === null ) return false;
		return true;
    }


    public function isManagerGroup($g) {
    	$sql = $this->getCommonSQL();
		$params = array($groupName, GK::MANAGER_MODE_INT);
		return $this->commonAnswer($sql, $params);
    }


    public function findGroupNamesInMode($mode) {
         $sql = 'SELECT name FROM `'.$this->getTableName().'` WHERE  `mode`=?';
         $params = array($mode);
        return $this->getNames($sql, $params, $limit, $offset);
    }

    public function findExclusionGroups() {
        $sql = 'SELECT name FROM `'.$this->getTableName().'` WHERE  `mode`=?';
        $params = array(GK::EXCLUSION_GROUP_TYPE);
        return $this->getNames($sql, $params, $limit, $offset);
    }


    public function findGroupsInMode($mode, $limit=null, $offset=null) {
         $sql = 'SELECT * FROM `'.$this->getTableName().'` WHERE  `mode`=?';
         $params = array($mode);
        return $this->findEntities($sql, $params, $limit, $offset);
    }    

    public function findGroupNamesLike($value, $limit=null, $offset=null) {
        $sql = 'SELECT name FROM `'.$this->getTableName().'` WHERE `name` LIKE ?';
        $params = array('%'.$value.'%');
        return $this->getNames($sql, $params, $limit, $offset);
    }

    protected function getNames($sql, $params, $limit, $offset) {
        $result = $this->execute($sql, $params, $limit, $offset);

        $names = array();
        
        while($row = $result->fetch()){
            $names[] = $row['name'];
        }

        return $names;
    }

}
