<?php

namespace App\Http\Controllers;

use App\Classes\CaptchaTasks\cfdiTask;
use Nesk\Rialto\Exceptions\Node;

use App\Classes\CaptchaTasks\curpTask;
use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;


class CfdiValidationController extends Controller
{


    public function getCfdiValidation(Request $request){

        $data = $this->validate($request, [
            'uuid' => 'required',
            'emisor' => 'required',
            'receptor' => 'required',
        ]);


        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();

        $page->setViewport([
            'width'  => 1024,
            'height' => 860,
            'deviceScaleFactor' => 1,
        ]);

        $homePage = 'https://verificacfdi.facturaelectronica.sat.gob.mx/';

        $page->goto($homePage);

        $setValueFunction = JsFunction::createWithParameters(['el','setText'])
            ->body("el.value = setText");

        $getInnerTextFunction = JsFunction::createWithParameters(['el'])
            ->body(" return el.innerText");

        $getAttributeFunction = JsFunction::createWithParameters(['el','attribute'])
            ->body(" return el.getAttribute(attribute) ");

        /////////////////////////////

        $captchaSelector = '#ctl00_MainContent_ImgCaptcha';
        $captchaURL = $page->querySelectorEval($captchaSelector, $getAttributeFunction, 'src');
        $task = new cfdiTask($captchaURL);
        $captchaText = $task->solveCaptcha();


        //////////////////////////////

        $page->querySelectorEval('#ctl00_MainContent_TxtUUID',$setValueFunction,$data['uuid']);
        $page->querySelectorEval('#ctl00_MainContent_TxtRfcEmisor',$setValueFunction,$data['emisor']);
        $page->querySelectorEval('#ctl00_MainContent_TxtRfcReceptor',$setValueFunction,$data['receptor']);
        $page->querySelectorEval('#ctl00_MainContent_TxtCaptchaNumbers',$setValueFunction,$captchaText);

        $page->click('#ctl00_MainContent_BtnBusqueda');

        try{

            $titleSelector = '//*[@id="ctl00_MainContent_PnlResultados"]';
            $page->tryCatch->waitForXPath($titleSelector,['timeout'=>2500]);

            $page->evaluate(JsFunction::createWithBody("
                window.scrollTo(0, 610)
            "));

//
//            $page->scrollingElement->scrollTo('#ctl00_MainContent_LblRfcEmisor');

            $screenshot = 'cfdival_' .  uniqid() .'.png';
            $path = storage_path('app/puppeter-screenshots/'.$screenshot);
            $page->screenshot( ['path' => $path ]);
            $payload = base64_encode(file_get_contents($path));
//            $pdf = 'pdf_'.date('YmdHis').'.pdf';
//            $page->pdf( [ 'format'=>'letter', 'path' => storage_path('app/puppeter-screenshots/'.$pdf) ]);

            $data = [
                'code'=>'200',
                'message'=>'Validación encontrada',
                'payload'=>$payload,
            ];
            $browser->close();
            return \Response::json($data)->setStatusCode(200);

        }catch(Node\Exception $e){
        }

        $screenshot = 'sc_'.date('YmdHis').'.png';
        $page->screenshot( ['path' => storage_path('app/puppeter-screenshots/'.$screenshot) ]);

        $browser->close();
        $data = [
            'code'=>'504',
            'message'=>'No se pudo resolver la validación del CFDI.',
        ];
        return \Response::json($data)->setStatusCode(200);

    }

}
