<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $configCache = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($configCache)) {
            unlink($configCache);
        }

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException('Tests must use sqlite. Refusing to run against a live database.');
        }
    }
}
