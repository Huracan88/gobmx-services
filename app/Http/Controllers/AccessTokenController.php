<?php

namespace App\Http\Controllers;

use App\Models\User;
use Nesk\Rialto\Exceptions\Node;

use App\Classes\CaptchaTasks\curpTask;
use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;


class AccessTokenController extends Controller
{

    public function getNewToken(User $user){

        $user->tokens()->delete();

        $token = $user->createToken('access-token');

        return ['token' => $token->plainTextToken];
    }


}
