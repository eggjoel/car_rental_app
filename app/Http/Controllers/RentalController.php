<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RentalController extends Controller
{
    public function register(Request $request, $regNo)
{
    try {
        // Validate the registration number
        $validator = Validator::make(['regNo' => $regNo], [
            'regNo' => ['required', 'exists:cars,reg_no'],
        ], [
            'regNo.required' => 'The car registration number field is required.',
            'regNo.exists' => 'The selected car does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check if the specified car is associated with any rental record with status pending or ongoing
        $existingRental = Rental::whereHas('car', function ($query) use ($regNo) {
            $query->where('reg_no', $regNo);
        })->whereIn('status', ['pending', 'ongoing'])->exists();

        if ($existingRental) {
            return response()->json(['error' => 'The selected car is already rented or pending.'], 422);
        }

        // Retrieve the currently authenticated user
        $user = Auth::user();

        // Find the car by registration number
        $car = Car::where('reg_no', $regNo)->firstOrFail();

        // Create a new rental record associated with the authenticated user
        $rental = new Rental();
        $rental->status = 'pending';
        $rental->user_id = $user->id;
        $rental->car_id = $car->id;
        $rental->save();

        return response()->json(['message' => 'Rental registered successfully.'], 200);
    } catch (\Exception $e) {
        // Print the actual error message
        Log::error($e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function show(Request $request)
{
    // Retrieve rentals with related car and car model information
    $rentals = Rental::with('car.model');

    // Apply filters based on status if provided in the request
    if ($request->has('status')) {
        $status = $request->input('status');
        if (in_array($status, ['pending', 'ongoing', 'complete', 'cancelled'])) {
            $rentals->where('status', $status);
        }
    }

    // Fetch the filtered rentals
    $filteredRentals = $rentals->get();

    // Modify the response to include specified fields from rentals
    $rentalsData = $filteredRentals->map(function ($rental) {
        return [
            'id' => $rental->id, // Include the ID field
            'make' => $rental->car->model->make,
            'model' => $rental->car->model->name,
            'regno' => $rental->car->reg_no,
            'start_date' => $rental->start_date,
            'status' => $rental->status,
            'total_price' => $rental->total_price,
        ];
    });

    return response()->json($rentalsData);
}

public function modify(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'action' => 'required|in:cancel,confirm,complete',
        'id' => 'required|exists:rentals,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Get the logged-in user's rentals
    $user = Auth::user();
    $targetRental = Rental::with('car')->where('id', $request->id)->firstOrFail();

    // Check if the rental belongs to the logged-in user
    if ($targetRental->user_id !== $user->id) {
        return response()->json(['error' => 'You are not authorized to modify this rental.'], 403);
    }

    // Perform action based on request
    switch ($request->action) {
        case 'cancel':
            if ($targetRental->status !== 'pending') {
                return response()->json(['error' => 'Only pending rentals can be cancelled.'], 422);
            }
            $targetRental->status = 'cancelled';
            break;
        case 'confirm':
            if ($targetRental->status !== 'pending') {
                return response()->json(['error' => 'Only pending rentals can be confirmed.'], 422);
            }
            $targetRental->status = 'ongoing';
            $targetRental->start_date = Carbon::now()->toDateString(); // Set start_date to today without time
            break;
        case 'complete':
            if ($targetRental->status !== 'ongoing') {
                return response()->json(['error' => 'Only ongoing rentals can be completed.'], 422);
            }
            $targetRental->status = 'complete';
            $targetRental->end_date = Carbon::now()->toDateString(); // Set end_date to today without time
            $startDate = Carbon::parse($targetRental->start_date);
            $endDate = Carbon::parse($targetRental->end_date);
            $numberOfDays = $startDate->diffInDays($endDate);
            $pricePerDay = $targetRental->car->price;
            $targetRental->total_price = $numberOfDays * $pricePerDay;
            break;
        default:
            return response()->json(['error' => 'Invalid action.'], 422);
    }

    // Save changes to rental
    $targetRental->save();

    return response()->json(['message' => 'Rental modified successfully.'], 200);
}




}
