<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PatientFile extends Model
{
    use SoftDeletes;

   // app/Models/PatientFile.php

protected $fillable = [
    'patient_id',
    'blood_type', // Maintenant un enum
    'height',
    'weight',
    'allergies', 
    'medical_history',
    'current_medications', 
    'chronic_conditions',
    'surgical_history',
    'family_history'
];

protected $casts = [
    'height' => 'decimal:2',
    'weight' => 'decimal:2',
    'allergies' => 'array', 
    'current_medications' => 'array', 
    'deleted_at' => 'datetime'
];

    protected $appends = ['bmi', 'bmi_category'];

    /**
     * Relationship with the patient (User model)
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id')
            ->withTrashed()
            ->withDefault([
                'name' => '[Deleted Patient]'
            ]);
    }

    /**
     * Relationship with appointments
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    /**
     * Calculate BMI (Body Mass Index)
     */
    public function getBmiAttribute(): ?float
    {
        if (!$this->height || !$this->weight) {
            return null;
        }
        
        return round($this->weight / pow($this->height / 100, 2), 1);
    }

    /**
     * Get BMI category
     */
    public function getBmiCategoryAttribute(): ?string
    {
        if (!$this->bmi) return null;

        return match(true) {
            $this->bmi < 18.5 => 'Underweight',
            $this->bmi < 25 => 'Normal weight',
            $this->bmi < 30 => 'Overweight',
            default => 'Obese'
        };
    }

    /**
     * Scope for patients with allergies
     */
    public function scopeWithAllergies(Builder $query): Builder
    {
        return $query->whereNotNull('allergies');
    }

    /**
     * Scope for patients with chronic conditions
     */
    public function scopeWithChronicConditions(Builder $query): Builder
    {
        return $query->whereNotNull('chronic_conditions');
    }

    /**
     * Scope for patients taking specific medications
     */
    public function scopeTakingMedication(Builder $query, string $medication): Builder
    {
        return $query->where('current_medications', 'like', "%{$medication}%");
    }

    /**
     * Check if patient has known allergies
     */
    public function hasAllergies(): bool
    {
        return !empty($this->allergies);
    }

    /**
     * Check if patient takes any medications
     */
    public function takesMedications(): bool
    {
        return !empty($this->current_medications);
    }

    /**
     * Get blood type with RH factor
     */
    public function getFullBloodTypeAttribute(): string
    {
        return $this->blood_type 
            ? "Blood Type: {$this->blood_type}" 
            : "Blood Type: Unknown";
    }
}