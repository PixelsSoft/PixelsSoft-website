<?php

namespace App\Support\Freelancer;

class FreelancerApiQuery
{
    /**
     * Build a query string using official Freelancer-style repeated array keys (jobs[], users[], etc.).
     *
     * @param  array<string, scalar|null>  $scalar
     * @param  array<string, array<int, scalar>>  $repeated
     */
    public static function build(array $scalar, array $repeated = []): string
    {
        $parts = [];

        foreach ($scalar as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $parts[] = rawurlencode((string) $key).'='.rawurlencode((string) $value);
        }

        foreach ($repeated as $key => $values) {
            foreach (array_values($values) as $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $parts[] = rawurlencode($key.'[]').'='.rawurlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }
}
