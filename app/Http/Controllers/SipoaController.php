<?php

namespace App\Http\Controllers;


use Nesk\Rialto\Exceptions\Node;

use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;


class SipoaController extends Controller
{


    public function test(){

        $url = 'http://egresos.sefiplan.qroo.gob.mx/PBR_2022/Web/DistribucionMonetariaMMLComponentes.aspx';
        $url_form = 'http://egresos.sefiplan.qroo.gob.mx/PBR_2022/Web/DistribucionMonetariaMMLComponentes.aspx';

        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();

        $page->setDefaultNavigationTimeout(60000);

        $page->setViewport([
            'width'  => 1024,
            'height' => 860,
            'deviceScaleFactor' => 1,
        ]);

        $page->authenticate(['username'=>'aaguirrec','password'=>'delanzaieHcj']);
        $page->goto($url);

        $page->goto($url_form);
        $page->setDefaultNavigationTimeout(60000);


        /////////////////////////////////////
        $setValueFunction = JsFunction::createWithParameters(['el','setText'])
            ->body("el.value = setText");

        $getInnerTextFunction = JsFunction::createWithParameters(['el'])
            ->body(" return el.innerText");

        $getAttributeFunction = JsFunction::createWithParameters(['el','attribute'])
            ->body(" return el.getAttribute(attribute) ");
        /////////////////////////////

        $page->click('#btnNuevo');

//        $this->takeScreenshot($page);

//        $leyendaXpath = '#FormView1_rntbFeb_text';
//        $page->tryCatch->waitFor($leyendaXpath,['timeout'=>250]);
        $page->waitForTimeout(3000);


        $this->takeScreenshot($page);

        $page->close();

    }









