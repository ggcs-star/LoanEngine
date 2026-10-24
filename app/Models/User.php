<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'channel_name',
        'channel_url',
        'mobile',
        'address',
        'city',
        'employment_type',
        'annual_income',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'annual_income' => 'integer',
        ];
    }

    public function financialEvents(): HasMany
    {
        return $this->hasMany(FinancialEvent::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class);
    }
    public function calculatorUsages(): HasMany
    {
        return $this->hasMany(CalculatorUsage::class);
    }
}
