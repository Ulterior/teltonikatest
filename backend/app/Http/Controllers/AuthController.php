<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user)
    {

        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'roles' => array_map(function ($role) {
                return $role['name'];
            }, $user->roles()->get()->toArray()),
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60*60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function authenticate(User $user)
    {

        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);
        // Find the user by email
        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            // You wil probably have some sort of helpers or whatever
            // to make sure that you have the same response format for
            // differents kind of responses. But let's return the
            // below respose for now.
            return response()->json([
                'error' => 'Email does not exist.'
            ], 400);
        } elseif (1 !== $user->confirmed) {
            return response()->json([
              'error' => 'Your account is in approval process'
            ], 400);
        }
        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }
        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }

    public function forgot(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
         'email' => 'string|min:1|required'
        ]);

        if ($validator->fails()) {
            return response()->json([
            'error' => $validator->errors()->all()
            ], 400);
        }

        $user = User::where('email', $input['email'])->firstOrFail();

        if ($user->hasRole('admin')) {
             return response()->json([
            'error' => 'invalid user'
             ], 400);
        }

        $user->confirmation_code = md5(uniqid(mt_rand(), true));
        $user->confirmed = 0;
        $user->save();

        $content = "We received a request to reset the password associated with this e-mail address. "
          . "If you made this request, please follow the instructions below.<br/>"
          . "Click on the link below to reset your password using our secure server: <br/>"
          .config('app.frontend')."/reset?token=".$user['confirmation_code']."<br/>"
          . "If you did not request to have your password reset you can safely ignore this email. "
          . "Rest assured your customer account is safe.<br/>"
          . "If clicking the link does not seem to work, you can copy and paste the link into your browser's "
          . "address window, or retype it there. Once you have returned to Bassetgold.co.uk, we will give "
          . "instructions for resetting your password.";

        Mail::send(
            'emails.templates.mailtemplates',
            compact('content'),
            function ($message) use (&$input) {
                $message->from('support@teltonika.lt', 'Teltonika Support');
                $message->to($input['email']);
                $message->subject('Your password reset link');
            }
        );

        (new Syslog)->register('user:'.$user->id.' initiated password reset');

        return response()->json([]);
    }

    public function reset(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
           'token' => 'string|min:1|required',
           'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
           'password_confirmation' => 'min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
             'error' => $validator->errors()->all()
            ], 400);
        }

        $user = User::where('confirmation_code', $input['token'])->firstOrFail();

        if ($user->hasRole('admin')) {
            return response()->json([
                'error' => 'invalid user'
            ], 400);
        }

        $user->password =  Hash::make($input['password']);
        $user->confirmation_code = md5(uniqid(mt_rand(), true));
        $user->confirmed = 1;
        $user->save();

        (new \App\Syslog)->register('user:'.$user->id.' changed his password');

        return response()->json([]);
    }

    /**
     * User list
     *
     * @return json
     */
    public function users(Request $request)
    {
        $users = \App\User::whereHas('roles', function ($q) {
            $q->where('roles.name', '=', 'user');
        })->get();

        return response()->json($users);
    }
}
