<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/vote', function(Request $request) {
    $topics = ['Everything', 'Test 1', 'Test 2', 'Etc.'];

    if (!$request->has('vote') || !in_array($request->input('vote'), $topics)) {
        return response()->json([
            'message' => 'Invalid vote'
        ], 400);
    }

    event(new App\Events\Vote([
        "vote" => $request->input('vote')
    ]));

    return response()->json([
        'message' => 'Voted successfully'
    ]);
});
