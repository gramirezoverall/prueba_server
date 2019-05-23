<?php

namespace App\Services;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Cabecera;
use Illuminate\Support\Facades\DB;

class GetLinks{

    public function getPreDoc(){
        $predocumentos = DB::select( DB::raw(
            "
            SELECT
            (SELECT (CASE  WHEN COUNT(DESCRIPCION)  > 0 THEN 'SI' ELSE 'NO' END)
            FROM
            SEGURIDAD_SISTEMA.USUARIO SU
            WHERE SU.Estado=0 AND SU.Nombre IN(
            SELECT  ID_REVISOR FROM WORKFLOW.FLUJO_DOCUMENTARIO FLA
            INNER JOIN WORKFLOW.NODO_REVISION NR ON  FL.ID=NR.ID_FLUJO_DOCUMENTARIO
            INNER JOIN WORKFLOW.REVISOR_NODO_REVISION RNR ON  RNR.ID_NODO_REVISION=NR.ID
            WHERE NR.AUD_ALTA=1 AND RNR.AUD_ALTA=1 AND FLA.ID=FL.ID)) AS USER_DELETE,
   		 (SELECT
            (CASE
            WHEN COUNT(TE.ID)=0 AND SUM(TE.TOTL)=0  THEN 'INCOMPLETO'
   		  WHEN (COUNT(TE.ID)> SUM(TE.TOTL)) THEN 'INCOMPLETO'
             ELSE 'COMPLETO' END)
   		  FROM
   		 (SELECT
   		 T.ID,
   		 SUM(CASE  T.CASE1+T.CASE2  WHEN 2  THEN 1 ELSE 0 END) AS TOTL
   		 FROM (SELECT   RNR.ID_NODO_REVISION AS ID, COUNT(RNR.ID_NODO_REVISION) AS ID_NODO_REVISION,
   		(CASE  WHEN RNR.ID_NODO_REVISION  IS NULL  THEN 0 ELSE 1 END) AS CASE1,
   		(CASE  WHEN  RNR.AUD_ALTA=1  THEN 1 ELSE 0 END) AS CASE2
   		 FROM  WORKFLOW.NODO_REVISION NR
   		 LEFT JOIN WORKFLOW.REVISOR_NODO_REVISION RNR ON  RNR.ID_NODO_REVISION=NR.ID
   		 WHERE NR.AUD_ALTA=1 AND NR.ID_FLUJO_DOCUMENTARIO=FL.ID
   		 GROUP BY
   		 RNR.ID_NODO_REVISION,
   		 RNR.AUD_ALTA,
   		 NR.AUD_ALTA
   		 ) T
   		 GROUP BY
   		 T.ID)AS TE) AS ESTADO,
   		 FL.ID AS FLID,
            DC.DESC_LARGA AS DCDESC_LARGA,
            CL.CIA_NO_CIA,
            CL.CIA_NOMBRE AS CLCIA_NOMBRE,
            CT.CLIE_COD_CLIE,
            CT.CLIE_DESC_CLIE AS CCCLIE_DESC_CLIE,
            CC.CECO_COD_CCOSTO_CTBLE AS CCCECO_COD_CCOSTO_CTBLE,
            DIV.ASUC_DESC_ASUC AS DIVASUC_COD_ASUC,
            FL.FECHA_REGISTRO,
            FL.NUM_NIVELES,
            FL.DESC_LARGA AS FLDESC_LARGA
            FROM WORKFLOW.FLUJO_DOCUMENTARIO FL
            INNER JOIN WORKFLOW.TIPO_DOCUMENTO AS DC  ON FL.ID_TIPO_DOCUMENTO=DC.ID
            INNER JOIN WORKFLOW.CONSULTORA     AS CL  ON FL.CIA_NO_CIA=CL.CIA_NO_CIA
            LEFT JOIN WORKFLOW.CLIENTE AS CT  ON FL.CLIE_COD_CLIE=CT.CLIE_COD_CLIE AND FL.CIA_NO_CIA=CT.CIA_NO_CIA
            LEFT JOIN WORKFLOW.CENTRO_COSTO AS CC  ON FL.CECO_COD_CCOSTO_CTBLE=CC.CECO_COD_CCOSTO_CTBLE AND FL.CLIE_COD_CLIE=CC.CLIE_COD_CLIE AND FL.CIA_NO_CIA=CC.CIA_NO_CIA
            LEFT JOIN WORKFLOW.DIVISION AS DIV ON FL.ASUC_COD_ASUC=DIV.ASUC_COD_ASUC
            WHERE FL.AUD_ALTA=1
            "
        ));
        return $predocumentos;
    }

    public function getNubefactData(){
        echo "<pre>".print_r($this->getPreDoc(),true)."<pre>";
    }
}
