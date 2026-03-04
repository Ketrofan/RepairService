<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RepairRequestService
{
    public function createNew(array $data): RepairRequest
    {
        $data['status'] = RequestStatus::New->value;
        $data['assigned_master_id'] = null;

        return RepairRequest::query()->create($data);
    }

    public function assignMaster(RepairRequest $repairRequest, int $masterId): void
    {
        if ($repairRequest->status !== RequestStatus::New) {
            throw ValidationException::withMessages([
                'status' => 'Назначить мастера можно только для заявок со статусом NEW.',
            ]);
        }

        $master = User::query()
            ->whereKey($masterId)
            ->where('role', 'master')
            ->first();

        if (!$master) {
            throw ValidationException::withMessages([
                'assigned_master_id' => 'Выбранный мастер не найден.',
            ]);
        }

        $repairRequest->forceFill([
            'assigned_master_id' => $master->id,
            'status' => RequestStatus::Assigned->value,
        ])->save();
    }

    public function cancel(RepairRequest $repairRequest): void
    {
        if (in_array($repairRequest->status, [RequestStatus::Done, RequestStatus::Canceled], true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя отменить заявку в статусе DONE или CANCELED.',
            ]);
        }

        $repairRequest->forceFill([
            'status' => RequestStatus::Canceled->value,
        ])->save();
    }

    /**
     * Гонка take-in-work: атомарный UPDATE по условию.
     * Возвращает true если удалось взять, false если уже взяли/нельзя.
     */
    public function takeInWorkAtomic(int $repairRequestId, int $masterId): bool
    {
        $affected = DB::table('requests')
            ->where('id', $repairRequestId)
            ->where('assigned_master_id', $masterId)
            ->where('status', RequestStatus::Assigned->value)
            ->update([
                'status' => RequestStatus::InProgress->value,
                'updated_at' => now(),
            ]);

        return $affected === 1;
    }

    public function markDone(RepairRequest $repairRequest, int $masterId): void
    {
        if ((int) $repairRequest->assigned_master_id !== $masterId) {
            throw ValidationException::withMessages([
                'assigned_master_id' => 'Эта заявка назначена другому мастеру.',
            ]);
        }

        if ($repairRequest->status !== RequestStatus::InProgress) {
            throw ValidationException::withMessages([
                'status' => 'Завершить можно только заявку со статусом IN_PROGRESS.',
            ]);
        }

        $repairRequest->forceFill([
            'status' => RequestStatus::Done->value,
        ])->save();
    }
}