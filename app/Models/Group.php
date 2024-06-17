<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'admin_id', 'description'
    ];

    public $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['balance', 'admin']);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function groupInvitations()
    {
        return $this->hasMany(GroupInvitation::class);
    }

    public function groupPayments()
    {
        return $this->hasMany(GroupPayment::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
