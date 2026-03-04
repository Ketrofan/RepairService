<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

final class RepairRequestSeeder extends Seeder
{
    public function run(): void
    {
        $master1 = User::query()->where('email', 'master1@example.com')->first();
        $master2 = User::query()->where('email', 'master2@example.com')->first();

        RepairRequest::query()->create([
            'client_name' => 'Иван Петров',
            'phone' => '+79991112233',
            'address' => 'Казань, ул. Примерная, 1',
            'problem_text' => 'Не включается кондиционер, нужна диагностика. '.str_repeat('Длинный текст. ', 20),
            'status' => RequestStatus::New->value,
            'assigned_master_id' => null,
        ]);

        if ($master1) {
            RepairRequest::query()->create([
                'client_name' => 'Анна Смирнова',
                'phone' => '+79992223344',
                'address' => 'Казань, пр-т Победы, 10',
                'problem_text' => 'Течёт кран на кухне, требуется осмотр.',
                'status' => RequestStatus::Assigned->value,
                'assigned_master_id' => $master1->id,
            ]);
        }

        if ($master2) {
            RepairRequest::query()->create([
                'client_name' => 'Сергей Иванов',
                'phone' => '+79993334455',
                'address' => 'Казань, ул. Ленина, 5',
                'problem_text' => 'Не работает розетка в комнате.',
                'status' => RequestStatus::InProgress->value,
                'assigned_master_id' => $master2->id,
            ]);
        }
    }
}