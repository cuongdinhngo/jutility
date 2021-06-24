<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

if (! function_exists('jpostal_pref')) {
    /**
     * Get Japanese prefectures by code
     *
     * @param mixed $code
     *
     * @return void
     */
    function jpostal_pref($code = null)
    {
        return $code ? config("jpostal.prefectures.{$code}") : config("jpostal.prefectures");
    }
}

if (! function_exists('jpostal_pref_city')) {
    /**
     * Get Japanese city by prefecture code
     *
     * @param [type] $prefCode
     * @param [type] $city
     * @return void
     */
    function jpostal_pref_city($prefCode, $city = null)
    {
        $prefCode = intval($prefCode);
        if (false === in_array($prefCode, range(1,47))) {
            throw new \Exception('jpostal_pref_city: Invalid Prefecture Code');
        }
        $prefecture = config("jpostal.city-{$prefCode}");
        if (is_null($city)) {
            return $prefecture;
        }
        $city = array_filter($prefecture, function ($item) use ($city) {
            return $item['id'] == $city;
        });
        return $city[0]['name'];
    }
}

if (! function_exists('jpostal_code')) {
    /**
     * Get Japanese postal data by code
     *
     * @param mixed $code
     *
     * @return void
     */
    function jpostal_code($code)
    {
        if (str_contains($code, '-')) {
            $tmp = explode('-', $code);
            $zipCode = $tmp[0];
            $zip = implode('', $tmp);
        } else {
            $zip = $code;
            $zipCode = substr($code, 0, 3);
        }
        
        if (strlen($zipCode) < 3 || strlen($zip) < 7 || is_numeric($zipCode) === false) {
            throw new \Exception('jpostal_code: Invalid Postal Code');
        }

        $data = config("jpostal.zip-{$zipCode}.{$zip}");

        return is_null($data) ? $data : collect(['prefecture', 'city', 'area', 'street'])->map(function($item, $index) use ($data){
            if ($item == 'prefecture') {
                return [$item =>  jpostal_pref($data[$index]) ?? null];
            }
            return [$item =>  $data[$index] ?? null];
        })->collapse()->toArray();
    }
}

if (! function_exists('jlang')) {
    /**
     * Use translation strings as keys are stored as JSON files in the resources/lang/{$currentLocale}/ directory
     *
     * @param mixed $key
     *
     * @return void
     */
    function jlang($key)
    {
        try {
            $locale = App::getLocale();
            $path = resource_path("lang/{$locale}/{$locale}.json");
            $content = json_decode(File::get($path), true);
            return $content[$key] ?? $key;
        } catch (\Exception $e) {
            report($e);
            throw new \Exception('jlang: Invalid Key');
        }
    }
}
