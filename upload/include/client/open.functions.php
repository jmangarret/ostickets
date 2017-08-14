<?php
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function getLimiteCredito(){
    global $mysqli;
    $query = "  SELECT 
            a.value 
        FROM 
            ost_form_entry_values a,
            ost_form_entry b,
            ost_user c
        WHERE
            b.object_type = 'O'
            AND b.object_id = c.org_id
            AND a.entry_id = b.id
            AND a.field_id = 90
            AND c.id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limite = "BsF ".number_format($filas[0],2,",",".");
    return $limite;        
}

function getLimiteDisponible(){
    global $mysqli;
    $query = "  SELECT 
                a.value 
            FROM 
                ost_form_entry_values a,
                ost_form_entry b,
                ost_user c
            WHERE
                b.object_type = 'O'
                AND b.object_id = c.org_id
                AND a.entry_id = b.id
                AND a.field_id = 91
                AND c.id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limiteDisponible = $filas[0];
    return $limiteDisponible;

}

function getFechaModificacionSaldo(){
    global $mysqli;
    //Inicio Billy 11/02/2016 Se agrego la fecha de la ultima modificacion del saldo disponible
    //Query para consultar en la base de datos la ultima fecha de actualizacion 
    $limit="SELECT 
                b.date 
            FROM 
                ost_user AS a INNER JOIN ost_auditoria_limite_credito AS b ON a.org_id=b.org_id 
            WHERE 
                a.id=". $_SESSION["_auth"]["user"]["id"]." ORDER BY b.date DESC Limit 1";  

    $limit2 = $mysqli->query($limit);
    $row = $limit2->fetch_array();

    return $row['date'];

}

function getValUserAgent(){
    global $mysqli;
    $query = "SELECT staff_name FROM  `ost_user_agent` WHERE user_id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $rowcount = mysqli_num_rows($result);
    return $rowcount;

}

function getDptoUserAgent(){
    global $mysqli;
    $query = "SELECT staff_name FROM  `ost_user_agent` WHERE user_id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $rowcount = mysqli_num_rows($result);
    $row = $result->fetch_array();
    return $row[0];

}

function getDefaultDpto(){
    global $mysqli;
    $query2 = " SELECT b.dept_name
            FROM  `ost_config` a, ost_department b
            WHERE a.id =89
                AND a.value = b.dept_id";
    $result2 = $mysqli->query($query2);
    $row2 = $result2->fetch_array();
    $dep = $row2[0];
    return $dep;
}
?>