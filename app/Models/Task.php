<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Models\Audit;

class Task extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'project_id',
        'employee_id',
        'title',
        'description',
        'details',
        'duedate',
        'is_done',
        'document',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}
