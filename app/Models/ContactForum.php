<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactForum extends Model
{
    protected $table = 'contact_forum';
    
    protected $fillable = [
        'email',
        'message',
    ];  
}
