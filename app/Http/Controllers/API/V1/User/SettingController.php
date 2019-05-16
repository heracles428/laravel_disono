<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\API\V1\User;

use App\Http\Controllers\API\APIController;
use App\Http\Requests\API\V1\User\AccountSecurity;
use App\Http\Requests\API\V1\User\AccountSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SettingController extends APIController
{
    /**
     * Sync user
     *
     * @return JsonResponse
     */
    public function syncAction()
    {
        return $this->json([
            'profile' => (new User())->single(authId()),
            'setting' => (new Setting())->keyValuePair()
        ]);
    }

    /**
     * Update profile details
     *
     * @param AccountSettings $request
     * @return JsonResponse
     */
    public function settingsUpdateAction(AccountSettings $request)
    {
        $inputs = $request->only([
            'first_name', 'last_name', 'phone', 'gender', 'birthday', 'address', 'country_id', 'city_id'
        ]);
        $inputs['profile_picture'] = $request->file('profile_picture');

        (new User())->clearBoolean();
        (new User())->edit(__me()->id, $inputs);

        return $this->json((new User())->single(__me()->id));
    }

    /**
     * Update profile security
     *
     * @param AccountSecurity $request
     * @return JsonResponse
     */
    public function securityUpdateAction(AccountSecurity $request)
    {
        (new User())->clearBoolean();
        (new User())->edit(__me()->id, $request->only(['email', 'password']));

        return $this->json((new User())->single(__me()->id));
    }
}
