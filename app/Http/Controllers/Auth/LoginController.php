<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Brian2694\Toastr\Facades\Toastr;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'locked',
            'unlock'
        ]);
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $email    = $request->email;
        $password = $request->password;

        if (Auth::attempt(['email'=>$email,'password'=>$password,'status'=>'Active'])) {
            Toastr::success('Login successfully :)','Success');

            //-------- get token BE API
            $apiUrl = config('api.be_api_url') ;
            $apiUrlLogin =  $apiUrl ."login";
            $client = new Client();
    
            $headersLogin = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',            
            ];       
    
            try {
    
                $dataLogin = [
                    'email' =>  config('api.be_username'),
                    'password' =>  config('api.be_password') ,
                ];
    
                $respLogin = $client->post($apiUrlLogin, [
                    'headers' => $headersLogin,
                    'json' => $dataLogin,
                ]);
    
                $respBody = $respLogin->getBody()->getContents();
                $respBodyArray = json_decode($respBody,true);    
                $token  = $respBodyArray['authorisation']['token'];
                //session()->put('sess_token', $token);
                session(['sess_token' => $token]);




            return redirect()->intended('home');
        } elseif (Auth::attempt(['email'=>$email,'password'=>$password,'status'=> null])) {
            Toastr::success('Login successfully :)','Success');
            return redirect()->intended('home');
        } else {
            Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
            return redirect('login');
        }
    }

    public function logout()
    {
        Auth::logout();
        Toastr::success('Logout successfully :)','Success');
        return redirect('login');
    }

}
