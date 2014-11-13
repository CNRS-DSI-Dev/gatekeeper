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


namespace OCA\GateKeeper\Controller;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\GateKeeper\AppInfo\GKConstants as GK;
use OCA\GateKeeper\Db\AccessObject;
/**
*
*/
class SettingsController extends Controller {

	var $appConfig;
	var $accessObjectMapper;
	var $groupManager;
	var $loggers = array('owncloud','syslog','none');

	public function __construct($request, $appConfig, $accessObjectMapper, $groupManager) {
		parent::__construct('gatekeeper', $request);
		$this->appConfig = $appConfig;
		$this->accessObjectMapper = $accessObjectMapper;
		$this->groupManager = $groupManager;
	}


	/**
	* @Ajax
	*/
	public function setLogger() {
		\OC_Util::checkAdminUser();
		$params = $this->request->post;
		$value = isset($params['logger']) ? $params['logger'] : null;
		if ( is_null($value) )  {
			return new JSONResponse( array("msg" => "value is null"), Http::STATUS_BAD_REQUEST);
		}
		if ( ! in_array($value, $this->loggers)) {
			return new JSONResponse( array("msg" => "value is incorrect"), Http::STATUS_BAD_REQUEST);
		}
		$this->appConfig->setValue('gatekeeper','logger',$value);
		return new JSONResponse( array('status' => 'ok') );
	}



	/**
	* @Ajax
	*/
	public function setMode() {
		\OC_Util::checkAdminUser();
		$params = $this->request->post;
		$value = isset($params['value']) ? $params['value'] : null;

		if ( is_null($value) )  {
			throw new  \Exception("mode $value can not be null", 1);
		}
		if ( ! GK::checkMode($value)) {
			throw new  \Exception("mode $value is incorrect", 1);	
		}
		$this->appConfig->setValue('gatekeeper','mode',$value);
		return new JSONResponse( array('status' => 'ok') );
	}

	/**
	* @Ajax
	*/
	public function setDelay() {
		\OC_Util::checkAdminUser();
		$params = $this->request->post;
		$value = isset($params['delay']) ? $params['delay'] : null;
		$intVal = 0;
		if ( is_numeric($value))  {
			$intVal = intVal();
		} else {
			throw new  \Exception("delay $value is incorrect", 1);	
		}

		if ( $intVal < 0 )  {
			$this->appConfig->deleteKey('gatekeeper','refresh_delay');
		} else {
			$this->appConfig->setValue('gatekeeper','refresh_delay',strval($intVal));
		}
		return new JSONResponse( array('status' => 'ok') );
	}	

	/**
	* @Ajax
	*/
	public function searchGroup() {
		\OC_Util::checkAdminUser();
		$params = $this->request->get;
		$kind = isset($params['kind']) ? $params['kind'] : null;
		if ( is_null($kind) )  {
			return new JSONResponse( array("msg" => "criteria is not specified"), Http::STATUS_BAD_REQUEST);
		}
 		if ( ! GK::checkGroupTypeLabel($kind) ) {
			return new JSONResponse( array("msg" => "mode is not valid"), Http::STATUS_BAD_REQUEST);
		}

		$groups = $this->accessObjectMapper->findGroupsInMode(GK::modeToInt($kind));
		$array = array();
		if ( $groups ) {
			foreach ($groups as $group) {
				$array[] = array('id' => $group->getId(), 'name' => $group->getName());
			}
		}
		return new JSONResponse( $array );
	}	


	/**
	* @Ajax
	*/
	public function manageGroup() {
		\OC_Util::checkAdminUser();
		$params = $this->request->post;
		$id = isset($params['group']) ? $params['group'] : null;
		$name = isset($params['name']) ? $params['name'] : null;
		$action = isset($params['action']) ? $params['action'] : null;
		$groupType = isset($params['gt']) ? $params['gt'] : null;

		if ( is_null($action) || is_null($groupType)) {
			return new JSONResponse( array("msg" => "Not specified action or group type"), Http::STATUS_BAD_REQUEST);
		}

		if ( ! GK::checkGroupType($groupType) ) {
			return new JSONResponse( array("msg" => "Group type ${groupType} is not valid"), Http::STATUS_BAD_REQUEST);
		}
		switch ($action) {
			case 'rm':
				if ( is_null($id)) {
					return new JSONResponse( array("msg" => "Not specified id"), Http::STATUS_BAD_REQUEST);
				}
				$ao = AccessObject::fromParams(array('id' => $id));
				$this->accessObjectMapper->delete($ao);
				break;
			case 'add':
				if ( is_null($name)) {
					return new JSONResponse( array("msg" => "Not specified name"), Http::STATUS_BAD_REQUEST);
				}
				if( $this->accessObjectMapper->isGroupInMode($name, $groupType) ) {
					return new JSONResponse( array("msg" => "Group $name already exists in this list"), Http::STATUS_BAD_REQUEST);
				} else {
					$ao = AccessObject::fromParams(array('name' => $name, 'mode' => $groupType));
					$this->accessObjectMapper->insert($ao);
					$id = $ao->getId();
				}
				break;				
			
			default:
				# code...
				break;
		}
		return new JSONResponse( array('id' => $id));

	}
}