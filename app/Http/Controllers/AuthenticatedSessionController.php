<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Two\InvalidStateException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use Exception;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Javaabu\EfaasSocialite\EfaasUser;
use App\Models\User;
use Override;

class AuthenticatedSessionController extends Controller

{
    
    public function create()
    {
        return Socialite::driver('efaas')->enablePKCE()->with(['response_mode' => 'query'])->redirect();
    }

    public function callback(Request $request){
       
            /** @var EfaasUser $efaas_user */
            
            try  {

                $efaas_user = Socialite::driver('efaas')->enablePKCE()->user();

                if ($efaas_user->first_name == null) {
                    session()->put('efaas_id_token', $efaas_user->id_token);
                    return redirect('')->with('error', "This service requires access to certain information from your eFaas account.\n"
                                                        ."Access to one or more required items was not granted.\n"
                                                        ."Please sign in again and provide consent for the information required by this service."
                        );
                }

                if($efaas_user->verified == false){
                    session()->put('efaas_id_token', $efaas_user->id_token);
                    return redirect('')->with('error', "Your eFaas account is not verified. A verified eFaas account is required to access this service.\n"
                                                        ."Please try again after verifying your eFaas account.");
                }

                $id_token = $efaas_user->id_token;
                $sid = $efaas_user->sid;
                $access_token = $efaas_user->token;
                
                // find and update the user
                $user = User::findEfaasUserAndUpdate($efaas_user);

                // login -- note this migrates the session id
                Auth::guard()->login($user, true);

                // Repetetive
                #$request->session()->regenerate();

                session()->put('efaas_id_token', $id_token);
                session()->put('efaas_sid', $sid);
                session()->put('efaas_token', $access_token);

                Socialite::driver('efaas')->sessionHandler()->saveSid($sid);

                // redirect to home
                return redirect('dashboard');
            }
            // error handling for when access is denied
            catch(ClientException $e){

                $response = $e->getResponse()->getStatusCode();

               if ($response === 400) {
                   return redirect('')->with('error', "This service requires access to certain information from your eFaas account.\n"
                                                        ."Access to one or more required items was not granted.\n"
                                                        ."Please sign in again and provide consent for the information required by this service.");
            }
            }
            catch(InvalidStateException $e){
                Log::error('eFaas Login Error: '.$e->getMessage());
                return redirect('');
            }
    //         catch (\Exception $e) {
    //         Log::error('eFaas Login Error: '.$e->getMessage());

    //         return redirect()->route('login')->withErrors([
    //             'efaas' => 'eFaas Login Failed: '.$e->getMessage(),
    //         ]);
    //         }

    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $efaas_token = session('efaas_id_token');
        

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($efaas_token) {
            return Socialite::driver('efaas')->logOut($efaas_token, url('/'));
        }

        return redirect('/');
    }

   
    public function handleBackChannelSingleSignOut(Request $request)
    {    
        $sid = Socialite::driver('efaas')->getLogoutSid();
        
        if ($sid) {
            Socialite::driver('efaas')
            ->sessionHandler()
            ->logoutSessions($sid, 'web');
            }
            
            // for back channel logout you must return 200 OK response
            return response()->json([
                'success' => ! empty($sid)
                ]);


}
                
                
}                
              
