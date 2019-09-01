<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = null;

        if( !function_exists('apache_request_headers') ) {
          ///
          function apache_request_headers() {
            $arh = array();
            $rx_http = '/\AHTTP_/';
            foreach($_SERVER as $key => $val) {
              if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                  foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                  $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
              }
            }
            return( $arh );
          }
        }

        $requestHeaders = apache_request_headers();

        $authorizationHeader = null;
        if(isset($requestHeaders['AUTHORIZATION']))
          $authorizationHeader = $requestHeaders['AUTHORIZATION'];
        else
        if(isset($requestHeaders['Authorization']))
          $authorizationHeader = $requestHeaders['Authorization'];
        else
        if(isset($requestHeaders['authorization']))
          $authorizationHeader = $requestHeaders['authorization'];

        if (isset($authorizationHeader))
          $token = str_replace('Bearer ', '', $authorizationHeader);

        if(!$token)
          $token = $request->get('token');

        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Token not provided.'
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'error' => 'Provided token is expired.'
            ], 400);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error while decoding token.'
            ], 400);
        }
        $user = User::find($credentials->sub);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;

        return $next($request);
    }
}
