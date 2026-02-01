<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditTrail;

class Document extends Model
{
    use HasFactory, HasAuditTrail;

    protected $table = 'hr_documents';

    protected $fillable = [
        'employee_id',
        'document_type',
        'document_number',
        'issue_date',
        'expiry_date',
        'file_path',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
