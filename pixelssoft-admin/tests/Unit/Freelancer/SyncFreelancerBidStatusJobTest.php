<?php

namespace Tests\Unit\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerBidStatusJob;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SyncFreelancerBidStatusJobTest extends TestCase
{
    public function test_maps_common_remote_bid_statuses(): void
    {
        $job = new SyncFreelancerBidStatusJob(1);
        $method = new ReflectionMethod(SyncFreelancerBidStatusJob::class, 'mapRemoteStatus');
        $method->setAccessible(true);

        $this->assertSame('accepted', $method->invoke($job, ['awarded' => true]));
        $this->assertSame('withdrawn', $method->invoke($job, ['retracted' => true]));
        $this->assertSame('rejected', $method->invoke($job, ['status' => 'lost']));
        $this->assertSame('closed', $method->invoke($job, ['status' => 'closed']));
        $this->assertNull($method->invoke($job, ['status' => 'pending']));
    }
}
