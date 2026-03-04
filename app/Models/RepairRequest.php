<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RepairRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'client_name',
        'phone',
        'address',
        'problem_text',
        'status',
        'assigned_master_id',
    ];

    protected $casts = [
        'status' => RequestStatus::class,
        'assigned_master_id' => 'integer',
    ];

    public function assignedMaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_master_id');
    }
}