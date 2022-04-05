<?php
namespace App\Classes\CaptchaTasks;

use Exception;
use App\Classes\Anticaptcha\ImageToText;
use Storage;

class cfdiTask
{

    private  $firstUrl = "";

    public function __construct($captchaUrl)
    {
        $this->firstUrl = 'https://verificacfdi.facturaelectronica.sat.gob.mx/'.$captchaUrl;
    }

    public function solveCaptcha(){

        $api = new ImageToText();
        $api->setKey(config('services.anticaptcha.key'));

        $url = $this->firstUrl;
        $contents = file_get_contents($url);

        $name = uniqid() . '.jfif';
        Storage::disk('local')->put($name, $contents);

        $path = storage_path('app/'.$name);

        $api->setFile($path);

        if (!$api->createTask()) {
            throw new Exception("CFDI-BOT: API v2 send failed - ".$api->getErrorMessage(),500);
        }

        $taskId = $api->getTaskId();

        if (!$api->waitForResult()) {
            throw new Exception("could not solve captcha: ".$api->getErrorMessage(), 500);
        } else {
            $captchaText    =   $api->getTaskSolution();
        }

        unlink($path);

        return $captchaText;
    }

}
