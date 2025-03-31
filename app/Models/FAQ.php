<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = '_f_a_q';
    
    protected $fillable = ['question', 'answer', 'category'];


}
