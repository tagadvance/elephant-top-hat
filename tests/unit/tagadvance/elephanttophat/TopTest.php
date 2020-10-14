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
			'ubuntu-20-top-multiple-users.txt'
		];
		$tests = dirname(__DIR__, 3);
		$testOutput = array_map(
			function (string $filename) use ($tests) {
				$pathToOutput = implode(DIRECTORY_SEPARATOR, [$tests, 'resources', $filename]);

				return file_get_contents($pathToOutput);
			},
			$testFiles
		);

		foreach ($testOutput as $output) {
			$result = Top::parse($output);

			$this->assertIsArray($result);
			foreach ($result as $measurement) {
				$this->assertInstanceOf(Measurement::class, $measurement);
			}
		}
	}
}
