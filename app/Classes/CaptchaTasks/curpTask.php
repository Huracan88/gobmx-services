<?php
namespace App\Classes\CaptchaTasks;


use Exception;
use \Unirest\Request as UniRequest;
use App\Classes\Anticaptcha\RecaptchaV2Proxyless;
use App\Classes\Anticaptcha\ImageToText;

class curpTask
{
    private  $firstUrl = "";

    public function __construct()
    {
        $this->firstUrl = 'https://www.gob.mx/curp/';
    }

    public function solveCaptcha(){

        $api = new RecaptchaV2Proxyless();
        $api->setKey(config('services.anticaptcha.key'));

        $api->setWebsiteURL($this->firstUrl);
        $api->setWebsiteKey("6LdJssgUAAAAAKkVr-Aj-xP5QQzclPeGZmhRwXeY");

        if (!$api->createTask()) {
            throw new Exception("CURP-BOT: API v2 send failed - ".$api->getErrorMessage(),500);
        }

        $taskId = $api->getTaskId();

        if (!$api->waitForResult()) {
            throw new Exception("could not solve captcha: ".$api->getErrorMessage(), 500);
        } else {
            $recaptchaToken = $api->getTaskSolution();
        }

        return $recaptchaToken;
    }


}
