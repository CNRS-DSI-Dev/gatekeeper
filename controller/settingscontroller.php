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
use \OCA\GateKeeper\AppInfo\GKConstants as GK;
/**
*
*/
class SettingsController extends Controller {

	var $appConfig;
	var $accessObjectMapper;

	public function __construct($request, $appConfig) {
		parent::__construct('gatekeeper', $request);
		$this->appConfig = $appConfig;
		$this->accessObjectMapper = $accessObjectMapper;
	}


	/**
	* @Ajax
	*/
	public function setMode() {
		\OC_Util::checkAdminUser();
		$params = $this->request->post;
		$value = isset($params['value']) ? $params['value'] : null;

		if ( is_null($value) )  {
			throw new  \Exception("mode value can not be null", 1);
		}
		if ( ! GK::checkMode($value)) {
			throw new  \Exception("mode value is incorrect", 1);	
		}
		$this->appConfig->setValue('gatekeeper','mode',$value);
		return new JSONResponse( array('status' => 'ok') );
	}

	/**
	* @Ajax
	*/
	public function searchGroup() {
		\OC_Util::checkAdminUser();
		$params = $this->request->get;
		$value = isset($params['term']) ? $params['term'] : null;
		if ( is_null($value) )  {
			return new JSONResponse( array('status' => 'no_criteria') );
		}
		// Comment avoir les groupes ?
		
		// $this->accessObjectMapper->findGroupLike($value);
		return new JSONResponse( array('status' => 'ok') );
	}	
}