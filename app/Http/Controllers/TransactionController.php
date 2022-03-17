<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Currency;
use App\Models\Transaction;
use App\Traits\ExchangeRate;
use App\Traits\CanPay;
use Illuminate\Http\Request;
;

class TransactionController extends Controller
{
    use ExchangeRate, CanPay;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // User default currency
        $sourceCurrency = auth()->user()->currency;

        // Default currency balance
        $sourceAmount = auth()->user()->latestCurrencyBalance->USD;

        return $this->converter($sourceCurrency, $sourceCurrency, $sourceAmount, 'application.transactions.create');
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Validate user request
        (array) $validated = $this->validateRequest();
        $amount = Transaction::removeComma($validated['source_amount']);
        $currency = Currency::where('id', $validated['source_currency_id'])->firstOrFail();

        if($this->canMakePayment($currency['code'], $amount)){
            return back()->with('error', 'Oops! Your '.$currency['name'].' account balance is insufficient to complete this transaction.');
        }

        // Return back if source amount is equal to zero
        if($amount == 0.0){
            return back()->with('error', 'Sorry! The source amount cannot be less than '.$currency['symbol'].'1.');
        }

        // Calculate the target amount to be sent to recipient
        $validated = $this->calculation($validated, (float)$amount);

        try{
            // Record first transaction for authenticated user
            if(Transaction::doubleEntryRecord($validated, $amount))
            {
                // Alternate data for recipient transaction record
                $amount = $validated['targetAmount'];
                $validated = $this->alternateSourceRecord($validated);
            }

            // Record second transaction and redirect back to home
            return (Transaction::doubleEntryRecord($validated, $amount))
            ? redirect()->route('home')->with('success', 'Your transaction was successful')
            : back()->with('error', 'Sorry! An error occured while make transfer');
        }catch (\Exception $ex){
            //Record double-entry failed transactions
            Transaction::failedTransaction($validated, $amount, 'Debit', auth()->id(), $validated['recipient_id']);
            Transaction::failedTransaction($validated, $validated['targetAmount'], 'Credit', $validated['recipient_id'], auth()->id());

            // Redirect back to home
            return redirect()->route('home')->with('error', 'Sorry! An error occured while make transaction');
        }
    }

    /**
     * Display the details of the specified transaction.
     * This is an ajax call on transaction details modal
     * Present on click of details button
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        return view('application.show', [
            'transaction'   => Transaction::where('uuid', $uuid)->with('CurrencyBalance')->firstOrFail()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Convert the source amount and currency.
     * This is an ajax call on currency conversion.
     * Present on keyup or on changce of source amount and currency.
     *
     * @param  float  $source_amount
     * @param  int  $source_currency_id
     * @param  int  $target_currency_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sourceConverter(Request $request)
    {
        if ($request->ajax()) {
            (array) $filters = $request->only('source_amount', 'source_currency_id', 'target_currency_id');

            // If source currecnt is empty or `NaN`
            if($filters['source_amount'] == 'NaN' || $filters['source_amount'] == ''){
                return;
            }

            // Last currency balance
            $latestCurrencyBalance = auth()->user()->latestCurrencyBalance;

            // Determine source amount
            if($filters['source_currency_id'] == 1)
            {
                if((float)$filters['source_amount'] > (float)$latestCurrencyBalance->EUR){
                    $sourceAmount = $latestCurrencyBalance->EUR;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }else if($filters['source_currency_id'] == 2)
            {
                if((float)$filters['source_amount'] > (float)$latestCurrencyBalance->NGN){
                $sourceAmount = $latestCurrencyBalance->NGN;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }else{
                if((float)$filters['source_amount'] > (float)$latestCurrencyBalance->USD){
                    $sourceAmount = $latestCurrencyBalance->USD;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }

            // User source currency
            $sourceCurrency = Currency::where('id', $filters['source_currency_id'])->firstOrFail();

            // Recipient target currency
            $targetCurrency = Currency::where('id', $filters['target_currency_id'])->firstOrFail();

            // Pass data to converter method
            return $this->converter($sourceCurrency, $targetCurrency, $sourceAmount, 'application.transactions.includes._transaction_breakdown');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function converter($sourceCurrency, $targetCurrency, $sourceAmount, $view)
    {
        /**
         * Legend
         * VP = Variable Percentage
         * ER = Exchange Rate
         * VF = Variable Fee
         * TC = Transfer Fee
         * ATC = Amount To Convert
         * TA = Target Amount
         */

        /**
         * Calculation
         * Step 1: Variable Fee  = (VP/100) * Source Amount
         * Step 2: Transfer Fee = VF + Fixed Fee
         * Step 3: ATC = Source Amount - Transfer fee
         * Step 4: TA = ATC x ER
         */

        //Use default source currency($) as target currency
        $currency = Charge::where('source_currency_id', $sourceCurrency['id'])
        ->where('target_currency_id', $targetCurrency['id'])
        ->firstOrFail();

        // Fixed fee
        $fixedFee = (float) $currency['fixed_fee'];

        // Variable fee
        (float) $variableFee = ($currency['variable_percentage']/100) * $sourceAmount;

        // Transfer fee
        $transferFee = $variableFee + $fixedFee;

        // Amount the recipient will get
        $amountToConvert = $sourceAmount - $transferFee;

        // Current exchange rate or rate from currency charge
        $rate = (float) $currency['rate'];

        // Target amount after conversion
        $targetAmount = $amountToConvert * $rate;

        // Current user
        $user = auth()->user();

        return view($view, [
            'user'                      => $user,
            'recipients'                =>  \App\Models\User::where([['role_id', 2], ['id', '!=', $user['id']]])->orderBy('name')->get(),
            'currencies'                => Currency::get(),
            'sourceCurrency'            => $sourceCurrency,
            'targetCurrency'            => $targetCurrency,
            'sourceCurrencyBalance'     => $sourceAmount,
            'targetAmount'              => $targetAmount,
            'charges'                   => Charge::get(),
            'summary' => [
                'transferFee'       => number_format($transferFee, 2),
                'amountToConvert'   => number_format($amountToConvert, 2),
                'fixedFee'          => number_format($fixedFee, 2),
                'variableFeeText'   => number_format($variableFee, 2).' '.$sourceCurrency['code'].' ('.$currency['variable_percentage'].'%)',
                'variableFee'       => number_format($variableFee, 2),
                'rate'              => $rate,
            ]
        ]);
    }

    /**
     * Get either currenc exchange rate or fallback rate.
     * @param  float  $rate
     *
     * @return  $rate
     */
    public function getRate($rate, $sourceCurrency, $targetCurrency, $sourceAmount)
    {
        return (float) ($this->currentExchangeRate($sourceCurrency, $targetCurrency, $sourceAmount) == null) ? $rate : $this->currentExchangeRate($sourceCurrency, $targetCurrency, $sourceAmount);
    }

     /**
     * Get variable fee.
     * @param  float  $variablePercentage
     * @param  float  $sourceAmount
     *
     * @return  float
     */
    public function getVariableFee($variablePercentage, $sourceAmount)
    {
        return (float) number_format(($variablePercentage/100) * $sourceAmount, 2);
    }

    /**
     * Execute calculations and conversions
     * @param  array  $validated
     * @param  float  $amount
     *
     * @return array $validated
     */
    public function calculation(array $validated, float $amount)
    {
        // Get charges from source and target currency ID's
        $currency = Charge::where('source_currency_id', $validated['source_currency_id'])
        ->where('target_currency_id', $validated['target_currency_id'])
        ->firstOrFail();

        // Merge new calculated variables into validated array request
        $validated['user_id'] = auth()->id();
        $validated['recipient_id'] = \App\Models\User::where('uuid', $validated['recipient_uuid'])->firstOrFail()->id;
        $validated['variableFee'] = $this->getVariableFee($currency['variable_percentage'], $amount);
        $validated['rate'] = $this->getRate($currency['rate'], $currency['sourceCurrency']['code'], $currency['targetCurrency']['code'], $amount);
        $validated['fixedFee'] = (float) $currency['fixed_fee'];
        $validated['transferFee'] = $validated['variableFee'] + $validated['fixedFee'];
        $validated['amountToConvert'] = $amount - $validated['transferFee'];
        $validated['targetAmount'] = $validated['amountToConvert'] * $validated['rate'];
        $validated['type'] = 'Debit';
        $validated['currency_id'] = $validated['source_currency_id'];
        $validated['sign'] = '-';

        return $validated;
    }

    /**
     * Validate user input fields request
     */
    private function validateRequest()
    {
        return request()->validate([
            'recipient_uuid'        =>  'bail|required|string',
            'source_amount'         =>  'bail|required|between:1,99999999.99|min:1',
            'target_amount'         =>  'bail|required|between:1,99999999.99|min:1',
            'source_currency_id'    =>  'bail|required|numeric|min:1|max:3',
            'target_currency_id'    =>  'bail|required|numeric|min:1|max:3',
        ]);
    }

    /**
     * Convert the source amount and currency.
     * This is an ajax call on currency conversion.
     * Present on keyup or on change of source amount and currency.
     *
     * @param  float  $source_amount
     * @param  int  $source_currency_id
     * @param  int  $target_currency_id
     *
     * @return $sourceCurrency && $sourceCurrencyBalance
     */
    public function currencyBalance(Request $request)
    {
        if ($request->ajax()) {
            (array) $filters = $request->only('source_currency_id');

            // Get currency details
            $sourceCurrency = Currency::findOrFail($filters['source_currency_id']);

            // Last currency balance
            $latestCurrencyBalance = auth()->user()->latestCurrencyBalance;

            // Compare source amount with currency amount available
            if($filters['source_currency_id'] == 1)
            {
                $sourceAmount = $latestCurrencyBalance->EUR;
            }else if($filters['source_currency_id'] == 2)
            {
                $sourceAmount = $latestCurrencyBalance->NGN;
            }else{
                $sourceAmount = $latestCurrencyBalance->USD;
            }

            // Return ajax response
            return [
                'sourceCurrency'            => $sourceCurrency,
                'sourceCurrencyBalance'     => $sourceAmount,
            ];
        }
    }

    /**
     * Alternate data for recipient transaction record.
     *
     * @param  array  $validated
     *
     * @return $validated
     */
    public function alternateSourceRecord($validated)
    {
        $validated['type'] = 'Credit';
        $validated['user_id'] = $validated['recipient_id'];
        $validated['recipient_id'] = auth()->id();
        $validated['currency_id'] = $validated['target_currency_id'];
        $validated['sign'] = '+';

        return $validated;
    }
}
