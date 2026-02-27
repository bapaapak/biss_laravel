<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCategory extends Model
{
    public $timestamps = false;
    protected $table = 'master_categories';
    protected $fillable = ['category_name'];
}
