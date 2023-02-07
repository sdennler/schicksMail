<?php

declare(strict_types=1);

namespace SDennler\schicksMail\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use SDennler\schicksMail\Exception\SchicksMailException;
use SDennler\schicksMail\Validator\ConfigValidator;

class ConfigValidatorTest extends TestCase
{
    public function testDefaultValuesAreAddedToValidConfig(): void
    {
        $config['emailTo']   = 'example@example.com';
        $config['emailFrom'] = 'example2@example.com';

        $validator = new ConfigValidator();
        $this->assertSame(
            [
                'debug' => false,
                'name' => '',
                'emailTo' => 'example@example.com',
                'emailFrom' => 'example2@example.com',
            ],
            $validator->validate($config),
        );
    }

    public function testMissingEmailToThrowsException(): void
    {
        $this->expectException(SchicksMailException::class);

        $validator = new ConfigValidator();
        $validator->validate([]);
    }
}
