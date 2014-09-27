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

namespace OCA\GateKeeper\Service;
use \OCA\GateKeeper\AppInfo\GKConstants as GK;


class GateKeeperServiceTests extends \PHPUnit_Framework_TestCase { 

	var $service;
	var $session;
	var $accessObjectMapper;
	var $groupManager;
	var $user;

	public function setUp() {
		$this->accessObjectMapper = $this->getMockBuilder('OCA\GateKeeper\Db\AccessObjectMapper')
 								->disableOriginalConstructor()
								->getMock();

		$this->groupManager = 	$this->getMockBuilder('\OC\Group\Manager')
									->disableOriginalConstructor()
									->getMock();								
		$this->session = 	$this->getMockBuilder('\OCP\ISession')
									->disableOriginalConstructor()
									->getMock();								
		$this->user = $this->getMockBuilder('\OC\User\User')
								->disableOriginalConstructor()
								->getMock();

		$_SERVER['REQUEST_URI'] = '/something';


	}

	public function testIsGroupAllowed_WHITELIST() {
		//__setup
		$this->service = new GateKeeperService(
			GK::WHITELIST_MODE,
			$this->session,
			$this->accessObjectMapper,
			$this->groupManager
			);

		$mockParmMap = array(
			array('grp0', 	GK::WHITELIST_GROUP_TYPE, true),
			array('grp1', GK::WHITELIST_GROUP_TYPE, false)
			);
		$this->accessObjectMapper->expects( $this->any() )->method('isGroupInMode')->will( $this->returnValueMap($mockParmMap) ) ;

		
		$this->assertTrue($this->service->isGroupAllowed('grp0'));
		$this->assertTrue($this->service->isGroupAllowed('grp1') === false);


	}


	public function testIsGroupAllowed_BACKLIST() {
		//__setup
		$this->service = new GateKeeperService(
			GK::BLACKLIST_MODE,
			$this->session,
			$this->accessObjectMapper,
			$this->groupManager
			);

		$mockParmMap = array(
			array('grp0', 	GK::BLACKLIST_GROUP_TYPE, true),
			array('grp1', GK::BLACKLIST_GROUP_TYPE, false)
			);
		$this->accessObjectMapper->expects( $this->any() )->method('isGroupInMode')->will( $this->returnValueMap($mockParmMap) ) ;

		
		$this->assertTrue($this->service->isGroupAllowed('grp0') === false );
		$this->assertTrue($this->service->isGroupAllowed('grp1'));


	}

	public function testIsUserInMode_WHITELIST_group() {
				//__setup
		$this->setup_TEST_IsUserAllowed(GK::WHITELIST_MODE, 
			array(
				array('grp0', 	GK::WHITELIST_GROUP_TYPE, true),
				array('grp1', GK::WHITELIST_GROUP_TYPE, false)
			)
		);
		$this->groupManager->expects($this->any())
						->method('getUserGroupIds')
						->willReturn(array('grp0', 'grp1'));

		//__WEN__
		$respons = $this->service->isUserAllowed($this->user);

		$this->assertTrue( ! is_null($respons));
		$this->assertTrue( ! $respons->isDenied());

	}


	public function testIsUserInMode_WHITELIST_group_false() {
				//__setup
		$this->setup_TEST_IsUserAllowed(GK::WHITELIST_MODE, 
			array(
				array('grp1', GK::WHITELIST_GROUP_TYPE, false)
			)
		);
		$this->groupManager->expects($this->any())
						->method('getUserGroupIds')
						->willReturn(array('grp0', 'grp1'));

		//__WEN__
		$respons = $this->service->isUserAllowed($this->user);

		$this->assertTrue( ! is_null($respons));
		$this->assertTrue( $respons->isDenied());
		$this->assertEquals($respons->getCause(), 'not.whitelisted');
		$this->assertEquals($respons->getUid(), 'uid0');

	}

	/**
	* User is in WHITELIST but in EXCLUSION.
	* User must not have access!
	*/
	public function testIsUserInMode_WHITELIST_EXCLUSION() {
				//__setup
		$this->setup_TEST_IsUserAllowed(GK::WHITELIST_MODE, 
			array(
				array('grp0', 	GK::WHITELIST_GROUP_TYPE, true),
				array('grp1', GK::WHITELIST_GROUP_TYPE, false)
			)
		);
		$this->groupManager->expects($this->any())
						->method('getUserGroupIds')
						->willReturn(array('grp0', 'grp1', 'badguys'));

		$this->accessObjectMapper->expects( $this->any() )->method('findExclusionGroups')->willReturn( array('badguys') ) ;						

		//__WEN__
		$respons = $this->service->isUserAllowed($this->user);

		$this->assertTrue( ! is_null($respons));
		$this->assertTrue( $respons->isDenied(), "$respons");
		$this->assertEquals( $respons->getGroup(), 'badguys');

	}


	public function testIsUserInMode_BLACKLIST_group() {
				//__setup
		$this->setup_TEST_IsUserAllowed(GK::BLACKLIST_MODE, 
			array(
				array('grp0', GK::BLACKLIST_GROUP_TYPE, true),
				array('grp1', GK::BLACKLIST_GROUP_TYPE, false)
			)
		);
		$this->groupManager->expects($this->any())
						->method('getUserGroupIds')
						->willReturn(array('grp0', 'grp1'));

		//__WEN__
		$respons = $this->service->isUserAllowed($this->user);

		$this->assertTrue( ! is_null($respons));
		$this->assertTrue( $respons->isDenied());
		$this->assertEquals($respons->getCause(), 'group.blacklisted');
		$this->assertEquals($respons->getGroup(), 'grp0');

	}

	public function testIsUserInMode_BLACKLIST_group_none() {
				//__setup
		$this->setup_TEST_IsUserAllowed(GK::BLACKLIST_MODE, 
			array(
				array('grp0', 	GK::BLACKLIST_GROUP_TYPE, false),
				array('grp1', GK::BLACKLIST_GROUP_TYPE, false)
			)
		);
		$this->groupManager->expects($this->any())
						->method('getUserGroupIds')
						->willReturn(array('grp0', 'grp1'));

		//__WEN__
		$respons = $this->service->isUserAllowed($this->user);

		$this->assertTrue( ! is_null($respons));
		$this->assertTrue( ! $respons->isDenied());
	}


	function setup_TEST_IsUserAllowed($mode, $groupMockMap) {
		$this->service = new GateKeeperService(
			$mode,
			$this->session,
			$this->accessObjectMapper,
			$this->groupManager
			);

		$this->user->expects($this->any())->method('getUID')->willReturn('uid0');

		$this->accessObjectMapper->expects( $this->any() )->method('isGroupInMode')->will( $this->returnValueMap($groupMockMap) ) ;
	}
}