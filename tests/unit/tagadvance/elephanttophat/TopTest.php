<?php

declare(strict_types=1);

namespace tagadvance\elephanttophat;

use PHPUnit\Framework\TestCase;

final class TopTest extends TestCase
{
    public function testExec(): void
    {
        $this->assertIsArray(Top::exec());
    }

    public function testParse(): void
    {
        $testFiles = [
            'ubuntu-20-top-single-user.txt',
            'ubuntu-20-top-multiple-users.txt',
            'debian-13-top-padded.txt',
        ];

        foreach ($testFiles as $filename) {
            $result = Top::parse(self::readResource($filename));

            $this->assertIsArray($result);
            foreach ($result as $measurement) {
                $this->assertInstanceOf(Measurement::class, $measurement);
            }
        }
    }

    /**
     * procps-ng pads the summary lines to the terminal width. The trailing whitespace must not
     * defeat the end-of-line anchors in the parser's patterns.
     */
    public function testParseToleratesTrailingWhitespace(): void
    {
        $result = Top::parse(self::readResource('debian-13-top-padded.txt'));

        $this->assertSame('0.0', $result['cpu_steal']->getValue());
        $this->assertSame('10164.7', $result['memory_cache']->getValue());
        $this->assertSame('MiB', $result['memory_cache']->getUnit());
        $this->assertSame('10098.1', $result['memory_available']->getValue());
    }

    private static function readResource(string $filename): string
    {
        $tests = dirname(__DIR__, 3);
        $pathToOutput = implode(DIRECTORY_SEPARATOR, [$tests, 'resources', $filename]);

        return file_get_contents($pathToOutput);
    }
}
