<?php

namespace Krak\SymfonyMessengerAutoScale\Tests\Unit\Internal;

use Krak\SymfonyMessengerAutoScale\Internal\Glob;
use PHPUnit\Framework\TestCase;

final class GlobTest extends TestCase
{
    /** @dataProvider provide_strings_for_matching */
    public function test_can_match_strings(string $pattern, string $toMatch, bool $expectedMatch) {
        $glob = new Glob($pattern);
        $this->assertEquals($expectedMatch, $glob->matches($toMatch));
    }

    public function provide_strings_for_matching() {
        yield 'abc => abc' => ['abc', 'abc', true];
        yield 'abc !=> def' => ['abc', 'def', false];
        yield 'abc !=> a' => ['abc', 'a', false];
        yield 'abc !=> ab' => ['abc', 'ab', false];
        yield 'abc !=> abcd' => ['abc', 'abcd', false];
        yield 'abc* => abcd' => ['abc*', 'abcd', true];
        yield '*abc* => xabc' => ['*abc*', 'xabc', true];
        yield '*abc* => xabcx' => ['*abc*', 'xabcx', true];
    }
}
