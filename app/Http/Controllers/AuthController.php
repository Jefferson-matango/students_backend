<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;
 
 
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
 
    public function register() {

        try {
            $validator = Validator::make(request()->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:8',
            ], [
                'name.required' => 'The name is required',
                'email.required' => 'The email is required',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email already exists.',
                'password.required' => 'The password is required',
                'password.confirmed' => 'The password must match the password_confirmation field',
                'password.min' => 'The password must be at least 8 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation Error',
                    'status' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = new User;
            $user->name = request()->input('name');
            $user->email = request()->input('email');
            $user->password = bcrypt(request()->input('password'));
            $user->save();

            return response()->json([
                'message'=> 'Successfully created',
                'status'=> 200,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the user',
                'error' => $e->getMessage()
            ], 500);
        } 
    }


    public function login()
    {

        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'The email is required',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $email = request('email');
        $password = request('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email or password invalid',
                'status' => 401,
            ], 401);
        }
        
        if (!$token = auth('api')->attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'message' => 'Email or password invalid',
                'status' => 401,
            ], 401);
        }

        return response()->json([
            'message' => 'Successfully logged in',
            'status' => 200,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user,
            ]
        ], 200);
    }
 
    public function me()
    {
        try {
            $user = auth('api')->user();

            return response()->json([
                'message' => 'User information retrieved successfully',
                'status' => 200,
                'user' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving user information',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
 
    public function logout()
    {
        try {
         
            auth('api')->logout();

            return response()->json([
                'message' => 'Successfully logged out',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json([
                'message' => 'An error occurred while logging out',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

 
    public function refresh()
    {
        try {

            $newToken = auth()->refresh();
    
            return response()->json([
                'newToken' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json([
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
 
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ]);
    }
}