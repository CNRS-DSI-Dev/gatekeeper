<?php 
openlog('gatekeeper', LOG_NDELAY, LOG_USER);
register_shutdown_function('closelog');
syslog(LOG_NOTICE, 'test');
?>
