<?php
namespace TwentyTwoEstore\Libs;

class Logger
{
	private static $instance;

	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Logger();
		}
		return self::$instance;
	}

	// block a direct instatiation
	private function __construct()
	{ }

	public function logException (\Exception $ex)
	{
		$isDisplay = ini_get('display_errors');
		if (!empty($isDisplay)){
			echo $ex->getMessage();
		}

		$dirPath = $this->openLogDir();
		$file = fopen($dirPath.DIRECTORY_SEPARATOR.strtr(get_class($ex), '\\', '-') . '.txt', 'a');

		fwrite($file, $this->genLogHeader($ex->getMessage()));
		while (isset ($ex)) {
			fwrite($file, PHP_EOL.PHP_EOL."Error code:\t\t".$ex->getCode());
			fwrite($file, PHP_EOL."File:\t\t\t".$ex->getFile());
			fwrite($file, PHP_EOL."Line:\t\t\t".$ex->getLine());
			fwrite($file, PHP_EOL.PHP_EOL.'Stack trace:');
			fwrite($file, PHP_EOL.PHP_EOL.str_replace(array("\r\n", "\r", "\n"), PHP_EOL, $ex->getTraceAsString()));
			$ex = $ex->getPrevious ();
			if (isset ($ex)) {
				fwrite($file, PHP_EOL.'------------------------------------------------'.PHP_EOL);
			}
		};
		fwrite($file, PHP_EOL.'================================================'.PHP_EOL);
		fclose($file);
	}

	private function genLogHeader ($message)
	{
		global $_SERVER;

		$date = strftime("%c", time());
		$header = "Date:\t\t\t$date"
			.PHP_EOL.PHP_EOL."REMOTE_ADDR:\t".$_SERVER['REMOTE_ADDR']
			.PHP_EOL."SERVER_ADDR:\t".$_SERVER['SERVER_ADDR']
			.PHP_EOL."REQUEST_URI:\t".$_SERVER['REQUEST_URI']
			.PHP_EOL."QUERY_STRING:\t".$_SERVER['QUERY_STRING']
			.(isset($_SERVER['HTTP_REFERER']) ? PHP_EOL."HTTP_REFERER:\t".$_SERVER['HTTP_REFERER'] : null)
			.PHP_EOL.PHP_EOL."Message:\t\t".str_replace(array('<br>', '<br />'), PHP_EOL.PHP_EOL, $message);
		return $header;
	}

	/**
	 * Returns the path to the existing dir
	 * @throws IOException
	 * @return string
	 */
	private function openLogDir ()
	{
		$dirPath = str_replace(DIRECTORY_SEPARATOR.'Libs', DIRECTORY_SEPARATOR.'logs', __DIR__);

		if (is_dir($dirPath)){
			return $dirPath;
		}

		if (!mkdir($dirPath, 0700)) {
			throw new IOException("Can't create a directory '$dirPath'");
		}
		return $dirPath;
	}
}
