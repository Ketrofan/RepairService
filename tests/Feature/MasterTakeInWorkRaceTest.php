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

final class MasterTakeInWorkRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_take_in_work_is_atomic_second_attempt_gets_409(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $master = User::query()->create([
            'name' => 'Master',
            'email' => 'master_test@example.com',
            'password' => 'password',
            'role' => UserRole::Master->value,
        ]);

        $req = RepairRequest::query()->create([
            'client_name' => 'Клиент',
            'phone' => '+79991112233',
            'address' => 'Адрес',
            'problem_text' => 'Проблема',
            'status' => RequestStatus::Assigned->value,
            'assigned_master_id' => $master->id,
        ]);

        // 1) Первый take: OK
        $this->actingAs($master)
            ->postJson(route('master.requests.take', $req))
            ->assertOk();

        $req->refresh();
        $status = is_object($req->status) ? $req->status->value : $req->status;
        $this->assertSame(RequestStatus::InProgress->value, $status);

        // 2) Второй take: 409
        $this->actingAs($master)
            ->postJson(route('master.requests.take', $req))
            ->assertStatus(409)
            ->assertJsonFragment(['message' => 'Заявка уже взята в работу или недоступна для взятия.']);
    }
}