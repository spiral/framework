<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\FileSystemInfo\OptionsBasedInterface;
use Spiral\Storage\Config\DTO\Traits\OptionsTrait;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class OptionsTraitTest extends UnitTestCase
{
    private const BASIC_OPTIONS = [
        'option1' => 'optionVal1',
        'option2' => 'optionVal2',
        'option3' => 'optionVal3',
    ];

    /**
     * @throws \ReflectionException
     */
    public function testHasOption(): void
    {
        $trait = $this->buildBasicOptions();

        foreach (self::BASIC_OPTIONS as $option => $optionVal) {
            $this->assertTrue($trait->hasOption($option));
        }

        $this->assertFalse($trait->hasOption('optionMissed'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetOption(): void
    {
        $trait = $this->buildBasicOptions();

        foreach (self::BASIC_OPTIONS as $option => $optionVal) {
            $this->assertEquals($optionVal, $trait->getOption($option));
        }

        $this->assertNull($trait->getOption('optionMissed'));
    }

    /**
     * @dataProvider getOptionsListForHasType
     *
     * @param string $optionLabel
     * @param $optionVal
     * @param string $type
     * @param bool $expectedResult
     *
     * @throws \ReflectionException
     */
    public function testIsOptionHasRequiredType(
        string $optionLabel,
        $optionVal,
        string $type,
        bool $expectedResult
    ): void {
        $trait = $this->buildBasicOptions();

        $this->assertEquals(
            $expectedResult,
            $this->callNotPublicMethod($trait, 'isOptionHasRequiredType', [$optionLabel, $optionVal, $type])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testIsOptionHasRequiredTypeUnknownTypeFailed(): void
    {
        $trait = $this->buildBasicOptions();

        $optionLabel = 'someLabel';
        $optionType = 'missedType';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            \sprintf('Unknown option type detected for option `%s`: %s', $optionLabel, $optionType)
        );

        $this->callNotPublicMethod($trait, 'isOptionHasRequiredType', [$optionLabel, 'someVal', $optionType]);
    }

    /**
     * @dataProvider getOptionsListForProcessing
     *
     * @param $optionVal
     * @param string $type
     * @param $expectedVal
     *
     * @throws \ReflectionException
     */
    public function testProcessOptionByType($optionVal, string $type, $expectedVal): void
    {
        $trait = $this->buildBasicOptions();

        $this->assertEquals(
            $expectedVal,
            $this->callNotPublicMethod($trait, 'processOptionByType', [$optionVal, $type])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testValidateRequiredOptionsPassed(): void
    {
        $requiredOptions = array_keys(self::BASIC_OPTIONS);

        $trait = $this->buildBasicOptions();

        $this->callNotPublicMethod(
            $trait,
            'validateRequiredOptions',
            [$requiredOptions, static::BASIC_OPTIONS]
        );

        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws \ReflectionException
     */
    public function testValidateRequiredOptionsFailed(): void
    {
        $reqOption = 'reqOption';
        $msgPostfix = ' some postfix';

        $requiredOptions = array_keys(self::BASIC_OPTIONS);
        $requiredOptions[] = $reqOption;

        $trait = $this->buildBasicOptions();

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(\sprintf('Option `%s` not detected%s', $reqOption, $msgPostfix));

        $this->callNotPublicMethod(
            $trait,
            'validateRequiredOptions',
            [$requiredOptions, static::BASIC_OPTIONS, $msgPostfix]
        );
    }

    public function getOptionsListForHasType(): array
    {
        $result = [
            ['wrongIntOption', 'someStr', OptionsBasedInterface::INT_TYPE, false],
            ['floatOption', 1.0, OptionsBasedInterface::FLOAT_TYPE, true],
            ['strOption', '1', OptionsBasedInterface::STRING_TYPE, true],
            ['wrongStrOption', 1, OptionsBasedInterface::STRING_TYPE, false],
            ['wrongBoolOption', '4', OptionsBasedInterface::BOOL_TYPE, false],
            ['wrongBoolOption2', 5, OptionsBasedInterface::BOOL_TYPE, false],
            ['arrayOption', [1, 2], OptionsBasedInterface::ARRAY_TYPE, true],
            ['arrayOption', [], OptionsBasedInterface::ARRAY_TYPE, true],
            ['arrayOption', 4, OptionsBasedInterface::ARRAY_TYPE, false],
            ['arrayOption', true, OptionsBasedInterface::ARRAY_TYPE, false],
        ];

        foreach ([0, 1, 3, '4', '15'] as $key => $intVal) {
            $result[] = ['intOption' . $key, $intVal, OptionsBasedInterface::INT_TYPE, true];
        }

        foreach ([0, 1, '0', '1', true, false] as $key => $boolVal) {
            $result[] = ['boolOption' . $key, $boolVal, OptionsBasedInterface::BOOL_TYPE, true];
        }

        $mixedVals = [
            1,
            5.0,
            '0',
            'some string',
            false,
            true,
            [],
            new \DateTimeImmutable(),
            [3, 4],
        ];

        foreach ($mixedVals as $key => $mixedVal) {
            $result[] = ['mixedOption' . $key, $mixedVal, OptionsBasedInterface::MIXED_TYPE, true];
        }

        return $result;
    }

    public function getOptionsListForProcessing(): array
    {
        $date = new \DateTimeImmutable();
        return [
            ['1', OptionsBasedInterface::INT_TYPE, 1],
            ['1.35', OptionsBasedInterface::INT_TYPE, 1],
            [7, OptionsBasedInterface::INT_TYPE, 7],
            [5.00, OptionsBasedInterface::INT_TYPE, 5],
            ['1.00', OptionsBasedInterface::FLOAT_TYPE, 1.00],
            [7, OptionsBasedInterface::FLOAT_TYPE, 7.00],
            [5.00, OptionsBasedInterface::FLOAT_TYPE, 5.00],
            [5.0, OptionsBasedInterface::STRING_TYPE, '5'],
            [0, OptionsBasedInterface::STRING_TYPE, '0'],
            ['some string', OptionsBasedInterface::STRING_TYPE, 'some string'],
            [true, OptionsBasedInterface::STRING_TYPE, '1'],
            [true, OptionsBasedInterface::BOOL_TYPE, true],
            ['true', OptionsBasedInterface::BOOL_TYPE, true],
            [1, OptionsBasedInterface::BOOL_TYPE, true],
            [18.0, OptionsBasedInterface::BOOL_TYPE, true],
            ['someVal', OptionsBasedInterface::BOOL_TYPE, true],
            ['', OptionsBasedInterface::BOOL_TYPE, false],
            [0, OptionsBasedInterface::BOOL_TYPE, false],
            [null, OptionsBasedInterface::BOOL_TYPE, false],
            [[1, 2], OptionsBasedInterface::ARRAY_TYPE, [1, 2]],
            [null, OptionsBasedInterface::MIXED_TYPE, null],
            [[1], OptionsBasedInterface::MIXED_TYPE, [1]],
            [$date, OptionsBasedInterface::MIXED_TYPE, $date],
        ];
    }

    /**
     * @param array|string[] $options
     *
     * @return MockObject|OptionsTrait
     *
     * @throws \ReflectionException
     */
    private function buildBasicOptions(array $options = self::BASIC_OPTIONS)
    {
        $trait = $this->getMockForTrait(OptionsTrait::class);

        $this->setNotPublicProperty($trait, 'options', $options);

        return $trait;
    }
}
