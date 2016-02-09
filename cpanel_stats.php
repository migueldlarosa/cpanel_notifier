<?php

$domex = ""; //Esta variable contendra todos los dominios que esten por excederse en su espacio en disco (que su % restante sea mayor a 94 y que su espacio restante sea menor a 100Mb)
$domex100 = "";

$user = array();

$whmusername = "";
$whmhash     = "";
$whmserver   = "";

$user[] = array($whmusername, $whmhash, $whmserver);
//Agregar elementos al arreglo si se tienen varios servidores que consultar Ej:
//$user[1] = array($whmusername2, $whmhash2, $whmserver2);
$output = "<h1>Ancho de Banda y Espacio de Cuentas en CPanel</h1>";
$bandwidthlimit = "Bandwidth: "; //Esta variable contendra todos los dominios proximos a exceder su bandwidth
$bandwidthlimitcounter = 0;

foreach ($user as $v)
{

    $query = "https://".$v[2].":2087/xml-api/showbw";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    $header[0] = "Authorization: WHM $v[0]:" . preg_replace("'(\r|\n)'","",$v[1]);
    curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
    curl_setopt($curl, CURLOPT_URL, $query);
    $result = curl_exec($curl);
    if ($result == false) {
        error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
    }
    curl_close($curl);
    $xml = new SimpleXMLElement($result);

    $output .= "<h3>".$v[2]."</h3>";
    $output .= '<table border="1" style="border-collapse:collapse;font-family:monospace" cellpadding="5"><tbody><tr><th>Dominio</th><th>BW Total</th><th colspan="2">Porcentaje BW Usado</th><th>Usado BW</th><th>Espacio Total</th><th>Espacio Usado</th><th colspan="2">Porcentaje Usado</th></tr>';

    foreach($xml->bandwidth[0] as $acct)
    {
        $warning = false;
        if (isset($acct->user)){

            $query2 = "https://".$v[2].":2087/xml-api/accountsummary?user=".$acct->user;
            $curl2 = curl_init();
            curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($curl2, CURLOPT_RETURNTRANSFER,1);
            $header[0] = "Authorization: WHM $v[0]:" . preg_replace("'(\r|\n)'","",$v[1]);
            curl_setopt($curl2,CURLOPT_HTTPHEADER,$header);
            curl_setopt($curl2, CURLOPT_URL, $query2);
            $result2 = curl_exec($curl2);
            if ($result2 == false) {
                error_log("curl_exec threw error \"" . curl_error($curl2) . "\" for $query2");
            }
            curl_close($curl2);
            $xml2 = new SimpleXMLElement($result2);
        }
        if (isset($acct->limit)){
            $limit = $acct->limit;
            $mbs   = floatval($limit)/(1024*1024);
            $total = $acct->totalbytes;
            $total2 = floatval($total)/(1024*1024);
            $total3 = $xml2 -> acct;
            $total4 = $total3 -> disklimit;
            $totalusado = $total3 -> diskused;
            $percentdd = round((floatval($totalusado)/floatval($total4))*100);
            $warndd = ($percentdd >= 90) ? 'style="background:#F00;color:#FFF;font-weight:bold"' : '';
            if($percentdd>=90)
            {
                $colordd = '#f00';
            }
            if($percentdd>=94)
            {
                $colordd = '#f00';
                $warning = true;
            }
            elseif($percentdd>=75)
            {
                $colordd = '#FFE900';
            }
            else
            {
                $colordd = '#00E70A';
            }
            if($limit>0){
                $percent = round((floatval($total)/floatval($limit))*100);}
            else{
                $percent = 0;
            }
            $warn = ($percent >= 90) ? 'style="background:#F00;color:#FFF;font-weight:bold"' : '';
            if($percent>=90)
            {
                $color = '#f00';
            }
            elseif($percent>=75)
            {
                $color = '#FFE900';
            }
            else
            {
                $color = '#00E70A';
            }

            if($percent>=95)
            {
                $bandwidthlimitcounter = $bandwidthlimitcounter+1;
                $bandwidthlimit = $bandwidthlimit." ".$acct->maindomain;
            }
        }

        $output .= '<tr $warn=""><td>' . $acct->maindomain . '</td><td>' . $mbs . 'MB</td><td>' . $percent .  '%</td><td>
<div style="width:100px;height:10px;border:1px solid #000">
<div style="height:10px;width:'.$percent.'px;background:'.$color.'"></div>
</div>
</td><td>'.round($total2).'MB</td><td>'.$total4.'</td><td>'.$totalusado.'</td><td>' . $percentdd .  '%</td><td>
<div style="width:100px;height:10px;border:1px solid #000">
<div style="height:10px;width:'.$percentdd.'px;background:'.$colordd.'"></div>
</div>
</td></tr>';
        $dom = $acct->maindomain;
        $rtot = round($total2);
        if(strlen($dom) > 1){
            if (true) {
                if($warning & $total4-$totalusado < 100){
                    $restante = $total4-$totalusado;
                    $domex.=$dom."($restante MB)";
                }
                if($total4-$totalusado < 100){
                    $restante100 = $total4-$totalusado;
                    $domex100.=$dom."($restante100 MB)";
                }
                if(round($total2)-$mbs < 100){
                    $restante = $mbs-$total2;
                    $domex2.=$dom."($restante MB)";
                }

            }
        }
    }

    $output .= '</tbody></table>';
}
echo $output;

/*

//Activar solo si quieres notificaciones via Pushover, puedes sustituir esto por cualquier otra forma de contacto Ej. email.

if(strlen($domex)>1){
    curl_setopt_array($ch = curl_init(), array(
        CURLOPT_URL => "https://api.pushover.net/1/messages.json",
        CURLOPT_POSTFIELDS => array(
            "token" => "",
            "user" => "",
            "message" => "Espacios - {$domex}",
        )));
    curl_exec($ch);
    curl_close($ch);
}

if($bandwidthlimitcounter > 0){

    curl_setopt_array($ch = curl_init(), array(
        CURLOPT_URL => "https://api.pushover.net/1/messages.json",
        CURLOPT_POSTFIELDS => array(
            "token" => "",
            "user" => "",
            "message" => "{$bandwidthlimit}",
        )));
    curl_exec($ch);
    curl_close($ch);

}
*/

/*

//opcional si deseas guardar el ultimo resultado para evitar procesar el php nuevamente
//Ej. Yo tengo un cron corriendo este archivos cada Hora en horario laboral y sabados y domingo cada 12 horas
//Asi solo tengo que leer el archivo de texto en vez de llamar el PHP a cada consulta

$fichero = 'espacios.txt';
$actual = "Espacios ".$domex." Bandwidth ".$bandwithlimit;
file_put_contents($fichero, $actual);
*/
?>
