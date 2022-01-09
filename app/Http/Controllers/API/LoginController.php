<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * The login process for the api authentication
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Create the validator
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Catch the specific errors if there is any
        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Retrieve the user from the database filtered by email
        $user = User::where('email', $request->email)->first();

        // Check if the password hash matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'password' => ['These credentials do not match our records'],
                ],
            ], 422);
        }

        // Return response token if everything is successful
        return response()->json([
            'token' => $user->createToken('api-token')->plainTextToken,
            'type' => 'bearer',
        ], 200);
    }

}
