# Cpanel Disk space / Bandwith visualizer - notifier

Script PHP para visualizar el estado de los dominios en tus resellers/hostings con cpanel con un mini sistema de notificaciones.

## Instrucciones

Si tan solo quieres revisar un hosting, basta con llenar los datos de estas variables

$whmusername = "";
$whmhash     = "";
$whmserver   = "";

Si deseas revisar varios, puedes repetir estas variables y agregarlas al arreglo de servidores
Ej.

 - $whmusername2 = "";
 - $whmhash2     = "";
 - $whmserver2   = "";
 
 - $user[1] = array($whmusername2, $whmhash2, $whmserver2);

## Resultado
 
Al correr el script se te mostrara una pequeña tabla con todos tus dominios y los parametros principales y se te enviara una notificacion(si la activaste) con los dominios que este excediendose en bandwith o espacio.

## Screenshots

![Screenshot de la tabla]()

## Aviso

  - Nota: Para este script me base en algunas otras fuentes, una de ella de un tipo que ya no postea(devtrench).
