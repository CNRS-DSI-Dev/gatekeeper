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

if ( !  \OC_App::isEnabled( 'gatekeeper') ) {
	return;
}
\OCP\Util::writeLog('gatekeeper','appInfo.app',\OCP\Util::ERROR);
\OCP\App::registerAdmin('gatekeeper','settings/admin');


$app = new GateKeeperConfigApp();

if ( !$app->isGateActivated() ) {
	return;
}

$c = $app->getContainer();

//$app->getGroupManager()->addBackend( new \OC_Group_Database() );

$hooks = $c->query('GateKeeperHooks');
$hooks->registerForUserEvents( $app->getUserSession());
$hooks->registerForGroupEvents( $app->getGroupManager());

if ( $c->isAdminUser() ) {
	return;
}
$c->query('Interceptor')->run();