<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'identityNumber',
        'address',
        'city',
        'password',
        'gender',
        'role',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    /**
     * Generate unique user_id with format SWR-USER_XXXXX
     */
    public static function generateUserId(): string
    {
        $lastUser = self::orderBy('id', 'desc')->first();
        
        if ($lastUser && strpos($lastUser->user_id, 'SWR-USER_') === 0) {
            // Extract number from existing format and increment
            $number = intval(substr($lastUser->user_id, 9)) + 1;
        } else {
            // Start from 1 if no previous user exists
            $number = 1;
        }
        
        return 'SWR-USER_' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = self::generateUserId();
            }
        });
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($roleName): bool
    {
        return $this->role === $roleName;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}