<?php

namespace App\Http\Controllers;

use Nesk\Rialto\Exceptions\Node;

use App\Classes\CaptchaTasks\curpTask;
use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;


class CurpController extends Controller
{

    public function getCurpInfo($curp){

        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();

        $page->setViewport([
            'width'  => 1024,
            'height' => 860,
            'deviceScaleFactor' => 1,
        ]);

        $homePage = 'https://www.gob.mx/curp/';

        $page->goto($homePage);

        $setValueFunction = JsFunction::createWithParameters(['el','setText'])
            ->body("el.value = setText");

        $getInnerTextFunction = JsFunction::createWithParameters(['el'])
            ->body(" return el.innerText");

        /////////////////////////////

        $curpTask = new curpTask();
        $recaptchaToken = $curpTask->solveCaptcha();

        //////////////////////////////
        $page->querySelectorEval('#g-recaptcha-response',$setValueFunction,$recaptchaToken);
        $page->querySelectorEval('#curpinput', $setValueFunction,$curp);

        $page->click('#searchButton');

        /////////////////////////////////

        try{

            $titleSelector = '//*[@id="ember335"]/section/div/div/div[2]/form/div[2]/div[1]/div/div[1 and contains(., "Datos del solicitante")]';
            $page->tryCatch->waitForXPath($titleSelector,['timeout'=>2500]);

            //take screenshot
//            $screenshot = 'sc_'.date('YmdHis').'.png';
//            $page->screenshot( ['path' => storage_path('app/puppeter-screenshots/'.$screenshot) ]);


            $curpSelector = '#ember335 > section > div > div > div.col-xs-12.col-sm-12.col-md-12.clearfix > form > div.row.clearfix > div.col-md-7 > div > div.panel-body > table > tr:nth-child(1) > td:nth-child(2)';
            $curpVal = $page->querySelectorEval($curpSelector, $getInnerTextFunction);

            $nombreSelector = '#ember335 > section > div > div > div.col-xs-12.col-sm-12.col-md-12.clearfix > form > div.row.clearfix > div.col-md-7 > div > div.panel-body > table > tr:nth-child(2) > td:nth-child(2)';
            $nombreVal = $page->querySelectorEval($nombreSelector, $getInnerTextFunction);

            $pApellidoSelector = '#ember335 > section > div > div > div.col-xs-12.col-sm-12.col-md-12.clearfix > form > div.row.clearfix > div.col-md-7 > div > div.panel-body > table > tr:nth-child(3) > td:nth-child(2)';
            $pApellidoVal = $page->querySelectorEval($pApellidoSelector, $getInnerTextFunction);

            $sApellidoSelector = '#ember335 > section > div > div > div.col-xs-12.col-sm-12.col-md-12.clearfix > form > div.row.clearfix > div.col-md-7 > div > div.panel-body > table > tr:nth-child(4) > td:nth-child(2)';
            $sApellidoVal = $page->querySelectorEval($sApellidoSelector, $getInnerTextFunction);

            $data = [
                'code'=>'200',
                'message'=>'Información encontrada',
                'curp' => $curpVal,
                'nombre' => $nombreVal,
                'primer_apellido' => $pApellidoVal,
                'segundo_apellido' => $sApellidoVal,
            ];
            $browser->close();
            return \Response::json($data)->setStatusCode(200);


        }catch(Node\Exception $e){
        }

        $errorCurpSelector = '//*[@id="warningMenssage" and contains(., "Los datos ingresados no son correctos")]/div';
        $x = $page->tryCatch->querySelectorXPath($errorCurpSelector,['timeout'=>500]);

        if($x){
            $browser->close();
            $data = [
                'code'=>'204',
                'message'=>'Al parecer la CURP ingresada es incorrecta',
            ];
            return \Response::json($data)->setStatusCode(200);
        }

        $browser->close();
        $data = [
            'code'=>'504',
            'message'=>'No se pudo resolver la información de la CURP.',
        ];
        return \Response::json($data)->setStatusCode(200);




    }


}
