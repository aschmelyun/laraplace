<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Events\ColorChanged;

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', function () {
        return view('index');
    });

    Route::get('/map', function () {
        $pixels = [];

        for($x=0; $x <= 99; $x++) {
            $rows = [];

            for($y=0; $y <= 99; $y++) {
                $rows[] = "{$x}:{$y}";
            }

            $pixels[] = Redis::mget($rows);
        }

        return $pixels;
    });

    Route::post('/save', function (Request $request) {
        Redis::set($request->key, $request->color . ':' . $request->user()->email);

        ColorChanged::dispatch([
            'key' => $request->key,
            'color' => $request->color . ':' . $request->user()->email
        ]);

        return response()->json(Redis::get($request->key));
    });
});

require __DIR__.'/auth.php';
