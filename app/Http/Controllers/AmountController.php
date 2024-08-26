<?php


namespace App\Http\Controllers;

use App\Models\Amount;
use Illuminate\Http\Request;

class AmountController extends Controller
{
    // Display a listing of the amounts
    public function index()
    {
        $amounts = Amount::all()->map(function ($amount) {
            $amount->paymentAmount = number_format($amount->paymentAmount, 0, ',', '.');
            return $amount;
        });
        return response()->json($amounts);
    }

    // Show the form for creating a new amount
    public function create()
    {
        // Typically, you'd return a view here, but for API just return a response
        return response()->json(['message' => 'Display form for creating a new amount']);
    }

    // Store a newly created amount in the database
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'paymentAmount' => 'required|numeric',
        ]);

        $amount = Amount::create($validatedData);

        // Format paymentAmount before returning the response
        $amount->paymentAmount = number_format($amount->paymentAmount, 0, ',', '.');

        return response()->json($amount, 201); // 201 status code for created
    }

    // Display the specified amount
    public function show($id)
    {
        $amount = Amount::find($id);

        if (!$amount) {
            return response()->json(['message' => 'Amount not found'], 404);
        }

        // Format paymentAmount before returning the response
        $amount->paymentAmount = number_format($amount->paymentAmount, 0, ',', '.');

        return response()->json(['data' => $amount->toArray()]);
    }

    // Show the form for editing the specified amount
    public function edit($id)
    {
        // Typically, you'd return a view here, but for API just return a response
        return response()->json(['message' => 'Display form for editing the amount with ID ' . $id]);
    }

    // Update the specified amount in the database
    public function update(Request $request, $id)
    {
        $amount = Amount::find($id);

        if (!$amount) {
            return response()->json(['message' => 'Amount not found'], 404);
        }

        $validatedData = $request->validate([
            'paymentAmount' => 'required|numeric',
        ]);

        $amount->update($validatedData);

        // Format paymentAmount before returning the response
        $amount->paymentAmount = number_format($amount->paymentAmount, 0, ',', '.');

        return response()->json($amount);
    }

    // Remove the specified amount from the database
    public function destroy($id)
    {
        $amount = Amount::find($id);

        if (!$amount) {
            return response()->json(['message' => 'Amount not found'], 404);
        }

        $amount->delete();

        return response()->json(['message' => 'Amount deleted successfully']);
    }
}
