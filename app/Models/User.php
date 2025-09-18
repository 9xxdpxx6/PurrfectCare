<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;
use App\Traits\NormalizesPhone;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Filterable, HasDeleteDependenciesCheck, NormalizesPhone, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'telegram',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $indexes = [
        'name',
        'email',
        'phone',
        'telegram',
    ];

    protected $deleteDependencies = [
        'pets' => 'Невозможно удалить клиента, так как у него есть питомцы',
        'orders' => 'Невозможно удалить клиента, так как с ним связаны заказы',
        'visits' => 'Невозможно удалить клиента, так как с ним связаны приёмы',
    ];

    public function pets() {
        return $this->hasMany(Pet::class, 'client_id');
    }

    public function orders() {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function visits() {
        return $this->hasMany(Visit::class, 'client_id');
    }

    /**
     * Мутатор для автоматической нормализации телефона
     */
    public function setPhoneAttribute($value)
    {
        if ($value) {
            $this->attributes['phone'] = $this->normalizePhone($value);
        }
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\ClientEmailVerificationNotification);
    }
}
