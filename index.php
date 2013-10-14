<?php   
    /*here I include the file that has the class Db*/
    require_once 'Db.php';
    /*here I include the config.php*/
    require_once 'Conf.php';

    

    /*Creamos la instancia del objeto. Ya estamos conectados*/
    $bd=Db::getInstance();

    /*Creamos una query sencilla*/
    $sql='SELECT * FROM student';
    //$bd->conectar();

    /*Ejecutamos la query*/
    $stmt=$bd->ejecutar($sql);

    /*Realizamos un bucle para ir obteniendo los resultados*/
    while ($x=$bd->obtener_fila($stmt,0))
    {
       echo $x['first_name'] . "\n";
    }
?> 