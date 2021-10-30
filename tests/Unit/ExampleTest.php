<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest3()
    {
        $user = [
            "email" => "0902888145"
        ];
        // $test = resolve(\App\Jobs\SynchronizeUsersWithCrmJob::class,['user'=>$user]);
        // $test->dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));

        $this->assertTrue(true);
    }
}
