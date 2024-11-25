<?php declare(strict_types = 1);

/**
 * CombinedEnumFormat.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Tools!
 * @subpackage     Formats
 * @since          1.0.0
 *
 * @date           05.08.22
 */

namespace FastyBird\Core\Tools\Formats;

use ArrayIterator;
use FastyBird\Core\Tools\Exceptions;
use FastyBird\Core\Tools\Utilities;
use IteratorAggregate;
use Nette;
use Traversable;
use TypeError;
use ValueError;
use function array_map;
use function explode;
use function implode;
use function is_string;
use function strval;
use function trim;

/**
 * Combined enum value format
 *
 * @implements     IteratorAggregate<int, array<int, CombinedEnumItem|null>>
 *
 * @package        FastyBird:Tools!
 * @subpackage     Formats
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class CombinedEnum implements IteratorAggregate
{

	use Nette\SmartObject;

	/** @var array<int, array<int, CombinedEnumItem|null>> */
	private array $items;

	/**
	 * @param string|array<int, array<int, string|array<int, string|int|float|bool|null>|null>> $items
	 *
	 * @throws Exceptions\InvalidArgument
	 * @throws ValueError
	 * @throws TypeError
	 */
	public function __construct(string|array $items)
	{
		if (is_string($items)) {
			$this->items = array_map(static function (string $item): array {
				if (trim($item) === '') {
					throw new Exceptions\InvalidArgument('Provided format is not valid for combined enum format');
				}

				$parts = explode(':', $item) + [null, null, null];

				return array_map(static function (string|null $part): CombinedEnumItem|null {
					if ($part === null || trim($part) === '') {
						return null;
					}

					return new CombinedEnumItem($part);
				}, $parts);
			}, explode(',', $items));
		} else {
			$this->items = array_map(static function (array $item): array {
				if ($item === []) {
					throw new Exceptions\InvalidArgument('Provided format is not valid for combined enum format');
				}

				return array_map(static function (string|array|null $part): CombinedEnumItem|null {
					if ($part === null || $part === []) {
						return null;
					}

					return new CombinedEnumItem($part);
				}, $item);
			}, $items);
		}
	}

	/**
	 * @return array<int, array<int, CombinedEnumItem|null>>
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @return array<int, array<int, string|array<int, string|int|float|bool>|null>>
	 *
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws ValueError
	 * @throws TypeError
	 */
	public function toArray(): array
	{
		return array_map(
			static fn (array $item): array => array_map(
				static function (CombinedEnumItem|null $part): array|string|null {
					if ($part instanceof CombinedEnumItem) {
						return $part->getDataType() !== null
							? $part->toArray()
							: Utilities\Value::toString($part->getValue());
					}

					return $part;
				},
				$item,
			),
			$this->getItems(),
		);
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->getItems());
	}

	/**
	 * @return array<int, array<int, string|array<int, string|int|float|bool>|null>>
	 *
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws ValueError
	 * @throws TypeError
	 */
	public function getValue(): array
	{
		return $this->toArray();
	}

	public function __toString(): string
	{
		return implode(',', array_map(static fn (array $item) =>
			implode(':', array_map(static function (CombinedEnumItem|null $part): string {
				if ($part instanceof CombinedEnumItem) {
					return strval($part);
				}

				return '';
			}, $item)), $this->getItems()));
	}

}
