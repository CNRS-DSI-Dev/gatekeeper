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
namespace OCA\GateKeeper\Lib;
use \OC_Log;

class SyslogDenyLogger implements DenyLogger {

	static protected $levels = array(
		OC_Log::DEBUG => LOG_DEBUG,
		OC_Log::INFO => LOG_INFO,
		OC_Log::WARN => LOG_WARNING,
		OC_Log::ERROR => LOG_ERR,
		OC_Log::FATAL => LOG_CRIT,
	);	

	static private $_instance = null;


	private function __construct() {

	}

	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new SyslogDenyLogger();
			self::init();
		}
		return self::$_instance;
	}

	public function write($message, $level=OC_Log::INFO) {
		self::doWrite($message, $level);
	}

	

	/**
	 * Init class data
	 */
	private static function init() {
		openlog('gatekeeper', LOG_NDELAY, LOG_USER);
		// Close at shutdown
		register_shutdown_function('closelog');
	}

	private static function doWrite($message, $level=OC_Log::INFO ) {
		$syslog_level = self::$levels[$level];
		syslog($syslog_level, $message);
	}
}
