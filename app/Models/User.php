<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use  Notifiable, HasFactory, HasRoles;

    // ===== Champs assignables =====
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'address',
    'date_of_birth',
    'gender',
];


    // ===== Champs cachés =====
    protected $hidden = [
        'password',
        'remember_token',
    ];



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
}
