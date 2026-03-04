<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\RepairRequest;
use App\Services\RepairRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\View\View;

final class MasterRequestsController extends Controller
{
    public function index(HttpRequest $request): View
    {
        $userId = (int) $request->user()->id;

        $requests = RepairRequest::query()
            ->where('assigned_master_id', $userId)
            ->whereIn('status', [RequestStatus::Assigned->value, RequestStatus::InProgress->value])
            ->orderByDesc('id')
            ->paginate(20);

        return view('master.requests.index', [
            'requests' => $requests,
        ]);
    }

    public function take(RepairRequest $repairRequest, HttpRequest $request, RepairRequestService $service): JsonResponse|RedirectResponse
    {
        $masterId = (int) $request->user()->id;

        $ok = $service->takeInWorkAtomic((int) $repairRequest->id, $masterId);

        if (!$ok) {
            $message = 'Заявка уже взята в работу или недоступна для взятия.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return back()->with('error', $message);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'OK'], 200);
        }

        return back()->with('success', 'Заявка взята в работу. Статус: IN_PROGRESS.');
    }

    public function done(RepairRequest $repairRequest, HttpRequest $request, RepairRequestService $service): RedirectResponse
    {
        $service->markDone($repairRequest, (int) $request->user()->id);

        return back()->with('success', 'Заявка завершена. Статус: DONE.');
    }
}