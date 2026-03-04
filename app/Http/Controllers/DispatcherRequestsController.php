<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\AssignMasterRequest;
use App\Models\RepairRequest;
use App\Models\User;
use App\Services\RepairRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\View\View;

final class DispatcherRequestsController extends Controller
{
    public function index(HttpRequest $request): View
    {
        $status = $request->query('status');

        $query = RepairRequest::query()->with('assignedMaster')->orderByDesc('id');

        if ($status && in_array($status, RequestStatus::values(), true)) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $masters = User::query()
            ->where('role', 'master')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dispatcher.requests.index', [
            'requests' => $requests,
            'masters' => $masters,
            'status' => $status,
            'allStatuses' => RequestStatus::values(),
        ]);
    }

    public function assign(RepairRequest $repairRequest, AssignMasterRequest $request, RepairRequestService $service): RedirectResponse
    {
        $service->assignMaster($repairRequest, (int) $request->validated()['assigned_master_id']);

        return back()->with('success', 'Мастер назначен. Статус: ASSIGNED.');
    }

    public function cancel(RepairRequest $repairRequest, RepairRequestService $service): RedirectResponse
    {
        $service->cancel($repairRequest);

        return back()->with('success', 'Заявка отменена. Статус: CANCELED.');
    }
}