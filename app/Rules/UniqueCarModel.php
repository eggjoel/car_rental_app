<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use App\Models\CarModel;

class UniqueCarModel implements ImplicitRule
{
    public function passes($attribute, $value)
    {
        // Convert all values to lowercase for case-insensitive comparison
        $name = strtolower($value['name']);
        $make = strtolower($value['make']);
        $year = $value['year'];
        
        // Check if a car model with the same combination exists
        return !CarModel::whereRaw('LOWER(name) = ? AND LOWER(make) = ? AND year = ?', [$name, $make, $year])->exists();
    }

    public function message()
    {
        return 'The combination of name, make, and year must be unique.';
    }
}
