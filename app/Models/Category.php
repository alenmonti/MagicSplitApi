<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name'
    ];

    public $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
