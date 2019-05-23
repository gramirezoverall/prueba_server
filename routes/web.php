<?php
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Cabecera;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    class getLinks{

        const F = 1;
        const B = 2;
        const C = 3;
        const D = 4;

        public function __construct(){
            //echo constant('self::'.'D');
        }

        public function sendData($tipo_de_comprobante, $serie,$numero){
            $client = new Client();

            $url = 'https://api.nubefact.com/api/v1/5d7e4758-659c-4232-9513-4addf6263312';

            $result = $client->post($url,
              [
                'verify' => false,
                'exceptions' => false,
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'a5eb728f181b4780aa132e58f78a89780214b492fcbf44ef83fba313934ed5ac'
                ],
                GuzzleHttp\RequestOptions::JSON => [
                    "operacion" => "consultar_comprobante",
                	"tipo_de_comprobante" => $tipo_de_comprobante,
                	"serie" => $serie,
                	"numero" => $numero
                ]
              ]
            );
            return json_decode($result->getBody());
        }

        public function getPreDoc(){
            $predocumentos = DB::select( DB::raw(
                "SELECT TOP 8
                CodVenta,
            	SUBSTRING(NumeroComprobantePagoSocio, 1, 1) AS tipo_de_comprobante,
            	SUBSTRING(NumeroComprobantePagoSocio, 1, 4) AS serie,
            	RIGHT(NumeroComprobantePagoSocio, 4) AS numero
            	FROM [PruebaLiquidacion].[Ventas].[CabeceraVenta2]
            	where TipoVenta='VF'
            	and FechaCreacion >= '2019-13-02'
            	and FechaCreacion <= '2019-20-02'
            	order by FechaCreacion ASC"
            ));
            return $predocumentos;
        }

        public function getNubefactData(){
            $nubeFactData = [];
            foreach ($this->getPreDoc() as $predoc) {
                $nubeFactData = $this->sendData(
                    constant('self::'.$predoc->tipo_de_comprobante),
                    $predoc->serie,
                    $predoc->numero
                );
                if(!isset($nubeFactData->errors) and empty($nubeFactData->errors)){
                    DB::enableQueryLog();

                    DB::table('Ventas.CabeceraVenta2')
                        ->where('CodVenta', $predoc->CodVenta)
                        ->update([
                            'Enlace_PDF' => $nubeFactData->enlace_del_pdf,
                            'Enlace_XML' => $nubeFactData->enlace_del_xml,
                        ])
                    ;
                }
            }
        }
    }

    $getLinks = new getLinks;
    $docs = $getLinks->getNubefactData();
    //echo "<pre>".print_r($docs,true)."<pre>";

    // foreach ($docs as $doc) {
    //     if(!isset($doc->errors) and empty($doc->errors)){
    //         echo "<pre>".print_r($doc,true)."<pre>";
    //         DB::table('users')
    //             ->where('id', 1)
    //             ->update(['votes' => 1]);
    //     }
    // }
    // $client = new Client();
    //
    // $url = 'https://api.nubefact.com/api/v1/5d7e4758-659c-4232-9513-4addf6263312';
    // $fecha_inicio = '2018-11-20';
    // $fecha_fin = '2018-11-20';
    // $api_key = '47bb8196ce52b4733b23594c2ddb8ae27369a7d3oPLiuK82xz';
    //
    // $result = $client->post($url,
    //   [
    //     'verify' => false,
    //     'headers' => [
    //         'Content-Type'  => 'application/json',
    //         'Authorization' => 'a5eb728f181b4780aa132e58f78a89780214b492fcbf44ef83fba313934ed5ac'
    //     ],
    //     GuzzleHttp\RequestOptions::JSON => [
    //         "operacion" => "consultar_comprobante",
    //     	"tipo_de_comprobante" => 2,
    //     	"serie" => "B279",
    //     	"numero" => 1133
    //     ]
    //   ]
    // );
    //
    // $result = json_decode($result->getBody());

    // $predocumentos = DB::table('Ventas.CabeceraVenta')->select('NumeroComprobantePagoSocio')
    //     ->where([
    //         ['TipoVenta', '=', 'VF'],
    //         ['FechaCreacion', '>=', '2019-13-02'],
    //         ['FechaCreacion', '<=', '2019-20-02'],
    //     ])
    // ->get();
    //
    // foreach ($predocumentos as $predocumento) {
    //
    //     echo $predocumento->NumeroComprobantePagoSocio."<br>";
    //     echo substr($predocumento->NumeroComprobantePagoSocio,0,1)."<br>";
    //     echo substr($predocumento->NumeroComprobantePagoSocio,0,4)."<br>";
    //     echo substr($predocumento->NumeroComprobantePagoSocio,-4)."<br>";
    //     die();
    // }



    // foreach ($predocumentos as $predocumento) {
    //     echo "<pre>".print_r($predocumento,true)."<pre>";
    //     die;
    // }


});
