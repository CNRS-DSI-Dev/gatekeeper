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
use \OCA\GateKeeper\AppInfo\GKConstants as GK;


$tmpl = new OCP\Template('gatekeeper', 'settings-admin');
$appName='gatekeeper';
$app = new OCA\GateKeeper\AppInfo\GateKeeperConfigApp();

$tmpl->assign('checked_mode', 	OCP\Config::getAppValue($appName, 'mode'));
$tmpl->assign('selected_logger', OCP\Config::getAppValue($appName, 'logger'));
$tmpl->assign('refreshDelay', OCP\Config::getAppValue($appName, 'refresh_delay'));


$accessObjectMapper = $app->getContainer()->query('AccessObjectMapper');
$whitelist = $accessObjectMapper->findGroupNamesInMode(GK::WHITELIST_GROUP_TYPE);
$blacklist = $accessObjectMapper->findGroupNamesInMode(GK::BLACKLIST_GROUP_TYPE);

$tmpl->assign('whitelist', $whitelist);
$tmpl->assign('blacklist', $blacklist);
return $tmpl->fetchPage();
