<?php
/**
 * Author: Archie, Disono (webmonsph@gmail.com)
 * Website: http://www.webmons.com
 * Copyright 2016 Webmons Development Studio.
 * License: Apache 2.0
 */
namespace App\Http\Controllers\API\V1\Authenticate;

use App\Events\EventSignUp;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\AuthenticationToken;
use App\Models\AuthHistory;
use App\Models\Slug;
use App\Models\SocialAuth;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Create new user
     *
     * @param Requests\API\V1\AuthRegister $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Requests\API\V1\AuthRegister $request)
    {
        $social_id = 0;
        $create = null;
        if ($request->get('social_id')) {
            if ($request->get('social_id') > 0 && is_numeric($request->get('social_id'))) {
                $social_id = (int)$request->get('social_id');
            }
        }

        $user = null;
        if ($social_id) {
            $user = SocialAuth::where('identifier', $social_id)->first();
        }

        if (!$user) {
            $create = $this->_createUser($request, $social_id);

            if ($create) {
                Slug::store([
                    'source_id' => $create->id,
                    'source_type' => 'user',
                    'name' => ($social_id) ? $create->id . time() . str_random() : request_value($request, 'username')
                ]);

                // send email for email verification if not authenticated by social
                if (!$social_id) {
                    event(new EventSignUp([
                        'user' => $create
                    ]));
                }
            } else {
                return failed_json_response('Can not create user.');
            }

            $user_id = $create->id;
        } else {
            $user_id = $user->user_id;
        }

        // save authentication id for social library
        if ($social_id && $create) {
            SocialAuth::insert([
                'user_id' => $create->id,
                'identifier' => $social_id
            ]);
        }

        // get the user details
        $user = User::single($user_id);

        // authentication history
        if ($user && $social_id) {
            AuthHistory::store([
                'user_id' => $user->id,
                'ip' => get_ip_address(),
                'platform' => get_user_agent(),
                'type' => 'login'
            ]);
        }

        // create authentication token
        if ($user) {
            $user = AuthenticationToken::createToken($user);

            if (!$user) {
                return failed_json_response('Failed to create token.');
            }
        }

        return success_json_response($user);
    }

    /**
     * Create new user
     *
     * @param $request
     * @param $social_id
     * @return User
     */
    private function _createUser($request, $social_id) {
        return User::create([
            'first_name' => ucfirst(request_value($request, 'first_name')),
            'last_name' => ucfirst(request_value($request, 'last_name')),

            'phone' => request_value($request, 'phone'),
            'email' => request_value($request, 'email'),
            'password' => request_value($request, 'password', '', true),
            'address' => request_value($request, 'address', ''),

            'role' => 'client',
            'enabled' => 1,
            'email_confirmed' => (($social_id) ? 1 : 0)
        ]);
    }
}
