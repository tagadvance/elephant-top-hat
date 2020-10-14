<?php

declare(strict_types=1);

namespace tagadvance\elephanttophat;

class Top
{
	private const COMMAND = 'top -b -n 2 -d 0.01 | grep ^top -A 5 | tail -n 6';

	private function __construct()
	{
	}

	public static function exec(): array
	{
		$output = shell_exec(self::COMMAND);

		return self::parse($output);
	}

	/**
	 * @param string $output The first 5 lines of output from the `top` command.
	 * @return Measurement[] An array of measurements indexed by measurement name.
	 */
	public static function parse(string $output): array
	{
		[$top, $tasks, $cpu, $memory, $swap] = explode(PHP_EOL, $output);

		$match = function (string $pattern, string $subject): array {
			$matches = [];

			if (preg_match($pattern, $subject, $matches)) {
				array_shift($matches);

				return $matches;
			}

			throw new \RuntimeException(
				<<<MESSAGE
				No matches found!
				\$pattern = '$pattern';
				\$subject = '$subject';
				MESSAGE
			);
		};

		$pattern = '/^top - (\d{2}:\d{2}:\d{2}) up\s*(.*),\s*(\d+) users?,\s*load average: (\d+\.\d{2}), (\d+\.\d{2}), (\d+\.\d{2})$/';
		[$time, $uptime, $users, $loadAverage1Minute, $loadAverage5Minutes, $loadAverage15Minutes] = $match($pattern, $top);

		$pattern = '/^Tasks: (\d+) total,\s*(\d+) running, (\d+) sleeping,\s*(\d+) stopped,\s*(\d+) zombie$/';
		[$tasksTotal, $tasksRunning, $tasksSleeping, $tasksStopped, $tasksZombie] = $match($pattern, $tasks);

		$pattern = '/^%Cpu\(s\):\s*(\d+\.\d+) us,\s*(\d+\.\d+) sy,\s*(\d+\.\d+) ni,\s*(\d+\.\d+) id,\s*(\d+\.\d+) wa,\s*(\d+\.\d+) hi,\s*(\d+\.\d+) si,\s*(\d+\.\d+) st$/';
		[$cpuUserSpace, $cpuKernelSpace, $cpuNice, $cpuIdle, $cpuWait, $cpuHardwareInterrupts, $cpuSoftwareInterrupts, $cpuSteal] = $match($pattern, $cpu);

		$pattern = '/^([KMGTPE]iB) Mem\s*:\s*([0-9.]+) total,\s*([0-9.]+) free,\s*([0-9.]+) used,\s*([0-9.]+) buff\/cache$/';
		[$memoryUnit, $memoryTotal, $memoryFree, $memoryUsed, $memoryCache] = $match($pattern, $memory);

		$pattern = '/^([KMGTPE]iB) Swap:\s*([0-9.]+) total,\s*([0-9.]+) free,\s*([0-9.]+) used\.\s*([0-9.]+) avail Mem\s*$/';
		[$swapUnit, $swapTotal, $swapFree, $swapUsed, $memoryAvailable] = $match($pattern, $swap);

		$toPercent = function (string $value): Measurement {
			return new Measurement($value, Measurement::UNIT_PERCENT);
		};

		$toMemory = function (string $value) use ($memoryUnit): Measurement {
			return new Measurement($value, $memoryUnit);
		};

		$toSwap = function (string $value) use ($swapUnit): Measurement {
			return new Measurement($value, $swapUnit);
		};

		$measurements = [
			'time' => \DateTimeImmutable::createFromFormat('H:i:s', $time),
			'uptime' => $uptime,
			'users' => intval($users),
			'load_average_1_minute' => $loadAverage1Minute,
			'load_average_5_minutes' => $loadAverage5Minutes,
			'load_average_15_minutes' => $loadAverage15Minutes,
			'tasks_total' => intval($tasksTotal),
			'tasks_running' => intval($tasksRunning),
			'tasks_sleeping' => intval($tasksSleeping),
			'tasks_stopped' => intval($tasksStopped),
			'tasks_zombie' => intval($tasksZombie),
			'cpu_user_space' => $toPercent($cpuUserSpace),
			'cpu_kernel_space' => $toPercent($cpuKernelSpace),
			'cpu_nice' => $toPercent($cpuNice),
			'cpu_%' => $toPercent(bcsub('100', $cpuIdle, 1)),
			'cpu_idle' => $toPercent($cpuIdle),
			'cpu_wait' => $toPercent($cpuWait),
			'cpu_hardware_interrupts' => $toPercent($cpuHardwareInterrupts),
			'cpu_software_interrupts' => $toPercent($cpuSoftwareInterrupts),
			'cpu_steal' => $toPercent($cpuSteal),
			'memory_total' => $toMemory($memoryTotal),
			'memory_free' => $toMemory($memoryFree),
			'memory_used' => $toMemory($memoryUsed),
			'memory_cache' => $toMemory($memoryCache),
			'swap_total' => $toSwap($swapTotal),
			'swap_free' => $toSwap($swapFree),
			'swap_used' => $toSwap($swapUsed),
			'memory_available' => $toSwap($memoryAvailable)
		];

		return array_map(
			function ($measurement): Measurement {
				return $measurement instanceof Measurement
					? $measurement
					: new Measurement($measurement);
			},
			$measurements
		);
	}
}
