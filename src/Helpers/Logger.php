<?php declare(strict_types = 1);

/**
 * Logger.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Tools!
 * @subpackage     Helpers
 * @since          1.0.0
 *
 * @date           08.04.23
 */

namespace FastyBird\Core\Tools\Helpers;

use DirectoryIterator;
use Nette;
use RuntimeException;
use Throwable;
use Tracy;
use UnexpectedValueException;
use function array_map;
use function array_merge;
use function date;
use function md5;
use function serialize;
use function str_contains;
use function strtr;
use function substr;
use const DIRECTORY_SEPARATOR;

/**
 * Logger helpers
 *
 * @package        FastyBird:Tools!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Logger
{

	use Nette\SmartObject;

	/**
	 * @return array<array<string, string|int>>
	 */
	public static function buildException(Throwable $ex, bool $render = true): array
	{
		if ($render) {
			try {
				$blueScreen = new Tracy\BlueScreen();
				$blueScreen->renderToFile($ex, self::getExceptionFile($ex));
			} catch (Throwable) {
				// Blue scree could not be saved
			}
		}

		return self::processAllExceptions($ex);
	}

	/**
	 * @return array<array<string, string|int>>
	 */
	private static function processAllExceptions(Throwable $ex): array
	{
		$result = [
			[
				'message' => $ex->getMessage(),
				'code' => $ex->getCode(),
			],
		];

		if ($ex->getPrevious() !== null) {
			$result = array_merge($result, self::processAllExceptions($ex->getPrevious()));
		}

		return $result;
	}

	/**
	 * @throws UnexpectedValueException
	 * @throws RuntimeException
	 */
	private static function getExceptionFile(Throwable $ex): string
	{
		$data = [];

		foreach (Tracy\Helpers::getExceptionChain($ex) as $exception) {
			$data[] = [
				$exception::class, $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(),
				array_map(static function (array $item): array {
					unset($item['args']);

					return $item;
				}, $exception->getTrace()),
			];
		}

		$hash = substr(md5(serialize($data)), 0, 10);
		$dir = strtr(FB_LOGS_DIR . '/', '\\/', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);

		foreach (new DirectoryIterator(FB_LOGS_DIR) as $file) {
			if (str_contains($file->getBasename(), $hash)) {
				return $dir . $file;
			}
		}

		return $dir . 'exception--' . date('Y-m-d--H-i') . "--$hash.html";
	}

}
