<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Rental;

class CarController extends Controller
{
    public function register(Request $request)
    {

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'reg_no' => 'required|string|max:255|unique:cars',
            'price' => 'required|numeric',
            'car_model_id' => 'required|exists:car_models,id'
        ]);
       
        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()],422);
        }
        
        // Create new car 
        $car = Car::create([
            'reg_no' => $request->input('reg_no'),
            'price' => $request->input('price'),
            'car_model_id' => $request->input('car_model_id'),
            
        ]);
        
        // Return success response with car model details
        return response()->json(['message' => 'Car registered successfully', 'car' => $car], 201);
    }

    public function show(Request $request,$filter=null)
    {
        if ($filter === 'available') {
            // Perform the left join query to fetch available cars
            $cars = Car::leftJoin('rentals', function ($join) {
                    $join->on('cars.id', '=', 'rentals.car_id')
                        ->where(function ($query) {
                            $query->where('rentals.status', 'complete')
                                ->orWhere('rentals.status', 'cancelled');
                        });
                })
                ->whereNull('rentals.car_id')
                ->join('car_models', 'cars.car_model_id', '=', 'car_models.id')
                ->select('cars.reg_no', 'cars.price', 'car_models.name as model_name', 'car_models.make')
                ->get();
        } else {
            // Fetch all cars with joins
            $cars = DB::table('cars')
                ->join('car_models', 'cars.car_model_id', '=', 'car_models.id')
                ->select('cars.reg_no', 'cars.price', 'car_models.name as model_name', 'car_models.make')
                ->get();
        }
    
        return response()->json($cars);
    
    }

    
    
}
