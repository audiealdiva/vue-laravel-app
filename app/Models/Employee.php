<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Models\Audit;

class Employee extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 
        'name', 
        'email', 
        'is_active', 
        'metadata', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'employee_project', 'employee_id', 'project_id');
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}