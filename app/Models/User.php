<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'full_name',
        'email_notifications',
        'phone',
        'active',
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
            'email_notifications' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** Alias para la vista (rol en español). Preferencia: primer rol Spatie, sino columna role. */
    public function getRolAttribute(): string
    {
        $roleNames = $this->getRoleNames();
        if ($roleNames->isNotEmpty()) {
            return $roleNames->first();
        }
        return $this->attributes['role'] ?? 'viewer';
    }

    /**
     * Destinatarios del digest de alertas críticas: administradores y supervisores
     * activos con correo en el dominio sover.cl.
     *
     * @return Collection<int, User>
     */
    public static function criticalAlertDigestRecipients(): Collection
    {
        return static::query()
            ->role(['administrator', 'supervisor'])
            ->where('active', true)
            ->whereNotNull('email')
            ->whereRaw('LOWER(TRIM(email)) LIKE ?', ['%@sover.cl'])
            ->get();
    }
}
