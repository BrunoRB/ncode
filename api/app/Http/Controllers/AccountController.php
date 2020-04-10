<?php

namespace App\Http\Controllers;

use App\Http\Resources\Account as ResourcesAccount;
use App\Http\Resources\Transaction as ResourcesTransaction;
use App\Models\Account;
use App\Models\Currency as C;
use App\Models\Transaction;
use Money\Money;
use Money\Currency as MoneyCurrency;


use Illuminate\Http\Request;

class AccountController extends Controller
{

    public function createTransaction(Request $request, Account $account)
    {
        $validatedData = $request->validate([
            'to' => 'required|integer|min:1',
            'details' => 'nullable|string',
            'amount' => [
                'required',
                'string',
                'regex:/^(\d+)(?:\.(\d{1,2}))?$/',
                function ($attribute, $value, $fail) {
                    if ($value <= 0) {
                        $fail($attribute.' should be a positive value.');
                    }
                },
            ]
        ]);

        $transferAmount = new Money(
            C::toCents($validatedData['amount']),
            new MoneyCurrency($account->currency->iso_code)
        );

        $to = Account::findOrFail($request['to']);

        if ($account->id === $to->id) {
            abort(400, 'Cannot transfer from one account to itself');
        } elseif ($transferAmount->greaterThan($account->balance)) {
            abort(400, 'Not enough money to transfer');
        }

        $t = Transaction::makeTransaction($transferAmount, $account, $to, $validatedData['details'] ?? null);
        return new ResourcesTransaction($t);
    }

    public function list(Request $request, Account $account)
    {
        $xs = $account
            ->all_transactions()
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return ResourcesTransaction::collection($xs);
    }

    public function find(Request $request, Account $account)
    {
        return new ResourcesAccount($account);
    }
}
