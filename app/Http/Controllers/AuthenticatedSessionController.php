<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Override;

class AuthenticatedSessionController extends Controller

{
    
    public function create()
    {
        return Socialite::driver('efaas')->enablePKCE()->redirect();
    }

    public function callback(Request $request){
       
            /** @var EfaasUser $efaas_user */
            #dd($request);
            $user = Socialite::driver('efaas')->enablePKCE()->user();
            #dd($user);dd
            
            
            /*$fake_data = $this->getFakeData();
            $efaas_user = (new \Javaabu\EfaasSocialite\EfaasUser)->setRaw($fake_data)->map($fake_data);*/
            
            $access_token = $efaas_user->token;
            
            // find and update the user
            $user = User::findEfaasUserAndUpdate($efaas_user);

            // login
            Auth::guard()->login($user, true);

            $request->session()->regenerate();

            session('efaas_token', $access_token);

            // redirect to home
            return redirect('dashboard');

        
    }
    }

