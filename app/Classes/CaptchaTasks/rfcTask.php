<?php
namespace App\Classes\CaptchaTasks;

use Exception;
use \Unirest\Request as UniRequest;
use App\Classes\Anticaptcha\ImageToText;

class rfcTask
{

    private  $cookieJar = "";
    private  $firstUrl = "";
    private  $captchaUrl = "";

    private $viewState = "";

    private $token = "";

    private $error_sat;


    public function __construct()
    {

        $this->cookieJar = storage_path("app/cookie-jars/cookie_bot_rfc.txt");
        $this->firstUrl = 'https://agsc.siat.sat.gob.mx/PTSC/ValidaRFC/index.jsf';
        $this->captchaUrl = 'https://agsc.siat.sat.gob.mx/PTSC/ValidaRFC/captchaReload';
        $this->validateCaptchaUrl = 'https://agsc.siat.sat.gob.mx/PTSC/ValidaRFC/index.jsf';
        $this->getRfcInfoUrl = 'https://agsc.siat.sat.gob.mx/PTSC/ValidaRFC/index.jsf';
        $this->token = rand(1111,9999);
        $this->captchaImage = storage_path("app/captcha-images/captcha_sat_". $this->token .".png");

    }

    private function getViewState(){
        return $this->viewState;
    }

    private function getCaptchaPath(){
        return $this->captchaImage;
    }

    /**
     * @return mixed
     */
    public function getErrorSat()
    {
        return $this->error_sat;
    }

    private function getSessionId(){
        $data = file_get_contents($this->cookieJar);
        $cookies_a = $this->extractCookies($data);
        return $cookies_a[0]['value'];
    }


    /**
     * @param $rfc
     * @return bool
     */
    public function validate($rfc){

        $this->getCaptcha();
        $captchaText = $this->solveCaptcha();
        return $this->getInfo($rfc, $captchaText);

    }

    private function solveCaptcha(){

        $api = new ImageToText();
        $api->setKey(config('services.anticaptcha.key'));

        $api->setFile($this->getCaptchaPath());

        if (!$api->createTask()) {
            throw new Exception("RFC-BOT: API v2 send failed - ".$api->getErrorMessage(),500);
        }

        $taskId = $api->getTaskId();

        if (!$api->waitForResult()) {
            throw new Exception("could not solve captcha: ".$api->getErrorMessage(), 500);
        } else {
            $captchaText    =   $api->getTaskSolution();
        }

        return $captchaText;
    }

    /**
     * Obtiene la primera imagen del captcha
     * @return bool
     */
    private function getCaptcha()
    {

        if(file_exists($this->cookieJar)){
            unlink($this->cookieJar);
        }

        UniRequest::verifyPeer(false);
        UniRequest::cookieFile($this->cookieJar);

        $ConCurp = UniRequest::get($this->firstUrl);

        $dom = new \DOMDocument();

        @$dom->loadHTML($ConCurp->raw_body);
        $xpath = new \DOMXPath($dom);


        $ViewState =  $xpath->query("//*[@id='javax.faces.ViewState']");
        $ViewState =    trim(utf8_decode($ViewState->item(0)->getAttribute('value')));
        $this->viewState = $ViewState;

        $Capcha = UniRequest::post($this->captchaUrl);

        $data = base64_decode($Capcha->raw_body);

        file_put_contents($this->captchaImage, $data);

        return true;

    }


    private function getInfo($rfc, $captcha_text){

        $headers = [
            'Accept' => 'application/xml, text/xml, */*; q=0.01'
            ];
        $data = array(
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'formMain:j_idt57',
            'javax.faces.partial.execute' => '@all',
            'javax.faces.partial.render' => 'formMain',
            'formMain:j_idt57'=> 'formMain:j_idt57',
            'formMain'=> 'formMain',
            'formMain:captchaInput' => $captcha_text,
            'javax.faces.ViewState' => $this->getViewState()
        );

        $session_id = $this->getSessionId();

        $body = UniRequest\Body::Form($data);
        $Request = UniRequest::post($this->validateCaptchaUrl.';jsessionid='.$session_id, $headers, $body);


        $headers = array('Accept' => 'application/xml, text/xml, */*; q=0.01');
        $data = array(
            'formMain'=> 'formMain',
            'formMain:valRFC'=> $rfc,
            'formMain:consulta' => '',
            'javax.faces.ViewState' => $this->getViewState()
        );


        $body = UniRequest\Body::Form($data);
        $Request = UniRequest::post($this->validateCaptchaUrl,$headers, $body);

        $dom = new \DOMDocument();
        @$dom->loadHTML($Request->raw_body);

        $xpath = new \DOMXPath($dom);

        $message = $xpath->query("//*[@class='ui-messages-info-summary']");
        if($message->length == 1 && $message->item(0)->nodeValue == 'RFC válido, y susceptible de recibir facturas')
        {
            return true;
        }

        if($message->length == 1 )
        {
            $this->error_sat = 'SAT: ' . $message->item(0)->nodeValue;
            return false;
        }

        $error = $xpath->query("//*[@class='ui-messages-error-summary']");
        if($error->length == 1 )
        {
            $this->error_sat = $error->item(0)->nodeValue;
            return false;
        }

//        echo $Request->body;

        throw new \Exception('Error desconocido. No se pudo validar contra el SAT',500);
    }


    /**
     * Extract any cookies found from the cookie file. This function expects to get
     * a string containing the contents of the cookie file which it will then
     * attempt to extract and return any cookies found within.
     *
     * @param string $string The contents of the cookie file.
     *
     * @return array The array of cookies as extracted from the string.
     *
     */
    private function extractCookies($string) {
        $cookies = array();

        $lines = explode("\n", $string);

        // iterate over lines
        foreach ($lines as $line) {

            // we only care for valid cookie def lines
            if (isset($line[0]) && substr_count($line, "\t") == 6) {

                // get tokens in an array
                $tokens = explode("\t", $line);

                // trim the tokens
                $tokens = array_map('trim', $tokens);

                $cookie = array();

                // Extract the data
                $cookie['domain'] = $tokens[0];
                $cookie['flag'] = $tokens[1];
                $cookie['path'] = $tokens[2];
                $cookie['secure'] = $tokens[3];

                // Convert date to a readable format
                $cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);

                $cookie['name'] = $tokens[5];
                $cookie['value'] = $tokens[6];

                // Record the cookie.
                $cookies[] = $cookie;
            }
        }

        return $cookies;
    }


}
