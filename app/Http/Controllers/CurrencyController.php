<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CurrencyController extends Controller
{
    /**
     * Get the currencies from cache.
     * The values will expire on every midnight. After midnight, the first request will get and store the latest values from exchangeratesapi.io.
     * The currency rates are based on the European Central Bank.
     */
    public static function currencyRates(): array
    {
        return Cache::remember('currencies', Carbon::tomorrow(), function () {
            $ch = curl_init('https://api.exchangeratesapi.io/latest');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($json, true);
            $result['rates']['EUR'] = 1;
            $result['rates']['CML'] = $result['rates']['HUF']; //Camel currency (1 CML = 1 HUF)
            return $result;
        });
    }

    private static function getBaseCurrency(): string
    {
        return CurrencyController::currencyRates()['base'];
    }

    public static function currencyList(): array
    {
        return array_keys(CurrencyController::currencyRates()['rates']);
    }

    public static function exchangeCurrency($from_currency, $to_currency,float $amount): float
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
