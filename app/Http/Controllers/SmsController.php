<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class SmsController extends Controller
{

    /**
     * Show the forms with users phone number details.
     *
     * @return Response
     */
    public function show()
    {
        $users = User::all();
        return view('welcome', compact("users"));
    }
    /**
     * Store a new user phone number.
     *
     * @param  Request  $request
     * @return Response
     */
    public function storeUser(Request $request)
    {
        //run validation on data sent in
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone_number' => 'required|unique:users|numeric',
            'password' => 'required',
        ]);
        $user = new User($request->all());
        $user->save();
        $this->sendMessage('User registration successful!!', $request->phone_number);
        return back()->with(['success' => "{$request->name} your {$request->phone_number} number is registered"]);
    }
    /**
     * Send message to a selected users
     */
    public function sendCustomMessage(Request $request)
    {
        $validatedData = $request->validate([
            'users' => 'required|array',
            'body' => 'required',
        ]);
        $recipients = $validatedData["users"];
        // iterate over the array of recipients and send a  request for each
        foreach ($recipients as $recipient) {
            $this->sendMessage($validatedData["body"], $recipient);
        }
        return back()->with(['success' => "Messages on their way!"]);
    }
    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients Number of recipient
     */
    private function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($recipients, ['from' => $twilio_number, 'body' => $message]);
    }
}
