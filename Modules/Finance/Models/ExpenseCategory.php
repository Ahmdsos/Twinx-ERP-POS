<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounting\Models\Account;

class ExpenseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'account_id',
        'is_active',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
