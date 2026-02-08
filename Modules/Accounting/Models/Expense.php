<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_date',
        'reference_number',
        'category_id', // Nullable for ad-hoc
        'payment_account_id',
        'amount',
        'tax_amount',
        'total_amount',
        'payee',
        'user_id',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'pos_shift_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function category()
    {
        return $this->belongsTo(\Modules\Finance\Models\ExpenseCategory::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'EXP-' . strtoupper(uniqid());
            }
        });
    }
}
