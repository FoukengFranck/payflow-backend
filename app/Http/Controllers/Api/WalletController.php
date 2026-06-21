<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psy\Util\Json;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Retourne le solde wallet de l'utilisateur connecté
     */

    public function balance(Request $request): JsonResponse {

        $request->user();
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => $wallet->balance,
            'currency' => 'FCFA',
        ]);
    }

    /**
     * Simulation de dépôt de d'argent sur le wallet
     */
    public function deposit(DepositRequest $request): JsonResponse {
        $user = $request->user();
        $wallet = $user->wallet;
        $amount = $request->amount;

        $transaction = DB::transaction(function () use ($wallet, $amount) {
            $wallet->increment('balance', $amount);

            return Transaction::created([
                'from_wallet_id' => null,
                'to_wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed',
            ]);
        });

        $wallet->refresh();

        return response()->json([
            'message' => 'Dépôt effectué avec succés',
            'transaction' => $transaction,
            'new_balance' => $wallet->balance,
        ], 201);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
