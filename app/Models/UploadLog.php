<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadLog extends Model
{
    protected $fillable = [
        'batch_id',
        'file_name',
        'file_type',
        'shop_name',
        'total_rows_imported',
        'uploaded_by'
    ];

    // Hubungan balik ke user yang mengunggah
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}   
