<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Currency;
use App\Models\Transaction;
use App\Traits\ExchangeRate;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ExchangeRate;

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate user request
        (array) $validated = $this->validateRequest();

        $amount = Transaction::removeComma($validated['source_amount']);
        $currency = Currency::where('id', $validated['source_currency_id'])->firstOrFail();

        if($this->canMakePayment($currency['code'], $amount)){
            return back()->with('error', 'Oops! Your '.$currency['name'].' account balance is insufficient to complete this transaction.');
        }

        // Calculate the target amount to be sent to recipient
        $validated = $this->calculation($validated, (float)$amount);

        try{
            // Record first transaction
            if(Transaction::firstEntryRecord($validated, $amount))
            {
                $amount = $validated['targetAmount'];
                $validated['type'] = 'Credit';
            }

            // Record second transaction and redirect back to home
            return (Transaction::secondEntryRecord($validated, $amount))
            ? redirect()->route('home')->with('success', 'Your transaction was successful')
            : back()->with('error', 'Sorry! An error occured while make transfer');
        }catch (\Exception $ex){
            //Record double-entry failed transactions
            Transaction::failedTransaction($validated, $amount, 'Debit', auth()->id(), $validated['recipient_id']);
            Transaction::failedTransaction($validated, $validated['targetAmount'], 'Credit', $validated['recipient_id'], auth()->id());

            // Redirect back to home
            return redirect()->route('home')->with('success', 'Sorry! An error occured while make transaction');
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

            // Source amount
            if($filters['source_currency_id'] == 1)
            {
                if((float)$filters['source_amount'] > (float)auth()->user()->latestCurrencyBalance->EUR){
                    $sourceAmount = auth()->user()->latestCurrencyBalance->EUR;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }else if($filters['source_currency_id'] == 2)
            {
                if((float)$filters['source_amount'] > (float)auth()->user()->latestCurrencyBalance->NGN){
                $sourceAmount = auth()->user()->latestCurrencyBalance->NGN;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }else{
                if((float)$filters['source_amount'] > (float)auth()->user()->latestCurrencyBalance->USD){
                    $sourceAmount = auth()->user()->latestCurrencyBalance->USD;
                }else{
                    $sourceAmount =  $filters['source_amount'];
                }
            }
            // User source currency
            $sourceCurrency = Currency::where('id', $filters['source_currency_id'])->firstOrFail();

            // Recipient target currency
            $targetCurrency = Currency::where('id', $filters['target_currency_id'])->firstOrFail();

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

        return view($view, [
            'recipients'                =>  \App\Models\User::where([['role_id', 2], ['id', '!=', auth()->id()]])->orderBy('name')->get(),
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
     * Get rate.
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
        //Use default source currency($) as target currency
        $currency = Charge::where('source_currency_id', $validated['source_currency_id'])
        ->where('target_currency_id', $validated['target_currency_id'])
        ->firstOrFail();

        // Merge new variables into validated array request
        $validated['user_id'] = auth()->id();

        $validated['recipient_id'] = \App\Models\User::where('uuid', $validated['recipient_uuid'])->firstOrFail()->id;

        $validated['variableFee'] = $this->getVariableFee($currency['variable_percentage'], $amount);

        $validated['rate'] = $this->getRate($currency['rate'], $currency['sourceCurrency']['code'], $currency['targetCurrency']['code'], $amount);

        $validated['fixedFee'] = (float) $currency['fixed_fee'];

        $validated['transferFee'] = $validated['variableFee'] + $validated['fixedFee'];

        $validated['amountToConvert'] = $amount - $validated['transferFee'];

        // Target amount after conversion
        $validated['targetAmount'] = $validated['amountToConvert'] * $validated['rate'];

        $validated['type'] = 'Debit';

        return $validated;
    }

    /**
     * Validate user input fields
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
     * Present on keyup or on changce of source amount and currency.
     *
     * @param  float  $source_amount
     * @param  int  $source_currency_id
     * @param  int  $target_currency_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function currencyBalance(Request $request)
    {
        if ($request->ajax()) {
            (array) $filters = $request->only('source_currency_id');

            // Get currency details
            $sourceCurrency = Currency::findOrFail($filters['source_currency_id']);

            // Source amount
            if($filters['source_currency_id'] == 1)
            {
                $sourceAmount = auth()->user()->latestCurrencyBalance->EUR;
            }else if($filters['source_currency_id'] == 2)
            {
                $sourceAmount = auth()->user()->latestCurrencyBalance->NGN;
            }else{
                $sourceAmount = auth()->user()->latestCurrencyBalance->USD;
            }

            return [
                'sourceCurrency'            => $sourceCurrency,
                'sourceCurrencyBalance'     => $sourceAmount,
            ];
        }
    }
}
