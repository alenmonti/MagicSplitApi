<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FictitiousUser extends Model
{
    protected $table = 'users'; 
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'email_verified_at', 'remember_token'];

    public $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withPivot('balance');
    }
}
