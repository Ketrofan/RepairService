<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

final class DispatcherAssignMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_assign_master_to_new_request(): void
    {
        // CSRF для web POST в тестах не нужен — отключаем, чтобы не заниматься токенами.
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $dispatcher = User::query()->create([
            'name' => 'Dispatcher',
            'email' => 'dispatcher_test@example.com',
            'password' => 'password',
            'role' => UserRole::Dispatcher->value,
        ]);

        $master = User::query()->create([
            'name' => 'Master',
            'email' => 'master_test@example.com',
            'password' => 'password',
            'role' => UserRole::Master->value,
        ]);

        $req = RepairRequest::query()->create([
            'client_name' => 'Тест Клиент',
            'phone' => '+79990000000',
            'address' => 'Тест адрес',
            'problem_text' => 'Тест проблема',
            'status' => RequestStatus::New->value,
            'assigned_master_id' => null,
        ]);

        $this->actingAs($dispatcher)
            ->post(route('dispatcher.requests.assign', $req), [
                'assigned_master_id' => $master->id,
            ])
            ->assertRedirect();

        $req->refresh();

        $status = is_object($req->status) ? $req->status->value : $req->status;
        $this->assertSame(RequestStatus::Assigned->value, $status);
        $this->assertSame($master->id, (int) $req->assigned_master_id);
    }
}