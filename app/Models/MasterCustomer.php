<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    use HasFactory;

    protected $table = 'master_customers';

    protected $fillable = [
        'customer_code',
        'customer_name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_customers', 'master_customer_id', 'user_id');
    }
}
