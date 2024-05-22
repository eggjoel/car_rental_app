<?php

namespace App\Http\Controllers;

use App\Rules\UniqueCarModel;
use App\Http\Requests\StoreCarModelRequest;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarModelController extends Controller
{
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'year' =>['required', 'digits:4'],
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()],422);
        }

        // Create new car model
        $carModel = CarModel::create([
            'name' => $request->input('name'),
            'make' => $request->input('make'),
            'year' => $request->input('year'),
        ]);
        
        // Return success response with car model details
        return response()->json(['message' => 'Car model registered successfully', 'model' => $carModel], 201);
    }

    public function show(Request $request)
    {   
        //Retrieve all car models 
        $models = CarModel::all();
        //Return models
        return response()->json($models,200);
    }
}
