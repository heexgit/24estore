<?php
namespace TwentyTwoEstore\Libs;

final class LibManager
{
	public static function autoload ($className)
	{
		if (empty($className)) {
			require_once 'ExecuteException.php';
			throw new ExecuteException("\$className can't be empty");
		}

		if (strpos($className, 'Decimal') === 0)
			$classPath = str_replace('Libs', 'Decimal', __DIR__).str_replace('Decimal\\', DIRECTORY_SEPARATOR, $className).'.php';
		else
			$classPath = __DIR__.str_replace(array('TwentyTwoEstore\\Libs', '\\'), array('', DIRECTORY_SEPARATOR), $className).'.php';

		if (!file_exists($classPath)){
			require_once 'IOException.php';
			throw new IOException("'$classPath' file is not readable or doesn't exist");
		}

		require $classPath;
	}
}
