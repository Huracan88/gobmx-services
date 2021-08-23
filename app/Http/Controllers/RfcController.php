<?php

namespace App\Http\Controllers;

use App\Classes\CaptchaTasks\rfcTask;
use Nesk\Rialto\Exceptions\Node;

use App\Classes\CaptchaTasks\curpTask;
use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;


class RfcController extends Controller
{

    public function validateRfc($rfc){

        $task = new rfcTask();

        $result = $task->validate($rfc);

        if($result === false){
            $data = [
                'code'=>'204',
                'message'=>"El RFC $rfc no es válido. ". $task->getErrorSat(),
            ];
            return \Response::json($data)->setStatusCode(200);
        }

        $data = [
            'code'=>'200',
            'message'=>"El RFC $rfc es válido.",
        ];
        return \Response::json($data)->setStatusCode(200);


    }


}
