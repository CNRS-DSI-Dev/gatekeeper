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
$selected = array(GK::WHITELIST_MODE => '', GK::BLACKLIST_MODE => '', GK::OPENED_GATE_MODE => '');
$selected[$_['selected']] = 'selected';
?>
<div class="section">
	<h2><?php p($l->t('GateKeeper'));?></h2>
	<pre id="gk_display_error" class="gk_error"></pre>
	<form id="gatekeeperForm">
		<div id="gkTabs">
			<ul>
				<li><a href="#gkTabs-1">General</a></li>
				<li><a href="#gkTabs-2">WHITELIST</a></li>
				<li><a href="#gkTabs-3">BLACKLIST</a></li>
			</ul>
			<fieldset id="gkTabs-1">
				<div class="block">
					<label class="label" for="mode"><?php p($l->t('Mode'));?></label>
					<select id="selectMode" name="mode">
						<option value="<?php p(GK::WHITELIST_MODE);?>" <?php p($selected[GK::WHITELIST_MODE]);?>><?php p($l->t('whitelist'));?></option>
						<option value="<?php p(GK::BLACKLIST_MODE);?>" <?php p($selected[GK::BLACKLIST_MODE]);?>><?php p($l->t('blacklist'));?></option>
						<option value="<?php p(GK::OPENED_GATE_MODE);?>" <?php p($selected[GK::OPENED_GATE_MODE]);?>><?php p($l->t('opened'));?></option>
					</select>
				</div>
			</fieldset>
			<fieldset id="gkTabs-2">
				<div class="block">
					<input type="text" id="gkGroupName_whitelist" placeholder="search group">
					<button type="button" id="gkAddButton_whitelist"><?php p($l->t('Add to list'));?></button>
				</div>
				<button type="button" id="gkLoadButton_whitelist"><?php p($l->t('load list'));?></button>
				<ul id="gkList_whitelist">
				</ul>
			</fieldset>
			<fieldset id="gkTabs-3">
				<div class="block">
					<input type="text" id="gkGroupName_blacklist" placeholder="search group">
					<button type="button" id="gkAddButton_blacklist"><?php p($l->t('Add to list'));?></button>
				</div>
				<button type="button" id="gkLoadButton_blacklist"><?php p($l->t('load list'));?></button>
				<ul id="gkList_blacklist">
				</ul>
			</fieldset>
		</div>
	</form>
	<div id="gk_display_info"></div>
</div>