<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
// List all transactions
    public function index()
    {
        return response()->json(Transaction::all(), Response::HTTP_OK);
    }

// Store a new transaction
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'reference' => 'required|string|unique:transactions',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
            'value_date' => 'required|date',
            'payment_type' => 'required|in:mobile,visa,mastercard,eft',
        ]);

        // Create a new instance of the Transaction model
        $transaction = new Transaction();

        // Populate the model with validated data
        $transaction->reference = $request->input('reference');
        $transaction->amount = $request->input('amount');
        $transaction->transaction_date = $request->input('transaction_date');
        $transaction->value_date = $request->input('value_date');
        $transaction->payment_type = $request->input('payment_type');

        // Save the model to the database
        $transaction->save();

        // Return the created transaction as a response
        return response()->json($transaction, Response::HTTP_OK);
    }


// Show a single transaction
    public function show($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($transaction, Response::HTTP_OK);
    }

// Update a transaction
    public function update(Request $request, $id)
    {
        // Find the transaction by ID
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate the incoming request data
        $request->validate([
            'reference' => 'string|unique:transactions,reference,' . $id,
            'amount' => 'numeric',
            'transaction_date' => 'date',
            'value_date' => 'date',
            'payment_type' => 'in:mobile,visa,mastercard,eft',
        ]);

        // Manually populate the model with validated data
        $transaction->reference = $request->input('reference', $transaction->reference);
        $transaction->amount = $request->input('amount', $transaction->amount);
        $transaction->transaction_date = $request->input('transaction_date', $transaction->transaction_date);
        $transaction->value_date = $request->input('value_date', $transaction->value_date);
        $transaction->payment_type = $request->input('payment_type', $transaction->payment_type);

        // Save the updated model to the database
        $transaction->save();

        // Return the updated transaction as a response
        return response()->json($transaction, Response::HTTP_OK);
    }


// Delete a transaction
    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted'], Response::HTTP_OK);
    }

    public function sendToBank(Request $request)
    {
        $transactionIds = $request->input('transaction_ids'); // Array of selected transaction IDs

        // Validate the input
        if (empty($transactionIds)) {
            return response()->json(['error' => 'No transactions selected.'], 400);
        }

        // Find the transactions based on the IDs
        $transactions = Transaction::whereIn('id', $transactionIds)->get();

        // Assuming you are updating the `send_to_bank` field to 1
        foreach ($transactions as $transaction) {
            $transaction->send_to_bank = 1;
            $transaction->save();
        }

        return response()->json(['success' => true]);
    }

    // Handle callback from external service
    public function transactionCallback(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'reference' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        // Extract the reference and amount from the request
        $reference = $request->input('reference');
        $amount = $request->input('amount');

        // Check if the transaction exists
        $transaction = Transaction::where('reference', $reference)->first();

        if (!$transaction) {
            // If the transaction is not found
            return response()->json(['message' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the amount matches
        if ($transaction->amount != $amount) {
            // If the amount doesn't match
            return response()->json(['message' => 'Amount mismatch'], Response::HTTP_BAD_REQUEST);
        }

        // Update the transaction status or perform any necessary logic
        $transaction->bank_confirmed =1; // Example status update
        // Set the current date and time
        $transaction->bank_confirmation_date = Carbon::now();
        $transaction->save();

        // Respond with a success message
        return response()->json(['message' => 'Transaction successfully confirmed'], Response::HTTP_OK);
    }
}
