<?php

namespace App\Http\Controllers;


use Nesk\Rialto\Exceptions\Node;

use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use App\Models\SentreUser;
use App\Models\SentreRecord;


class SentreController extends Controller
{


    public function getListadoDocumental(Request $request){
        set_time_limit(0); // Eliminar limite de tiempo para procesos largos

        $data = $this->validate($request, [
            'sentre_user' => 'required',
            'sentre_password' => 'required',
            'action' => 'required|in:concentracion,baja,tramite,historico',
            'visit_details' => 'nullable|boolean',
            'generate_csv' => 'nullable|boolean',
        ]);

//        dd($data);

        $loginURL = 'http://sentre.sabgob.qroo.gob.mx/login.php';
        $closeSessionUrl = 'http://sentre.sabgob.qroo.gob.mx/sistema/exit.php';
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
        $setValueFunction = (new JsFunction)->parameters(['el','setText'])
            ->body("el.value = setText");

        $getInnerTextFunction = (new JsFunction)->parameters(['el'])
            ->body(" return el.innerText");

        $getAttributeFunction = (new JsFunction)->parameters(['el','attribute'])
            ->body(" return el.getAttribute(attribute) ");

        $getEditLinksFunction = (new JsFunction)->parameters(['tableSelector'])
            ->body("
                let table = document.querySelector(tableSelector);
                let results = [];
                if (table) {
                    let rows = table.querySelectorAll('tr.list');
                    rows.forEach(row => {
                        let editLink = row.querySelector('td:first-child a[href*=\"frmanexo27c.php\"]');
                        let checkbox = row.querySelector('td:first-child input[type=\"checkbox\"]');
                        let id = '';
                        if (checkbox) {
                            id = checkbox.value;
                        } else if (editLink) {
                            let url = new URL(editLink.href);
                            id = url.searchParams.get('id_anexo');
                        }
                        results.push({
                            href: editLink ? editLink.href : null,
                            id: id
                        });
                    });
                }
                return results;
            ");

        $getRowsFunction = (new JsFunction)->parameters(['tableSelector'])
            ->body("
                let table = document.querySelector(tableSelector);
                let results = [];
                if (table) {
                    let rows = table.querySelectorAll('tr.list');
                    rows.forEach(row => {
                        let cells = row.querySelectorAll('td');
                        let rowData = [];
                        cells.forEach(cell => {
                            rowData.push(cell.innerText.trim());
                        });
                        results.push(rowData);
                    });
                }
                return results;
            ");
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
            if (isset($titles[0]) && $titles[0] == 'Editar') {
                $titles[0] = 'ID';
            }

            if (isset($data['visit_details']) && $data['visit_details']) {
                $titles[] = 'No. Legajos';
                $titles[] = 'No. Hojas';
                $titles[] = 'Preservación';
                $titles[] = 'Observaciones';
            }

            $NumRecordsSelector = '#wrap > table > tbody > tr:nth-child(5) > td:nth-child(2) > p';
            $numRecords = $page->querySelectorEval($NumRecordsSelector, $getInnerTextFunction);
            $numRecords = str_replace("Registros: ","",trim($numRecords));
            $explodeNR = explode(' de ',$numRecords);
            $numRecords = intval( trim($explodeNR[1]));
            $pages = ceil($numRecords / 20);


            // Guardar en la base de datos
            $sentreUser = SentreUser::firstOrCreate(
                ['username' => $data['sentre_user']],
                ['password' => $data['sentre_password']] // En un caso real, esto debería estar hasheado, pero seguimos el flujo actual
            );

            $tableSelector = '#anexo27 > table.formulario';
            $tableRows = $page->evaluate($getRowsFunction, $tableSelector);
            $tableResults = $page->evaluate($getEditLinksFunction, $tableSelector);

            $records = [];
            $allEditLinks = [];
            foreach($tableRows as $index => $campos){

                if (isset($tableResults[$index])) {
                    $campos[0] = $tableResults[$index]['id'];
                    $allEditLinks[] = $tableResults[$index]['href'];
                } else {
                    $allEditLinks[] = null;
                }

                $record_id = $campos[0] ?? null;
                if ($record_id) {
                    $recordData = [
                        'sentre_user_id' => $sentreUser->id,
                        'type' => $data['action'],
                        'record_id' => $record_id,
                        'expediente' => $campos[1] ?? null,
                        'descripcion' => $campos[2] ?? null,
                        'anio_creacion' => $campos[3] ?? null,
                        'ubicacion_fisica' => $campos[4] ?? null,
                        'no_caja' => $campos[5] ?? null,
                        'fecha_inicio' => $campos[6] ?? null,
                        'fecha_final' => $campos[7] ?? null,
                        'tiempo_conservacion' => $campos[8] ?? null,
                        'fecha_transferencia' => $campos[9] ?? null,
                        'clasificacion' => $campos[10] ?? null,
                        'caracter_documental' => $campos[11] ?? null,
                    ];
                    SentreRecord::updateOrCreate(
                        ['sentre_user_id' => $sentreUser->id, 'record_id' => $record_id, 'type' => $data['action']],
                        $recordData
                    );
                }

                $records[] = $campos;
            }

            if($pages > 1){
                for($i=2;$i<=$pages;$i++){
                    $url = $baseURL. '&orden=&page='.$i;
                    $page->goto($url);

                    $tableSelector = '#anexo27 > table.formulario';
                    $pageRows = $page->evaluate($getRowsFunction, $tableSelector);
                    $pageResults = $page->evaluate($getEditLinksFunction, $tableSelector);

                    foreach($pageRows as $index => $campos){

                        if (isset($pageResults[$index])) {
                            $campos[0] = $pageResults[$index]['id'];
                            $allEditLinks[] = $pageResults[$index]['href'];
                        } else {
                            $allEditLinks[] = null;
                        }

                        $record_id = $campos[0] ?? null;
                        if ($record_id) {
                            $recordData = [
                                'sentre_user_id' => $sentreUser->id,
                                'type' => $data['action'],
                                'record_id' => $record_id,
                                'expediente' => $campos[1] ?? null,
                                'descripcion' => $campos[2] ?? null,
                                'anio_creacion' => $campos[3] ?? null,
                                'ubicacion_fisica' => $campos[4] ?? null,
                                'no_caja' => $campos[5] ?? null,
                                'fecha_inicio' => $campos[6] ?? null,
                                'fecha_final' => $campos[7] ?? null,
                                'tiempo_conservacion' => $campos[8] ?? null,
                                'fecha_transferencia' => $campos[9] ?? null,
                                'clasificacion' => $campos[10] ?? null,
                                'caracter_documental' => $campos[11] ?? null,
                            ];
                            SentreRecord::updateOrCreate(
                                ['sentre_user_id' => $sentreUser->id, 'record_id' => $record_id, 'type' => $data['action']],
                                $recordData
                            );
                        }

                        $records[] = $campos;
                    }
                }
            }

            if (isset($data['visit_details']) && $data['visit_details']) {
                $getDetailInfoFunction = (new JsFunction)->body("
                    let n_legajos = document.querySelector('input[name=\"n_legajos\"]') ? document.querySelector('input[name=\"n_legajos\"]').value : '';
                    let n_hojas = document.querySelector('input[name=\"n_hojas\"]') ? document.querySelector('input[name=\"n_hojas\"]').value : '';
                    let preservacion = document.querySelector('select[name=\"preservacion\"]') ? document.querySelector('select[name=\"preservacion\"]').value : '';
                    let observaciones = document.querySelector('textarea[name=\"observaciones\"]') ? document.querySelector('textarea[name=\"observaciones\"]').value : '';
                    return [n_legajos, n_hojas, preservacion, observaciones];
                ");

                foreach ($records as $index => &$row) {
                    if (isset($allEditLinks[$index]) && $allEditLinks[$index]) {
                        $page->goto($allEditLinks[$index]);
                        $details = $page->evaluate($getDetailInfoFunction);
                        $row = array_merge($row, $details);

                        $record_id = $row[0] ?? null;
                        if ($record_id) {
                            SentreRecord::updateOrCreate(
                                ['sentre_user_id' => $sentreUser->id, 'record_id' => $record_id, 'type' => $data['action']],
                                [
                                    'no_legajos' => $details[0] ?? null,
                                    'no_hojas' => $details[1] ?? null,
                                    'preservacion' => $details[2] ?? null,
                                    'observaciones' => $details[3] ?? null,
                                ]
                            );
                        }
                    } else {
                        $row = array_merge($row, ['', '', '', '']);
                    }
                }
            }

            $page->goto($closeSessionUrl);
            $page->goto($loginURL);
            $logoutSelector = '//*[@id="wrap"]/table/tbody/tr/td/table[2]/tbody/tr/td/table/tbody/tr[1]/td/div';
            $page->tryCatch->waitForXPath($logoutSelector,['timeout'=>1000]);

            //armamos el archivo csv si se solicita
            if (isset($data['generate_csv']) && $data['generate_csv']) {
                $fp = fopen(storage_path('app/sentre/' . $data['sentre_user']. '_'.$data['action'].'_'.date('YmdHis') .'.csv' ), 'w');

                $titles =  array_map(function(&$entry){return $entry = utf8_decode($entry);},$titles);

                fputcsv($fp, $titles);
                foreach ($records as $row){
                    $row =  array_map(function(&$entry){return $entry = utf8_decode($entry);},$row);
                    fputcsv($fp, $row);
                }
                fclose($fp);
            }
            ///////////////////////////////////

            $browser->close();
            $browser = null;

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

            if (isset($browser)) {
                $this->takeScreenshot($page);

                $page->goto($closeSessionUrl);
                $page->goto($loginURL);
                $browser->close();
            }

            $data = [
                'code'=>'500',
                'message'=>'NodeException: '.$e
            ];
            return \Response::json($data)->setStatusCode(200);

        }catch(\Exception $e){
            if (isset($browser)) {
                $this->takeScreenshot($page);

                $page->goto($closeSessionUrl);
                $page->goto($loginURL);
                $browser->close();
            }
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
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27c';

        if($action == 'tramite')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27t';

        if($action == 'baja')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27b';

        if($action == 'historico')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/anexo27.php?tipo_anexo27=anexo27h';

        return false;

    }

    private function getEditFormUrl(string $type) {
        if ($type == 'concentracion')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/frmanexo27c.php';
        if ($type == 'tramite')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/frmanexo27t.php';
        if ($type == 'baja')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/frmanexo27b.php';
        if ($type == 'historico')
            return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/frmanexo27h.php';
        return 'http://sentre.sabgob.qroo.gob.mx/sistema/anexo27/frmanexo27c.php'; // default
    }

    public function syncRecordToRemote(Request $request)
    {
        $data = $this->validate($request, [
            'sentre_user' => 'required',
            'sentre_password' => 'required',
            'record_id' => 'required|exists:sentre_records,id',
        ]);

        $record = SentreRecord::findOrFail($data['record_id']);
        $sentreUser = SentreUser::where('username', $data['sentre_user'])->first();

        if (!$sentreUser || $record->sentre_user_id !== $sentreUser->id) {
            return response()->json([
                'code' => '403',
                'message' => 'El registro no pertenece al usuario proporcionado.'
            ], 403);
        }

        $loginURL = 'http://sentre.sabgob.qroo.gob.mx/login.php';
        $editURL = $this->getEditFormUrl($record->type) . '?accion=modificar&id_anexo=' . $record->record_id . '&page=&orden=';
        $submitURL = $this->getEditFormUrl($record->type);

        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();
        $page = $browser->newPage();

        try {
            $page->goto($loginURL);

            $setValueFunction = (new JsFunction)->parameters(['el', 'setText'])
                ->body("el.value = setText");

            $page->querySelectorEval('#login', $setValueFunction, $data['sentre_user']);
            $page->querySelectorEval('#password', $setValueFunction, $data['sentre_password']);
            $page->click('#wrap > table > tbody > tr > td > table:nth-child(6) > tbody > tr > td > table > tbody > tr:nth-child(2) > td > form > table > tbody > tr:nth-child(5) > td:nth-child(4) > input');

            // Esperar a que el login se procese
            $page->waitForNavigation();

            // Navegar al formulario de edición
            $page->goto($editURL);

            // Llenar el formulario con los datos de la DB
            $page->querySelectorEval('input[name="fecha_trans"]', $setValueFunction, $record->fecha_transferencia ?? '');
            $page->querySelectorEval('input[name="expediente"]', $setValueFunction, $record->expediente ?? '');
            $page->querySelectorEval('textarea[name="descripcion"]', $setValueFunction, $record->descripcion ?? '');
            $page->querySelectorEval('input[name="antiguedad"]', $setValueFunction, $record->anio_creacion ?? '');
            $page->querySelectorEval('input[name="per_del"]', $setValueFunction, $record->fecha_inicio ?? '');
            $page->querySelectorEval('input[name="per_al"]', $setValueFunction, $record->fecha_final ?? '');
            $page->querySelectorEval('input[name="tiempo_conservacion"]', $setValueFunction, $record->tiempo_conservacion ?? '');
            $page->querySelectorEval('input[name="n_legajos"]', $setValueFunction, $record->no_legajos ?? '');
            $page->querySelectorEval('input[name="n_hojas"]', $setValueFunction, $record->no_hojas ?? '');
            $page->querySelectorEval('input[name="preservacion"]', $setValueFunction, $record->preservacion ?? '');
            $page->querySelectorEval('input[name="localizacion"]', $setValueFunction, $record->ubicacion_fisica ?? '');
            $page->querySelectorEval('input[name="no_caja"]', $setValueFunction, $record->no_caja ?? '');
            $page->querySelectorEval('input[name="clasificacion"]', $setValueFunction, $record->clasificacion ?? '');
            $page->querySelectorEval('input[name="caracter"]', $setValueFunction, $record->caracter_documental ?? '');
            $page->querySelectorEval('textarea[name="observaciones"]', $setValueFunction, $record->observaciones ?? '');

            // Hacer click en guardar
            $page->click('input[name="modificar"]');

            // Esperar un momento para que aparezca el mensaje de éxito
            $page->waitForFunction((new JsFunction)->body("return document.body.innerText.includes('¡Cambios Guardados exitosamente!')"), ['timeout' => 5000]);

            $browser->close();
            $browser = null;

            return response()->json([
                'code' => '200',
                'message' => 'Registro sincronizado exitosamente con el servidor remoto.'
            ]);

        } catch (\Exception $e) {
            if (isset($browser)) {
                $this->takeScreenshot($page);
                $browser->close();
            }
            return response()->json([
                'code' => '500',
                'message' => 'Error al sincronizar con el servidor remoto: ' . $e->getMessage()
            ], 500);
        }
    }

    private function takeScreenshot($page){
        try {
            $screenshot = 'sentre_' .  uniqid() .'.png';
            $path = storage_path('app/puppeter-screenshots/'.$screenshot);
            $page->screenshot( ['path' => $path ]);
        } catch (\Exception $e) {
            // No podemos tomar la captura, probablemente el navegador se cerró
        }
    }


}
