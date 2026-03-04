<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use App\Services\RepairRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PublicRepairRequestController extends Controller
{
    public function create(): View
    {
        return view('requests.create');
    }

    public function store(StoreRepairRequestRequest $request, RepairRequestService $service): RedirectResponse
    {
        $repairRequest = $service->createNew($request->validated());

        return redirect()
            ->route('requests.create')
            ->with('success', 'Заявка создана. Номер: #'.$repairRequest->id);
    }
}