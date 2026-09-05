<?php

declare(strict_types=1);

namespace tagadvance\elephanttophat;

/**
 * Parses the summary block `top` prints above its process table into named measurements.
 * Only procps-ng's English output is understood, so `top` must run under a C locale.
 */
class Top
{
    private const COMMAND = 'top -b -n 2 -d 0.01 | grep ^top -A 5 | tail -n 6';

    private function __construct() {}

    /**
     * Samples `top` twice a hundredth of a second apart and parses the second sample, because
     * `top`'s first iteration reports CPU shares averaged since boot rather than current ones.
     *
     * @return Measurement[] Keyed by measurement name; see {@see parse()}.
     * @throws \TypeError when `top` writes nothing at all — procps-ng missing, or no `/proc` to
     *     read — because `shell_exec()` then returns null, which {@see parse()} will not accept.
     * @throws \RuntimeException when the output does not match; the command does not pin
     *     `LC_ALL=C`, so a translated locale reaches here too. See {@see parse()}.
     */
    public static function exec(): array
    {
        $output = shell_exec(self::COMMAND);

        return self::parse($output);
    }

    /**
     * Yields `time`, `uptime`, `users`, `load_average_*`, `tasks_*`, `cpu_*`, `memory_*`,
     * `swap_*` and `memory_available`.
     *
     * @param string $output `top`'s five summary lines, newline separated. Anything past the
     *     fifth line is ignored; fewer than five emits `Undefined array key` warnings first.
     * @return Measurement[] Keyed by measurement name. `time` carries today's date in PHP's
     *     default timezone, since `top` prints only a clock time; `cpu_*` are percentages and
     *     `memory_*`/`swap_*` use whatever scale `top` printed (see `top -E`); `cpu_utilization`
     *     is derived as `100 - cpu_idle` rather than reported by `top`.
     * @throws \RuntimeException when any summary line fails to match: truncated or corrupt
     *     input, a non-English locale (`top` translates the labels and prints comma decimals), a
     *     pre-2.6.11 `%Cpu(s)` line lacking the `st` field, or a host large enough that procps
     *     substitutes its `+` overflow marker for a value that will not fit `%9.9s`
     *     (`MiB Mem : 2097152.+total`). That last one is deliberate — the alternative is
     *     silently returning a truncated number.
     */
    public static function parse(string $output): array
    {
        // procps-ng pads summary lines to the terminal width, so trailing whitespace is expected.
        $lines = array_map('rtrim', explode(PHP_EOL, $output));
        [$top, $tasks, $cpu, $memory, $swap] = $lines;

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
				MESSAGE,
            );
        };

        $pattern = '/^top - (\d{2}:\d{2}:\d{2}) up\s*(.*),\s*(\d+) users?,\s*load average: (\d+\.\d{2}), (\d+\.\d{2}), (\d+\.\d{2})$/';
        [$time, $uptime, $users, $loadAverage1Minute, $loadAverage5Minutes, $loadAverage15Minutes] = $match($pattern, $top);

        $pattern = '/^Tasks:\s*(\d+) total,\s*(\d+) running,\s*(\d+) sleeping,\s*(\d+) stopped,\s*(\d+) zombie$/';
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
            'cpu_utilization' => $toPercent(bcsub('100', $cpuIdle, 1)),
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
            // `avail Mem` is printed on the swap line, and procps takes the scale label for both
            // summary lines from the same argument, so $toSwap and $toMemory cannot disagree here.
            'memory_available' => $toSwap($memoryAvailable),
        ];

        return array_map(
            function ($measurement): Measurement {
                return $measurement instanceof Measurement
                    ? $measurement
                    : new Measurement($measurement);
            },
            $measurements,
        );
    }
}
