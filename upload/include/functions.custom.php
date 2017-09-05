<?php
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function getLimiteCredito($userId=0){
    global $mysqli;
    if (!$userId) $userId=$_SESSION["_auth"]["user"]["id"];

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
            AND c.id = ".$userId;
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limite = "BsF ".number_format($filas[0],2,",",".");
    return $limite;        
}

function getLimiteDisponible($userId=0){
    global $mysqli;
    if (!$userId) $userId=$_SESSION["_auth"]["user"]["id"];

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
                AND c.id = ".$userId;
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limiteDisponible = $filas[0];
    return $limiteDisponible;

}

function getFechaModificacionSaldo($userId=0){
    global $mysqli;
    if (!$userId) $userId=$_SESSION["_auth"]["user"]["id"];

    $limit="SELECT 
                b.date 
            FROM 
                ost_user AS a INNER JOIN ost_auditoria_limite_credito AS b ON a.org_id=b.org_id 
            WHERE 
                a.id=". $userId." ORDER BY b.date DESC Limit 1";  
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

function getFeeNacional($userId){
    global $mysqli;
    $query = "  SELECT a.value FROM 
        ost_form_entry_values a,
        ost_form_entry b,
        ost_user c
        WHERE
            b.object_type = 'O'
            AND b.object_id = c.org_id
            AND a.entry_id = b.id
            AND a.field_id = 95
            AND c.id = ".$userId;
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $nacional = $filas[0];
    return $nacional;
}

function getFeeInternacional($userId){
    global $mysqli;
    $query = "  SELECT a.value FROM 
        ost_form_entry_values a,
        ost_form_entry b,
        ost_user c
        WHERE
            b.object_type = 'O'
            AND b.object_id = c.org_id
            AND a.entry_id = b.id
            AND a.field_id = 96
            AND c.id = ".$userId;
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $internacional = $filas[0];
    return $internacional;
}

function getTipoSatelite($userId){
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
                AND a.field_id = 97
                AND c.id = ".$userId;
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    //$filas retorna a.value = '{"Emision Rapida":"Emision Rapida"}'
    //Convertimos json a array
    $arrTipo= json_decode($filas['value'],true);
    
    if ($arrTipo['Emision Rapida'])
        $tipo = $arrTipo['Emision Rapida'];
    
    if ($arrTipo['Verificar Credito'])
        $tipo = $arrTipo['Verificar Credito'];

    if ($arrTipo['Pago Adjunto'])
        $tipo = $arrTipo['Pago Adjunto'];
    
    return $tipo;
}

function getLocalizadorStatus($ticketId){
    global $mysqli;
    $query = "  SELECT c.entry_id
                FROM ost_ticket a, ost_form_entry b, ost_form_entry_values c
                WHERE a.ticket_id = '".$ticketId."'
                AND a.ticket_id = b.object_id
                AND b.id = c.entry_id
                AND c.field_id ='86'";
    $result = $mysqli->query($query);
    $row = $result->fetch_array();
    $obj = $row[0];

    $query2 = "SELECT value FROM  `ost_form_entry_values` WHERE entry_id = '$obj' AND field_id ='86'";
    $result2 = $mysqli->query($query2);
    $row2 = $result2->fetch_array();
    $loc = $row2[0];

    return $loc;
}
?>