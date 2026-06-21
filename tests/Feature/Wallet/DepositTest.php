<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;
    /**
     * pour se test nous allons utiliser le principe de AAA qui signifie Arrange - Act - Assert
     * pour Arrange : on se pose la question de quoi avons nous besoin pour faire se test.
     *          et dans mon cas on a besoin d'un utilisateur qui est associer directement a son wallet ainsi que de son Token d'authentification
     * Pour Act : on se pose la question de savoir quel action devons nous mené pour faire se test
     *      dans notre cas on ferras appel a la methodes POST sur /api/wallet/deposit
     * et Pour Assert on se pose la question de savoir quel resultat attendons nous a la fin de se test
     *      et dans notre cas le résultat attendu est 201 et une augmentation du solde ainsi que de la creation de la transaction
     */

    public function test_authentificated_user_can_deposit_money() {
        // Arrange : Création d'un utilisateur avec son wallet directement associer

        $user = User::factory()->create();

        $wallet = $user->wallet;
        // $wallet = Wallet::factory()->create([
        //     'user_id' => $user->id,
        //     'balance' => 0,
        // ]);

        //Act : Effectuons un dépot de 100 000 FCFA

        $response = $this->actingAs($user)
            ->postJson('/api/wallet/deposit', [
                'amount' => 100000,
            ]);

        //Assert : on verifie que le status est bien 201
        $response->assertStatus(201);

        //Assert: on verifie que le solde a été crédicté

        $wallet->refresh();
        $this->assertEquals(100000, $wallet->balance);

        //Assert : on verifie qu'une transaction a été créer

        $this->assertDatabaseHas('transactions', [
            'to_wallet_id' => $wallet->id,
            'amount' => 100000,
            'type' => 'deposit',
            'status' => 'completed',
        ]);

    }

    public function test_unauthenticated_user_cannot_deposit() {
        //Act :  ici on vas essayer de tenter un dépot sans authentification

        $response = $this->postJson('/api/wallet/deposit', [
            'amount' => 100000,
        ]);

        //Assert : on verifie le status a retouner

        $response->assertStatus(401);
    }

    public function test_cannot_deposit_more_than_one_million() {
        // Arrange

        $user = User::factory()->create();
        // Wallet::factory()->create(['user_id' => $user->id]);

        //Act
        $response = $this->actingAs($user)
        ->postJson('/api/wallet/deposit', [
            'amount' => 2000000,
        ]);

        //Assert
        $response->assertStatus(422);
    }
}
