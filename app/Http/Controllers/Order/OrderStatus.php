<?php


namespace App\Http\Controllers\Order;


class OrderStatus
{
    public const STATUS_PENDING = 0;
    public const STATUS_DELIVERING = 1;
    public const STATUS_COMPLETED = 2;

    private static $statuses = [
        0 => "PENDING",
        1 => "DELIVERING",
        2 => "COMPLETED"
    ];

    private static $statusCodes = [
        "PENDING" => 0,
        "DELIVERING" => 1,
        "COMPLETED" => 2
    ];

    public static function getStatus(int $code): string
    {
        return static::$statuses[$code];
    }

    public static function getStatusCode(string $status): int
    {
        return static::$statusCodes[strtoupper($status)];
    }
}
