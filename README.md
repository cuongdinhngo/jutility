# Laravel Japanese Utility

This package provides a convenient way to retrieve Japanese Utility such as Japanese Postal Code, Japanese Localization, CSV

## Installation

1-Install `cuongnd88/jutility` using Composer.

```php
$ composer require cuongnd88/jutility
```

2-You can modify the configuration by copying it to your local config directory:

```php
php artisan vendor:publish --provider="Cuongnd88\Jutility\JutilityServiceProvider"
```

You select the utility by adding `--tag` option:

```php
php artisan vendor:publish --provider="Cuongnd88\Jutility\JutilityServiceProvider" --tag=public
```

There are 3 options:

_`--tag=public` is to publish the JPostal Utility via javascript._

_`--tag=config` is to publish the JPostal Utility via php/laravel._

_`--tag=lang` is to publish the Japanese Localization Utility._


## Sample Usage

### JPostal Utility via Javascript

With the JPostal utility, you can achieve Japanese postal data by postal code. You just need implementing like below 

`resources/views/user/jpostal.blade.php`

```php
. . . .

    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Post code') }}</label>

        <div class="col-md-6">
            <input id="zip" type="text" class="form-control" name="email" value="" onkeyup="JPostal.capture('#zip', ['#info'])">
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Info') }}</label>

        <div class="col-md-6">
            <input id="info" type="text" class="form-control" name="info">
        </div>
    </div>

. . . .
<script type="text/javascript" src="{{ asset('js/jpostal/jpostal.js') }}"></script>
<script type="text/javascript">
    JPostal.init();
</script>
```

`JPostal.capture(zip, response)`:

_`zip` : is a string value that you can assign a value contains id or class sign in identifing zip code. For example: `.zip` or `#zip`._

_`response` is a array or function that you get the data (prefecture, city, area and street). If the array only has one item, it resturns data with comma sign. The array has 4 elements, so it returns seperated data corresponding to prefecture, city, area and street. If the resposne is a function, it will callback ._

_`MEMO` you can use id and class signs for zip and response parameters. You can enter both postal code formats (NNN-NNNN or NNNNNNN)._

```php
	<div class="col-md-6">
	    <input id="zip" type="text" class="form-control" name="email" value="" onkeyup="JPostal.capture('#zip', ['.prefecture', '.city', '.area', '.street'])">
	</div>
```

```php
<script type="text/javascript">
    JPostal.init();

    $( "#zip" ).keyup(function() {
        JPostal.capture('#zip', function(data){
            console.log(data);
        });
    });
</script>
```

The JPostal provides functions to select a city correspond to a prefecture

_`JPostal.innerPrefecturesHtml(callback)` ._

_`JPostal.nnerCityHtmlByPref(prefTag, callback)` ._


```php
. . . .
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">{{ __('Prefecture') }}</label>

        <div class="col-md-6">
            <select class="form-control selectPrefecture" id="selectPrefecture">
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">{{ __('City') }}</label>

        <div class="col-md-6">
            <select class="form-control selectCity" id="selectCity">
            </select>
        </div>
    </div>
. . . .

<script type="text/javascript" src="{{ asset('js/jpostal/jpostal.js') }}"></script>
<script type="text/javascript">
    JPostal.init();

    JPostal.innerPrefecturesHtml(function(prefectures){
        let selectTag = '<option value="">Prefecture</option>';
        for (const [key, value] of Object.entries(prefectures)) {
            selectTag += `<option value="${key}">${value}</option>`;
        }
        $('#selectPrefecture').append(selectTag);
    });

    $("#selectPrefecture").change(function(){
        JPostal.innerCityHtmlByPref('#selectPrefecture', function(cities){
            let selectTag = '<option value="">City</option>';
            for (const item in cities) {
                const {id, name} = cities[item];
                selectTag += `<option value="${id}">${name}</option>`;
            }
            $('#selectCity').append(selectTag);
        });
    });
</script>
```

### JPostal Utility via PHP/Laravel

There are several functions to assist you get Japanese postal code:

_`jpostal_pref($code = null)`: Get Japanese prefectures by code ._

```php
dump(jpostal_pref(47));
```

