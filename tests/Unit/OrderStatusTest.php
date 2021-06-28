<?php

namespace Tests\Unit;

use App\Http\Controllers\Order\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_get_status_by_code()
    {
        $this->assertEquals(OrderStatus::getStatus(0),'PENDING');
        $this->assertEquals(OrderStatus::getStatus(1),'DELIVERING');
        $this->assertEquals(OrderStatus::getStatus(2),'COMPLETED');
    }

    public function test_get_code_by_status()
    {
        $this->assertEquals(OrderStatus::getStatusCode('pending'),0);
        $this->assertEquals(OrderStatus::getStatusCode('delivering'),1);
        $this->assertEquals(OrderStatus::getStatusCode('completed'),2);
    }
}
