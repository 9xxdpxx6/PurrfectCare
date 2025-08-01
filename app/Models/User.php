<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Filterable, HasDeleteDependenciesCheck;

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
}
