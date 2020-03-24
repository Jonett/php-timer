<?php declare(strict_types=1);
/*
 * This file is part of phpunit/php-timer.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Timer;

final class Timer
{
    private const SIZES = [
        'GB' => 1073741824,
        'MB' => 1048576,
        'KB' => 1024,
    ];

    private const TIMES = [
        'hour'   => 3600000,
        'minute' => 60000,
        'second' => 1000,
    ];

    /**
     * @var float[]
     */
    private static $startTimes = [];

    public static function start(): void
    {
        self::$startTimes[] = \microtime(true);
    }

    public static function stop(): float
    {
        return \microtime(true) - \array_pop(self::$startTimes);
    }

    public static function bytesToString(float $bytes): string
    {
        foreach (self::SIZES as $unit => $value) {
            if ($bytes >= $value) {
                return \sprintf('%.2f %s', $bytes >= 1024 ? $bytes / $value : $bytes, $unit);
            }
        }

        return $bytes . ' byte' . ((int) $bytes !== 1 ? 's' : '');
    }

    public static function secondsToTimeString(float $timeInSeconds): string
    {
        $timeInMilliseconds = \round($timeInSeconds * 1000);

        foreach (self::TIMES as $unit => $value) {
            if ($timeInMilliseconds >= $value) {
                $timeInSeconds = \floor($timeInMilliseconds / $value * 100.0) / 100.0;

                /** @noinspection TypeUnsafeComparisonInspection */
                return $timeInSeconds . ' ' . ($timeInSeconds == 1 ? $unit : $unit . 's');
            }
        }

        return $timeInMilliseconds . ' ms';
    }

    /**
     * @throws RuntimeException
     */
    public static function timeSinceStartOfRequest(): string
    {
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME_FLOAT'];
        } elseif (isset($_SERVER['REQUEST_TIME'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME'];
        } else {
            throw new RuntimeException('Cannot determine time at which the request started');
        }

        return self::secondsToTimeString(\microtime(true) - $startOfRequest);
    }

    /**
     * @throws RuntimeException
     */
    public static function resourceUsage(): string
    {
        return \sprintf(
            'Time: %s, Memory: %s',
            self::timeSinceStartOfRequest(),
            self::bytesToString(\memory_get_peak_usage(true))
        );
    }
}
