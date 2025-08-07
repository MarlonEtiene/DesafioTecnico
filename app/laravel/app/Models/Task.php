<?php

namespace App\Models;

use App\Traits\MultiTenantable;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use MultiTenantable;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