    public function getListadoDocumental(Request $request){

        $data = $this->validate($request, [
            'sentre_user' => 'required',
            'sentre_password' => 'required',
            'action' => 'required|in:concentracion,baja,tramite,historico',
        ]);

        $loginURL = 'http://sentre.secoes.qroo.gob.mx/login.php';
        $closeSessionUrl = 'http://sentre.secoes.qroo.gob.mx/sistema/exit.php';
        $baseURL = $this->getBaseUrl($data['action']);


        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();

        $page->setViewport([
            'width'  => 1024,
            'height' => 860,
            'deviceScaleFactor' => 1,
        ]);

        $page->goto($loginURL);

        /////////////////////////////////////
        $setValueFunction = JsFunction::createWithParameters(['el','setText'])
            ->body("el.value = setText");

        $getInnerTextFunction = JsFunction::createWithParameters(['el'])
            ->body(" return el.innerText");

        $getAttributeFunction = JsFunction::createWithParameters(['el','attribute'])
            ->body(" return el.getAttribute(attribute) ");
        /////////////////////////////


        $page->querySelectorEval('#login',$setValueFunction,$data['sentre_user']);
        $page->querySelectorEval('#password',$setValueFunction,$data['sentre_password']);
        $page->click('#wrap > table > tbody > tr > td > table:nth-child(6) > tbody > tr > td > table > tbody > tr:nth-child(2) > td > form > table > tbody > tr:nth-child(5) > td:nth-child(4) > input');

        try{

            $titleSelector = '//*[@id="wrap"]/table/tbody/tr[5]/td[2]/table/tbody/tr[2]/td/p';
            $page->tryCatch->waitForXPath($titleSelector,['timeout'=>2500]);

            //pagina principal del listado
            $page->goto($baseURL);

//            $this->takeScreenshot($page);

            $thSelector = '#anexo27 > table.formulario > tbody > tr.story-lista';
            $thText = $page->querySelectorEval($thSelector, $getInnerTextFunction);
            $titles = explode("\n\t\n",$thText);

            $NumRecordsSelector = '#wrap > table > tbody > tr:nth-child(5) > td:nth-child(2) > p';
            $numRecords = $page->querySelectorEval($NumRecordsSelector, $getInnerTextFunction);
            $numRecords = str_replace("Registros: ","",trim($numRecords));
            $explodeNR = explode(' de ',$numRecords);
            $numRecords = intval( trim($explodeNR[1]));
            $pages = ceil($numRecords / 20);


            $tableSelector = '#anexo27 > table.formulario';
            $tableText = $page->querySelectorEval($tableSelector, $getInnerTextFunction);

            $lineas = explode("\n\n\n",$tableText);
            $records = [];
            foreach($lineas as $l){
                $campos = explode("\n\t\n",$l);
                if($campos[0] == 'Editar')
                    continue;
                $records[] = $campos;
            }

            if($pages > 1){
                for($i=2;$i<=$pages;$i++){
                    $url = $baseURL. '&orden=&page='.$i;
                    $page->goto($url);

                    $tableSelector = '#anexo27 > table.formulario';
                    $tableText = $page->querySelectorEval($tableSelector, $getInnerTextFunction);
                    $lineas = explode("\n\n\n",$tableText);
                    foreach($lineas as $l){
                        $campos = explode("\n\t\n",$l);
                        if($campos[0] == 'Editar')
                            continue;
                        $records[] = $campos;
                    }
                }
            }

            $page->goto($closeSessionUrl);
            $page->goto($loginURL);
            $logoutSelector = '//*[@id="wrap"]/table/tbody/tr/td/table[2]/tbody/tr/td/table/tbody/tr[1]/td/div';
            $page->tryCatch->waitForXPath($logoutSelector,['timeout'=>1000]);

            //armamos el archivo csv
            $fp = fopen(storage_path('app/sentre/' . $data['sentre_user']. '_'.$data['action'].'_'.date('YmdHis') .'.csv' ), 'w');

           $titles =  array_map(function(&$entry){return $entry = utf8_decode($entry);},$titles);

            fputcsv($fp, $titles);
            foreach ($records as $row){
                $row =  array_map(function(&$entry){return $entry = utf8_decode($entry);},$row);
                fputcsv($fp, $row);
            }
            fclose($fp);
            ///////////////////////////////////

            $browser->close();

            $data = [
                'code'=>'200',
                'message'=>'Exito!',
                'titles'=>$titles,
                'numRecords' => $numRecords,
                'pages'=>$pages,
                'data' => $records
            ];
            return \Response::json($data)->setStatusCode(200);

        }catch(Node\Exception $e){

            $this->takeScreenshot($page);

            $page->goto($closeSessionUrl);
            $page->goto($loginURL);

            $data = [
                'code'=>'500',
                'message'=>'NodeException: '.$e
            ];
            return \Response::json($data)->setStatusCode(200);

        }catch(\Exception $e){
            $this->takeScreenshot($page);

            $page->goto($closeSessionUrl);
            $page->goto($loginURL);
            $data = [
                'code'=>'500',
                'message'=>'BasicException: '.$e
            ];
            return \Response::json($data)->setStatusCode(200);
        }

        $screenshot = 'sc_'.date('YmdHis').'.png';
        $page->screenshot( ['path' => storage_path('app/puppeter-screenshots/'.$screenshot) ]);

        $data = [
            'code'=>'504',
            'message'=>'No se pudo obtener el listado de documentación.',
        ];
        return \Response::json($data)->setStatusCode(200);

    }


    private function getBaseUrl(string $action) {

        if($action == 'concentracion')
            return 'http://sentre.secoes.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27c';

        if($action == 'tramite')
            return 'http://sentre.secoes.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27t';

        if($action == 'baja')
            return 'http://sentre.secoes.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27b';

        if($action == 'historico')
            return 'http://sentre.secoes.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27h';

        return false;

    }



    private function takeScreenshot($page){
        $screenshot = 'sentre_' .  uniqid() .'.png';
        $path = storage_path('app/sipoa/'.$screenshot);
        $page->screenshot( ['path' => $path ]);
    }


}
