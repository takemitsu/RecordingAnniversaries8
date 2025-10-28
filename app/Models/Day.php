<?php

namespace App\Models;

use App\Services\DateCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Day extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function entity()
    {
        return $this->belongsTo('App\Models\Entity');
    }

    protected $appends = ['diff_days'];

    public function getDiffDaysAttribute()
    {
        $dateCalculationService = app(DateCalculationService::class);

        return $dateCalculationService->calculateDiffDays($this->anniv_at);
    }
}
