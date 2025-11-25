<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Suppress deprecated notices for PHP 8.4+ since I still want it to work at 7.4
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }
}
