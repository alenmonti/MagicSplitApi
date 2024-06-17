<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $table = 'group_invitations';

    protected $fillable = ['group_id', 'user_id', 'status'];

    public $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accept()
    {
        $this->status = 'accepted';
        $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }


}
