<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\SocialProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Socialite;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));
        $this->guard()->login($user);

        //assign defualt role of school admin to new registered admin
        auth()->user()->assignRole('school_admin');
        $user->setupData();

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }


    /**
     * Redirect the user to the {$provider} authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
         return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from {$provider}.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
       

        try{

            $socialuser = Socialite::driver($provider)->user();
            $socialProvider=SocialProvider::where('provider_id',$socialuser->getId());
            if($socialProvider){
                $user=User::firstOrCreate(
                    ['email'=>$socialuser->getEmail()],
                    ['name'=>$socialuser->getName()]);
                $user->socialProviders()->create(
                    ['provider_id'=> $socialuser->getId(), 'provider'=>$provider]);
                        }
                    else {
                         $user=$socialProvider->user;
                         auth()->login($user);
                         return redirect('/');
                                 }

        }
        catch(\Exception $e)
            {
                return redirect('/');
                    }
        
                   

    }


}
