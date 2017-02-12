<?php
namespace TwentyTwoEstore;

use \TwentyTwoEstore\Libs;

require 'config.php';

if (defined('DEBUGMODE') && DEBUGMODE === true){
	ini_set('error_reporting', \E_ALL | \E_STRICT);
	ini_set('display_errors', true);
}
else{
	// disable E_NOTICE displaying
	ini_set('error_reporting', \E_ALL & ~\E_NOTICE);
}

require 'Libs'.DIRECTORY_SEPARATOR.'Logger.php';
$logger = Libs\Logger::instance();

try {
	// register class autoloader
	require_once 'Libs'.DIRECTORY_SEPARATOR.'LibManager.php';
	spl_autoload_register(array('\TwentyTwoEstore\Libs\LibManager', 'autoload'), true);

	// initialize common handlers
	Libs\ErrorHandlersRegister::init();

	// prepare exchange period dates
	$now = new \DateTime(); /* the timezone is determined by the config setting */

	$investStartDate = clone $now;
	$investPeriod = new \DateInterval('P'.INVEST_PERIOD.'Y');
	$investPeriod->invert = 1;
	$investStartDate->add($investPeriod);

	$contentParser = Libs\ContentParserFactory::produce(API_RESULT_FORAMT);

	$nbp = new Libs\Investor\NbpApiConnector($contentParser);
	$exchangeRates = $nbp->getGoldExchangeRates($investStartDate, $now);

	$trendDetector = new Libs\Investor\TrendDetector();
	$trends = $trendDetector->analyzeExchangeRates($exchangeRates);

	$investHelper = new Libs\Investor\InvestHelper(START_AMOUNT);
	$balances = $investHelper->investByTrends($trends);

	$view = new Libs\View('investmentHistory.phtml');
	$view->title('Investment History');
	$view->balances = $balances;

	require 'Views'.DIRECTORY_SEPARATOR.'layout.phtml';
	/*
	 * ===============================================================
	 * you can uncomment one of lines below to test error logging
	 * ===============================================================
	 */
// 	Libs\A::test();
// 	test();

} catch (\Exception $ex){
	$logger->LogException($ex);
}
