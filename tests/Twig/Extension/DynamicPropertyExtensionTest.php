<?php

declare(strict_types=1);

namespace App\Tests\Twig\Extension;

use App\Form\Type\DynamicPropertiesType;
use App\Twig\Extension\DynamicPropertyExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DynamicPropertyExtensionTest extends TestCase
{
    /**
     * @dataProvider dynamicPropertyValueProvider
     *
     * @param bool|string|int $value
     */
    public function testDynamicPropertyValue($value, array $propertyDefinition, string $expected): void
    {
        $dynamicPropertyExtension = new DynamicPropertyExtension(
            $translator = $this->createMock(TranslatorInterface::class)
        );
        $translator->expects($this->any())->method('trans')->willReturnArgument(0);

        $this->assertSame($expected, $dynamicPropertyExtension->dynamicPropertyValue($value, $propertyDefinition));
    }

    public function dynamicPropertyValueProvider(): array
    {
        return [
            'boolean_true' => [
                'value' => true,
                'propertyDefinition' => ['type' => 'boolean'],
                'expected' => 'common.yes',
            ],
            'boolean_false' => [
                'value' => false,
                'propertyDefinition' => ['type' => 'boolean'],
                'expected' => 'common.no',
            ],
            'boolean_wrong_type' => [
                'value' => 'foo',
                'propertyDefinition' => ['type' => 'boolean'],
                'expected' => 'common.yes',
            ],
            'choice' => [
                'value' => 'foo',
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_CHOICE, 'choices' => ['bar' => 'foo']],
                'expected' => 'bar',
            ],
            'choice_not_in_choices' => [
                'value' => 'bar',
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_CHOICE, 'choices' => ['foo' => 'foo']],
                'expected' => 'bar',
            ],
            'choices_with_other' => [
                'value' => 'bar',
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_CHOICE_WITH_OTHER, 'choices' => ['foo' => 'foo']],
                'expected' => 'bar',
            ],
            'number' => [
                'value' => 5,
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_NUMBER],
                'expected' => '5',
            ],
            'number_wrong_type' => [
                'value' => 'foo',
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_NUMBER],
                'expected' => 'foo',
            ],
            'text' => [
                'value' => 'foo',
                'propertyDefinition' => ['type' => DynamicPropertiesType::TYPE_SMALL_TEXT],
                'expected' => 'foo',
            ],
        ];
    }
}
