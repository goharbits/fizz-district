<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{

    public function login(){
        return view('login');
    }


    public function loginWeb(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password,'role'=>1])) {
            // Authentication passed...
            return redirect()->route('logs');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function googlePay($amount)
    {
        $amount = (int)($amount * 100);
        return view('googlePay',compact('amount'));
    }

    public function googlePayResponse(Request $request){
        Log::info('Google Pay response:', $request->all()); // Logs the entire payload

        if($request->has('cloverToken')){
            // return '/response/success';
            return response()->json(['success' => true, 'message' => 'Payment processed successfully.','url'=>'']);
        }
            // return '/response/failed';
        return response()->json(['error' => true, 'message' => 'Token not created','url'=>'']);
    }

    public function googlePaySuccess($token){
        return 'Google paid Successfully';
    }

    public function googlePayFailed(){
        return 'Google paid Failed';

    }

}
