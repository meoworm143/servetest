<?php

namespace Modules\ProviderManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagement\Entities\User;

class WithdrawRequest extends Model
{
    use HasFactory, HasUuid;

    protected $casts = [
        'amount' => 'float',
        'is_paid' => 'integer',
    ];

    protected $fillable = [
        'user_id',
        'request_updated_by',
        'amount',
        'request_status',
        'is_paid',
        'note',
        'admin_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function request_updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'request_updated_by');
    }

    protected static function newFactory()
    {
        return \Modules\ProviderManagement\Database\factories\WithdrawRequestFactory::new();
    }
}
