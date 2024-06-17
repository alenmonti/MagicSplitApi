<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPayment extends Model
{
    use HasFactory;

    protected $table = 'group_payments';

    protected $fillable = ['group_id', 'payer_id', 'recipient_id', 'amount', 'status'];

    public $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id', 'id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id', 'id');
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
