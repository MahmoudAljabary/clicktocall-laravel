<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function () {
    return View::make('home');
});

// POST URL to handle form submission and make outbound call
Route::post('/call', function () {
    // Get form input
    $number = Input::get('phoneNumber');

    // Set URL for outbound call - this should be your public server URL
    $host = parse_url(Request::url(), PHP_URL_HOST);

    // Create authenticated REST client using account credentials in
    // <project root dir>/.env.php
    $client = new Twilio\Rest\Client(
        getenv('TWILIO_ACCOUNT_SID'),
        getenv('TWILIO_AUTH_TOKEN')
    );

    try {
        $client->calls->create(
            $number, // The visitor's phone number
            getenv('TWILIO_NUMBER'), // A Twilio number in your account
            array(
                "url" => "http://$host/outbound"
            )
        );
    } catch (Exception $e) {
        // Failed calls will throw
        return $e;
    }

    // return a JSON response
    return array('message' => 'Call incoming!');
});

// POST URL to handle form submission and make outbound call
Route::post('/outbound', function () {
    // A message for Twilio's TTS engine to repeat
    $sayMessage = 'Thanks for contacting our sales department. If this were a 
        real click to call application, we would redirect your call to our 
        sales team right now using the Dial tag.';

    $twiml = new \Twilio\Twiml();
    $twiml->say($sayMessage, array('voice' => 'alice'));

    $response = Response::make($twiml, 200);
    $response->header('Content-Type', 'text/xml');
    return $response;
});
