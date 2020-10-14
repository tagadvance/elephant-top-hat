# Elephant Top Hat
A tiny stand-alone library for parsing the Linux `top` command.

## Installation
```
composer require tagadvance/elephant-top-hat:dev-main
```

## Example
```php
use tagadvance\elephanttophat\Top;

$top = Top::exec();
var_export($top);
```
yields e.g.
```php
[
	'time' => Measurement::__set_state([
		'value' => DateTimeImmutable::__set_state([
			'date' => '2020-10-14 17:26:45.000000',
			'timezone_type' => 3,
			'timezone' => 'UTC',
		]),
		'unit' => NULL,
	]),
	'uptime' => Measurement::__set_state([
		'value' => '1:23',
		'unit' => NULL,
	]),
	'users' => Measurement::__set_state([
		'value' => 1,
		'unit' => NULL,
	]),
	'load_average_1_minute' => Measurement::__set_state([
		'value' => '0.96',
		'unit' => NULL,
	]),
	'load_average_5_minutes' => Measurement::__set_state([
		'value' => '1.61',
		'unit' => NULL,
	]),
	'load_average_15_minutes' => Measurement::__set_state([
		'value' => '1.83',
		'unit' => NULL,
	]),
	'tasks_total' => Measurement::__set_state([
		'value' => 368,
		'unit' => NULL,
	]),
	'tasks_running' => Measurement::__set_state([
		'value' => 1,
		'unit' => NULL,
	]),
	'tasks_sleeping' => Measurement::__set_state([
		'value' => 367,
		'unit' => NULL,
	]),
	'tasks_stopped' => Measurement::__set_state([
		'value' => 0,
		'unit' => NULL,
	]),
	'tasks_zombie' => Measurement::__set_state([
		'value' => 0,
		'unit' => NULL,
	]),
	'cpu_user_space' => Measurement::__set_state([
		'value' => '5.6',
		'unit' => '%',
	]),
	'cpu_kernel_space' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'cpu_nice' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'cpu_%' => Measurement::__set_state([
		'value' => '5.6',
		'unit' => '%',
	]),
	'cpu_idle' => Measurement::__set_state([
		'value' => '94.4',
		'unit' => '%',
	]),
	'cpu_wait' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'cpu_hardware_interrupts' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'cpu_software_interrupts' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'cpu_steal' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => '%',
	]),
	'memory_total' => Measurement::__set_state([
		'value' => '15433.6',
		'unit' => 'MiB',
	]),
	'memory_free' => Measurement::__set_state([
		'value' => '7824.9',
		'unit' => 'MiB',
	]),
	'memory_used' => Measurement::__set_state([
		'value' => '4914.7',
		'unit' => 'MiB',
	]),
	'memory_cache' => Measurement::__set_state([
		'value' => '2694.0',
		'unit' => 'MiB',
	]),
	'swap_total' => Measurement::__set_state([
		'value' => '16384.0',
		'unit' => 'MiB',
	]),
	'swap_free' => Measurement::__set_state([
		'value' => '16384.0',
		'unit' => 'MiB',
	]),
	'swap_used' => Measurement::__set_state([
		'value' => '0.0',
		'unit' => 'MiB',
	]),
	'memory_available' => Measurement::__set_state([
		'value' => '9903.5',
		'unit' => 'MiB',
	]),
]
```
