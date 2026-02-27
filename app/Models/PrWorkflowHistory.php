<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrWorkflowHistory extends Model
{
    use HasFactory;

    protected $table = 'pr_workflow_history';
    protected $fillable = ['pr_number', 'action', 'notes', 'actor_id'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
