<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Appointment;
use App\Models\Slot;
use App\Models\PatientFile;
/**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 * @method bool hasAllRoles(string|array $roles)
 * @method bool hasPermissionTo(string|array $permission, string $guardName = null)
 * @method bool hasAnyPermission(string|array $permissions)
 * @method bool can(string $ability, mixed $arguments = null)
 * @method bool canAny(array $abilities)
 */

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // SoftDeletes : un compte supprimé est conservé (deleted_at) pour garder l'historique des rendez-vous
    use  Notifiable, HasFactory, HasRoles, SoftDeletes;

    // ===== Champs assignables =====
    // deactivated_at n'est pas assignable : il est géré par l'admin (deactivate / reactivate)
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'address',
    'date_of_birth',
    'gender',
    'specialty',
];


    // ===== Champs cachés =====
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'date_of_birth' => 'date:Y-m-d',
    ];

    // ===== ÉTAT DU COMPTE =====

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function deactivate(): void
    {
        $this->forceFill(['deactivated_at' => now()])->save();
    }

    public function reactivate(): void
    {
        $this->forceFill(['deactivated_at' => null])->save();
    }

    /**
     * Comptes non désactivés (ex. médecins visibles par les patients)
     */
    public function scopeNotDeactivated(Builder $query): Builder
    {
        return $query->whereNull('deactivated_at');
    }



    // ===== RELATIONS =====

 /**
 * Get all appointments where the user is the doctor.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function doctorAppointments()
{
    return $this->hasMany(Appointment::class, 'doctor_id');
}

/**
 * Get all appointments where the user is the patient.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function patientAppointments()
{
    return $this->hasMany(Appointment::class, 'patient_id');
}


    public function slots()
    {
        return $this->hasMany(Slot::class, 'doctor_id');
    }

    public function patientFile()
    {
        return $this->hasOne(PatientFile::class, 'patient_id');
    }

    // ===== HELPERS =====

    /**
     * Name of the dashboard route matching the user's role ('home' if no role).
     */
    public function dashboardRoute(): string
    {
        return match (true) {
            $this->hasRole('admin') => 'admin.dashboard',
            $this->hasRole('doctor') => 'doctor.dashboard',
            $this->hasRole('patient') => 'patient.dashboard',
            default => 'home',
        };
    }
}
