<?php

define('GW2RAIDAR_URL', 'https://www.gw2raidar.com/');
define('GW2RAIDAR_USER', '<username>');
define('GW2RAIDAR_PASS', '<password>');

define('DPSREPORT_URL', 'https://dps.report/');
define('DPSREPORT_TOKEN', '<token>');

define('ACCOUNTS', [
    '<name_tab_1>' => [
        'keys' => [
            'Account.1111' => '<api_key>',
            'Account.2222' => '<api_key>',
        ],
    ],
    '<name_tab_2>' => [
        'summary' => false,
        'keys'    => [
            'Account.3333' => '<api_key>',
        ],
    ],
]);

define('LOGS_DEFAULT_PAGE_LENGTH', 20);

define('PROCESS_MAX_EXECUTION_TIME', 900);
define('PROCESS_INTERVAL_INCREMENT', 300);
define('PROCESS_INTERVAL_MAXIMUM', 4 * 3600);
define('PROCESS_TTL_BEFORE_DISABLED', 7 * 86400);

define('FAIL_LOG_MAX_RETENTION', 86400 * 15);
define('KILL_LOG_MAX_RETENTION', 86400 * 365);

define('UPLOAD_MAX_FILE_SIZE', 10 * 1024 * 1024);
define('UPLOAD_NO_AUTH_PARAMETER_NAME', '<random_string_to_generate>');
