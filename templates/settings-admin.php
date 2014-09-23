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

\OCP\Util::addScript('gatekeeper', 'admin');
\OCP\Util::addStyle('gatekeeper', 'gk');
?>
<div class="section">
	<h2><?php p($l->t('GateKeeper'));?></h2>
	<pre id="gk_settingsError" class="gk_error"></pre>
	<form id="gatekeeperFormID">
		<div class="block">
		<label class="label" for="mode"><?php p($l->t('Mode'));?></label>
			<select id="selectModeID" name="mode">
				<option value="<?php p(GK::WHITELIST_MODE);?>"><?php p($l->t('whitelist'));?></option>
				<option value="<?php p(GK::BLACKLIST_MODE);?>"><?php p($l->t('blacklist'));?></option>
				<option value="<?php p(GK::OPENED_GATE_MODE);?>"><?php p($l->t('opened'));?></option>
			</select>
		</div>
		<div class="block">
			<input id="searchGroupFieldID" type="text" name="search_group" value="search a group">
		</div>
	</form>
	<div id="gk_settingsEcho"></div>
</div>