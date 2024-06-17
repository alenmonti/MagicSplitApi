<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime:Y-m-d H:i',
            'updated_at' => 'datetime:Y-m-d H:i',
        ];
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withPivot(['balance', 'admin']);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id');
    }

    public function groupInvitations()
    {
        return $this->hasMany(GroupInvitation::class);
    }

    public function groupPayments()
    {
        return $this->hasMany(GroupPayment::class, 'recipient_id');
    }

    public function groupPaymentsAsPayer()
    {
        return $this->hasMany(GroupPayment::class, 'payer_id');
    }

    public function allGroupPayments()
    {
        return $this->hasMany(GroupPayment::class, 'recipient_id')
            ->orWhere('payer_id', $this->id);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function isAdminOfGroup($group)
    {
        return $group->users()->wherePivot('admin', true)->where('user_id', $this->id)->exists();
    }
}
