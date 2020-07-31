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
    public static function currencyRates()
    {
        return Cache::remember('currencies', Carbon::tomorrow(), function () {
            $ch = curl_init('https://api.exchangeratesapi.io/latest');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($json, true);
            $result['rates']['CML'] = $currencies['rates']['HUF']; //Camel currency (1 CML = 1 HUF)
            return $result;
        });
    }
    public static function currencyList()
    {
        return array_keys(CurrencyController::currencyRates()['rates']);
    }
}
