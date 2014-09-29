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
$selected = array(GK::WHITELIST_MODE => '', GK::BLACKLIST_MODE => '', GK::OPENED_GATE_MODE => '', GK::MINIMAL_MODE => '');
$selected[$_['selected']] = 'checked';
function select($key) { 
	p($selected[$key]); 
}
?>
<div class="section">
	<h2><?php p($l->t('GateKeeper'));?></h2>
	<pre id="gk_display_error" class="gk_error"></pre>
	<form id="gatekeeperForm">
		<fieldset>
				<label class="label" for="mode"><?php p($l->t('Select Mode'));?></label>
				<div class="block">
					<label class="label" for="mode"><?php p($l->t('White List'));?></label>
					<input type="radio" name="mode"  value="<?php p(GK::WHITELIST_MODE);?>" <?php p($selected[GK::WHITELIST_MODE]);?>>
						<?php p($l->t('Only groups in this white list are allowed EXCEPT those in EXCLUSION list'));?>
				</div>
				<div class="block">
					<label class="label" for="mode"><?php p($l->t('Black List'));?></label>
					<input type="radio" name="mode"  value="<?php p(GK::BLACKLIST_MODE);?>" <?php p($selected[GK::BLACKLIST_MODE]);?>>
						<?php p($l->t('Only groups in this black list are denied PLUS those in EXCLUSION list'));?>
				</div>
				<div class="block">
					<label class="label" for="mode"><?php p($l->t('Minimal'));?></label>
					<input type="radio" name="mode"  value="<?php p(GK::MINIMAL_MODE);?>" <?php p($selected[GK::MINIMAL_MODE]);?>>
						<?php p($l->t('Only groups in EXCLUSION list are denied'));?>
				</div>
				<div class="block">
					<label class="label" for="mode"><?php p($l->t('Opengate'));?></label>
					<input type="radio" name="mode"  value="<?php p(GK::OPENED_GATE_MODE);?>" <?php p($selected[GK::OPENED_GATE_MODE]);?>>
						<?php p($l->t('No checking is done'));?>
				</div>
		</fieldset>
		<!-- TABULATIONS -->
		<div id="gkTabs">

			<ul>
				<li><a href="#gkTabs-1"><?php p($l->t('White List'));?></a></li>
				<li><a href="#gkTabs-2"><?php p($l->t('Black List'));?></a></li>
				<li><a href="#gkTabs-3"><?php p($l->t('Exclusion List'));?></a></li>			
			</ul>

			<fieldset id="gkTabs-1">
				<div class="block">
					<input type="text" id="gkGroupName_whitelist" placeholder="<?php p($l->t('Enter Group Name'));?>">
					<button type="button" id="gkAddButton_whitelist"><?php p($l->t('Add to list'));?></button>
				</div>
				<button type="button" id="gkLoadButton_whitelist"><?php p($l->t('Display List'));?></button>
				<ul class="gk_ul_double" id="gkList_whitelist">
				</ul>
			</fieldset>
			<fieldset id="gkTabs-2">
				<div class="block">
					<input type="text" id="gkGroupName_blacklist" placeholder="<?php p($l->t('Enter Group Name'));?>">
					<button type="button" id="gkAddButton_blacklist"><?php p($l->t('Add to list'));?></button>
				</div>
				<button type="button" id="gkLoadButton_blacklist"><?php p($l->t('Display List'));?></button>
				<ul class="gk_ul_double" id="gkList_blacklist">
				</ul>
			</fieldset>
			<fieldset id="gkTabs-3">
				<div class="block">
					<input type="text" id="gkGroupName_exclusion" placeholder="<?php p($l->t('Enter Group Name'));?>">
					<button type="button" id="gkAddButton_exclusion"><?php p($l->t('Add to list'));?></button>
				</div>
				<button type="button" id="gkLoadButton_exclusion"><?php p($l->t('Display List'));?></button>
				<ul class="gk_ul_double" id="gkList_exclusion">
				</ul>
			</fieldset>			
		</div>
	</form>
	<div id="gk_display_info"></div>
	<div id="gk_translation" style="visibility: hidden;">
		<span name="mode_is_selected"><?php p($l->t('Mode {0} is selected'));?></span>
		<span name="group_removed_from"><?php p($l->t('Group {0} is removed from {1}'));?></span>
		<span name="group_added_in"><?php p($l->t('Group {0} is added in {1}'));?></span>
	</div>
</div>