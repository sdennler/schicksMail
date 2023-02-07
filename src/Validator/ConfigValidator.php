<?php

declare(strict_types=1);

namespace schicksMail\schicksMail\Validator;

use schicksMail\schicksMail\Exception\SchicksMailException;

class ConfigValidator
{
    private array $defaultValues = [
        'debug' => false,
        'name' => '',
    ];

    private array $requiredKeys = [
        'emailTo',
        'emailFrom',
    ];

    /**
     * @throws SchicksMailException
     */
    public function validate(array $config): array
    {
        $config = array_merge($this->defaultValues, $config);

        foreach ($this->requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new SchicksMailException(sprintf('Config key %s not set.', $key));
            }
        }

        return $config;
    }
}
