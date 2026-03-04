<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_home_redirects_to_create_request(): void
    {
        $this->get('/')->assertRedirect('/requests/create');
    }
}