<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests;

use Displace\AI\Contracts\Tests\Fake\EchoGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorContractTest extends TestCase
{
    public function testGeneratesText(): void
    {
        self::assertSame('hello world', (new EchoGenerator())->generate('hello world'));
    }

    public function testMaxTokensBoundsTheOutput(): void
    {
        $output = (new EchoGenerator())->generate('one two three four', ['max_tokens' => 2]);

        self::assertSame('one two', $output);
    }

    public function testUnknownOptionKeysAreSilentlyIgnored(): void
    {
        $output = (new EchoGenerator())->generate('hello', ['definitely_not_a_real_option' => true]);

        self::assertSame('hello', $output);
    }
}