_`jpostal_pref_city($prefCode, $city = null)`: Get Japanese city by prefecture code ._

```php
dump(jpostal_pref_city(47));
dump(jpostal_pref_city(1, '01101));
```

_`jpostal_code($code)`: Get Japanese postal data by code ._

```php
    dump(jpostal_code('1200000'));
    dump(jpostal_code('120-0000'));
```

_`jlang($key)`: Use translation strings as keys are stored as JSON files in the resources/lang/{$currentLocale}/ directory ._

```php
    dump(jlang('Add Team Member'));
```

### Japanese Localization Utility

The `cuongnd88/jutility` package provides a convenient way to retrieve strings in Japanese languages. The default language for your application is stored in the `config/app.php` configuration file. You may modify this value to suit the needs of your application.

```php
. . . .
    'locale' => 'ja',
. . . .
```

Language strings are stored in files within the resources/lang directory.

```php
/resources
    /lang
        /en
            messages.php
        /ja
            messages.php
```

### CSV

The CSV utility support to read, validate and get the CSV file. You have to set the valitor in `config/csv.php`. Please refer to the defaut:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | UTF-8 Bom
    |--------------------------------------------------------------------------
    |
    | The UTF-8 BOM is a sequence of bytes at the start of a text stream (0xEF, 0xBB, 0xBF)
    | that allows the reader to more reliably guess a file as being encoded in UTF-8.
    | Suitable for exporting Japanese data
    |
    */
    'utf-8-bom' => false,

    /*
    |--------------------------------------------------------------------------
    | Validator Support
    |--------------------------------------------------------------------------
    |
    | This is a sample defines how to validate CSV data:
    | - `user.header` is to identify the format of CSV file, that compare the standard header to the CSV header.
    | The "Invalid Header" message of Exception is threw if there is an error
    |
    | - `user.validator` is based on Laravel Validator. If you have multiple user tables or models you may configure multiple
    |       + `user.validator.rules`: set the Laravel validation rules
    |       + `user.validator.messages`: customize the Laravel default error messages
    |       + `user.validator.attributes`: customize the validation attributes
    */
    'user' => [
        'header' => [
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'email' => 'Email',
        ],
        'validator' => [
            'rules' => [
                'fname' => 'required',
                'lname' => 'required',
                'email' => 'required|email',
            ],
            'messages' => [],
            'attributes' => [],
        ],
    ],
];
```

The `CSV` is a facade that provides access to an object from the container. You just need to import the `CSV` facade near the top of the file.

```php
. . . .
use Cuongnd88\Jutility\Facades\CSV;

class UserController extends Controller
{
    . . . .
    public function postCSV(Request $request)
    {
        $csv = CSV::read(
                    $request->csv,
                    config('csv.user.header'),
                    config('csv.user.validator')
                )->filter();
        dump($csv);
    }
}
. . . .
```

_`read($file, array $standardHeader = [], $validatorConfig = null)`: read CSV file, return CSV object ._

_`filter()`: filter CSV data, return an array `['validated' => [...], 'error' => [...]]`._


_`get()`: get CSV data (including validated and error data) except CSV header line, return an array._

_`validatorErrors()`: get validated errors, return an array ._


```php
    public function postCSV(Request $request)
    {
        $csv = CSV::read(
                    $request->csv,
                    config('csv.user.header'),
                    config('csv.user.validator')
                );
        $data = $csv->get();
        dump($data);
        $errorList = $csv->validatorErrors();
        dump($errorList);
    }
```

_`MEMO`: the CSV returns an array data (or error list), the index array is line number of CSV file._


_`save(string $fileName, array $data, $header = null)`: export data to CSV file ._


```php
    public function downloadCSV()
    {
        $data = User::all()->toArray();
        $header = ['ID','Fullname','Email','Mobile number', 'Email verified data time', 'Created date time', 'Updated date time'];
        CSV::save('user-data', $data, $header);
    }
```

## Demo

This is demo soure code.

[JPostal Utility](https://github.com/cuongnd88/lara-colab/blob/master/alpha/resources/views/user/jpostal.blade.php)

[CSV Utility](https://github.com/cuongnd88/lara-colab/blob/master/alpha/resources/views/user/jpostal.blade.php)

