<?php declare(strict_types = 1);

namespace FastyBird\Core\Tools\Tests\Cases\Unit\Formats;

use FastyBird\Core\Tools\Exceptions;
use FastyBird\Core\Tools\Formats;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use PHPUnit\Framework\TestCase;
use TypeError;
use ValueError;
use function strval;

final class CombinedEnumFormatTest extends TestCase
{

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function testFromString(): void
	{
		$valueObject = new Formats\CombinedEnum('one,sw|switch_on:1000:s|on,sw|switch_off:2000:s|off');

		$items = $valueObject->getItems();

		self::assertCount(3, $valueObject->toArray());
		self::assertEquals([
			['one', null, null],
			[['sw', 'switch_on'], '1000', ['s', 'on']],
			[['sw', 'switch_off'], '2000', ['s', 'off']],
		], $valueObject->toArray());
		self::assertCount(3, $items);
		self::assertTrue($items[1][0] instanceof Formats\CombinedEnumItem);
		self::assertTrue($items[1][0]->getDataType() instanceof MetadataTypes\DataTypeShort);
		self::assertSame(MetadataTypes\DataTypeShort::SWITCH, $items[1][0]->getDataType());
		self::assertTrue($items[1][0]->getValue() instanceof MetadataTypes\Payloads\Switcher);
		self::assertSame(MetadataTypes\Payloads\Switcher::ON, $items[1][0]->getValue());
		self::assertEquals('one::,sw|switch_on:1000:s|on,sw|switch_off:2000:s|off', strval($valueObject));

		$valueObject = new Formats\CombinedEnum('sw|switch_on:1000:s|on,sw|switch_off:2000:s|off');

		self::assertCount(2, $valueObject->toArray());
		self::assertEquals([
			[['sw', 'switch_on'], '1000', ['s', 'on']],
			[['sw', 'switch_off'], '2000', ['s', 'off']],
		], $valueObject->toArray());
		self::assertEquals('sw|switch_on:1000:s|on,sw|switch_off:2000:s|off', strval($valueObject));
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function testFromArray(): void
	{
		$valueObject = new Formats\CombinedEnum([
			['one', null, null],
			[['sw', 'switch_on'], '1000', ['s', 'on']],
			[['sw', 'switch_off'], '2000', ['s', 'off']],
		]);

		self::assertCount(3, $valueObject->toArray());
		self::assertEquals([
			['one', null, null],
			[['sw', 'switch_on'], '1000', ['s', 'on']],
			[['sw', 'switch_off'], '2000', ['s', 'off']],
		], $valueObject->toArray());
		self::assertEquals('one::,sw|switch_on:1000:s|on,sw|switch_off:2000:s|off', strval($valueObject));
	}

}
