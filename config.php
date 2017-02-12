<?php

// set true for debug mode
// set false or comment whole line for producton mode
define('DEBUGMODE', true);

// set localisation settings
// @todo it shuld be done by localisation manager
date_default_timezone_set('Pacific/Nauru');
setlocale(LC_TIME, 'Polish_Poland');


/*
 * ===============================================================
 * you can change the format of API result
 * ===============================================================
*/
define('API_RESULT_FORAMT', 'json');
//define('API_RESULT_FORAMT', 'xml');

define('INVEST_PERIOD', 10); // in years
define('INVEST_STOCK', 'gold');
define('START_AMOUNT', 600000);
