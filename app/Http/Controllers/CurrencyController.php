<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * Get the currencies from cache.
     * The values will expire on every midnight. After midnight, the first request will get and store the latest values from exchangeratesapi.io.
     * The currency rates are based on the European Central Bank.
     */
    public static function currencyRates(): array
    {
        if (config('app.exchange_rates_access_key')){
            return Cache::remember('currencies', Carbon::tomorrow(), function () {
                $ch = curl_init('http://api.exchangeratesapi.io/latest?access_key=' . config('app.exchange_rates_access_key'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                curl_close($ch);
    
                $result = json_decode($json, true);
                Log::info('Currencies refreshed', [$result]);
                $result['rates']['EUR'] = 1;
                $result['rates']['CML'] = $result['rates']['HUF']; //Camel currency (1 CML = 1 HUF)
                return $result;
            });
        } else {
            return [
                'base' => 'EUR',
                'rates' => [
                    'EUR' => 1,
                    'HUF' => 400,
                    'DKK' => 10
                ]
            ];
        }
        
    }

    private static function getBaseCurrency(): string
    {
        return CurrencyController::currencyRates()['base'];
    }

    public static function currencyList(): array
    {
        return array_keys(CurrencyController::currencyRates()['rates']);
    }

    public static function exchangeCurrency($from_currency, $to_currency, float $amount): float
    {
        $rates = CurrencyController::currencyRates()['rates'];
        if ($from_currency == $to_currency) {
            return $amount;
        } else {
            //convert to base currency
            $in_base = $amount
                / (($from_currency == CurrencyController::getBaseCurrency())
                    ? 1
                    : ($rates[$from_currency]  ?? abort(500, "Server Error. Invalid currency: " . $from_currency)));
            //convert to result currency
            return $in_base
                * (($to_currency == CurrencyController::getBaseCurrency())
                    ? 1
                    : ($rates[$to_currency] ?? abort(500, "Server Error. Invalid currency: " . $to_currency)));
        }
    }
}
