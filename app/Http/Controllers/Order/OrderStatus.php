<?php


namespace App\Http\Controllers\Order;


class OrderStatus
{
    public const STATUS_PENDING = 0;
    public const STATUS_DELIVERING = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_CANCELLED = -1;

    private static $statuses = [
        self::STATUS_PENDING => "PENDING",
        self::STATUS_DELIVERING => "DELIVERING",
        self::STATUS_COMPLETED => "COMPLETED",
        self::STATUS_CANCELLED => "CANCELLED"
    ];

    private static $statusCodes = [
        "PENDING" => self::STATUS_PENDING,
        "DELIVERING" => self::STATUS_DELIVERING,
        "COMPLETED" => self::STATUS_COMPLETED,
        "CANCELLED" => self::STATUS_CANCELLED
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
