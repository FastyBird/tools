<?php declare(strict_types = 1);

/**
 * DataType.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Tools!
 * @subpackage     Utilities
 * @since          1.0.0
 *
 * @date           05.12.20
 */

namespace FastyBird\Core\Tools\Utilities;

use FastyBird\Core\Tools\Exceptions;
use FastyBird\Core\Tools\Formats;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Throwable;
use function floatval;
use function intval;
use function sprintf;

/**
 * Data type helpers
 *
 * @package        FastyBird:Tools!
 * @subpackage     Utilities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DataType
{

	/**
	 * @throws Exceptions\InvalidState
	 */
	public static function inferNumberDataType(
		Formats\NumberRange $format,
		float|int|null $step = null,
		MetadataTypes\DataType|null $fallback = null,
	): MetadataTypes\DataType
	{
		if (
			$format->getMinDataType() !== null
			|| $format->getMaxDataType() !== null
		) {
			return match ($format->getMinDataType() ?? $format->getMaxDataType()) {
				MetadataTypes\DataTypeShort::CHAR => MetadataTypes\DataType::CHAR,
				MetadataTypes\DataTypeShort::UCHAR => MetadataTypes\DataType::UCHAR,
				MetadataTypes\DataTypeShort::SHORT => MetadataTypes\DataType::SHORT,
				MetadataTypes\DataTypeShort::USHORT => MetadataTypes\DataType::USHORT,
				MetadataTypes\DataTypeShort::INT => MetadataTypes\DataType::INT,
				MetadataTypes\DataTypeShort::UINT => MetadataTypes\DataType::UINT,
				MetadataTypes\DataTypeShort::FLOAT => MetadataTypes\DataType::FLOAT,
				default => MetadataTypes\DataType::UNKNOWN,
			};
		}

		if (
			$step !== null
			// If step is defined and is float number, data type have to be float
			&& floatval(intval($step)) !== $step
		) {
			return MetadataTypes\DataType::FLOAT;
		}

		if (
			(
				$format->getMin() !== null
				// If minimum value is defined and is float number, data type have to be float
				&& floatval(intval($format->getMin())) !== $format->getMin()
			) || (
				$format->getMax() !== null
				// If maximum value is defined and is float number, data type have to be float
				&& floatval(intval($format->getMax())) !== $format->getMax()
			)
		) {
			return MetadataTypes\DataType::FLOAT;
		}

		if ($format->getMin() !== null || $format->getMax() !== null) {
			$dataTypeRanges = [
				MetadataTypes\DataType::CHAR->value => [-128, 127],
				MetadataTypes\DataType::UCHAR->value => [0, 255],
				MetadataTypes\DataType::SHORT->value => [-32_768, 32_767],
				MetadataTypes\DataType::USHORT->value => [0, 65_535],
				MetadataTypes\DataType::INT->value => [-2_147_483_648, 2_147_483_647],
				MetadataTypes\DataType::UINT->value => [0, 4_294_967_295],
			];

			foreach ($dataTypeRanges as $dataType => $ranges) {
				if (
					(
						$format->getMin() === null
						|| (
							$format->getMin() >= $ranges[0]
							&& $format->getMin() <= $ranges[1]
						)
					) && (
						$format->getMax() === null
						|| (
							$format->getMax() >= $ranges[0]
							&& $format->getMax() <= $ranges[1]
						)
					)
				) {
					try {
						return MetadataTypes\DataType::from($dataType);
					} catch (Throwable) {
						throw new Exceptions\InvalidState(sprintf('Data type: %s could not be initialized', $dataType));
					}
				}
			}

			return MetadataTypes\DataType::FLOAT;
		}

		return $fallback ?? MetadataTypes\DataType::UNKNOWN;
	}

}
