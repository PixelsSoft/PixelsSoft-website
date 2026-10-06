<?php

namespace Tests\Unit\Freelancer;

use App\Support\Freelancer\FreelancerApiQuery;
use PHPUnit\Framework\TestCase;

class FreelancerApiQueryTest extends TestCase
{
    public function test_builds_official_repeated_array_keys(): void
    {
        $query = FreelancerApiQuery::build(
            ['limit' => 50, 'query' => 'laravel developer'],
            [
                'jobs' => [17, 9],
                'users' => [12345],
            ]
        );

        $this->assertStringContainsString('limit=50', $query);
        $this->assertStringContainsString('query=laravel%20developer', $query);
        $this->assertStringContainsString('jobs%5B%5D=17', $query);
        $this->assertStringContainsString('jobs%5B%5D=9', $query);
        $this->assertStringContainsString('users%5B%5D=12345', $query);
        $this->assertStringNotContainsString('jobs%5B0%5D', $query);
    }
}
