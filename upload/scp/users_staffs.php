<?php
/*********************************************************************
    staff.php

    Evertything about staff members.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('agent'));

if($_POST){

    $mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    $count = 0;
    foreach ($_POST as $key => $value) {
        if($count == 0)
            $count++;
        else{
            if($count == 1){
                $user_id = $value;
                $count++;
            }
            else{
                $staff_id = $value;

                $query = "SELECT * FROM  `ost_user_agent` WHERE user_id = $user_id";
                $result = $mysqli->query($query);

                $rowcount = mysqli_num_rows($result);

                $query2 = "SELECT * FROM  `ost_help_topic` WHERE topic_id = $staff_id";
                $result2 = $mysqli->query($query2);
                $row2 = $result2->fetch_array();
                $fil = mysqli_num_rows($result2);
                if($fil >0)
                    $name = $row2[16];
                else
                    $name = "";

                $query3 = "SELECT * FROM  `ost_user` WHERE id = $user_id";
                $result3 = $mysqli->query($query3);
                $row3 = $result3->fetch_array();

                if($rowcount > 0) 
                    $mysqli->query("UPDATE ost_user_agent SET staff_id=$staff_id,staff_name='$name' WHERE user_id = $user_id");
                else    
                    $mysqli->query("INSERT INTO ost_user_agent VALUES (NULL,$user_id,'".$row3[4]."',$staff_id,'".$row2[16]."')");
                
                $count = 1;
            }
        }
    }

}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');

?>

<div id="msg_notice" style="display:none;">Successfully updated</div>

<div class="pull-left" style="width:700px;padding-top:5px;padding-bottom:2px;">
    <h2>Usuarios</h2>
</div>

<?php

$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$query = "SELECT a . * , b . * FROM ost_user a LEFT JOIN ost_user_agent b ON a.id = b.user_id ";
if(isset($_GET["order"]))
    $query .= "ORDER BY `name` DESC ";
else
    $query .= "ORDER BY `name` ASC ";
$result = $mysqli->query($query);

while($row = $result->fetch_array())
    $rows[] = $row;

$rowcount = mysqli_num_rows($result);

$query2 = "SELECT * FROM  `ost_help_topic` ORDER BY `topic` ASC";
$result2 = $mysqli->query($query2);

while($row2 = $result2->fetch_array())
    $rows2[] = $row2;

?>

<form action"" method="POST">

<?php csrf_token(); ?>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption>Mostrando <?=$rowcount?> usuarios</caption>
    <thead>
        <tr>
            <th width="180"><a class="asc" href="users_staffs.php?&amp;order=DESC&amp;sort=name">Usuario</a></th>
            <th width="80"><a href="users_staffs.php?&amp;order=DESC&amp;sort=type">Agente</a></th>
            <th width="80"><a href="users_staffs.php?&amp;order=DESC&amp;sort=type">Acciones</a></th>
        </tr>

    </thead>
    <tbody>

<?php
$count = 0;
foreach($rows as $row)
{ ?>
    <tr>
        <td>
            <?=$row['name']?>
            <input type="hidden" name="<?=$count?>user_id" id="<?=$count?>user_id" value="<?=$row['id']?>"> 
        </td>
        <td>
            <?=$row['staff_name']?>
        </td>
        <td>
            <select name="<?=$count?>agent" id="<?=$count?>agent">

                <option value="0"> — Ninguno —</option>
                <?php
                    foreach($rows2 as $row2){
                        $options = "<option value='".$row2['topic_id']."'";
                        if ($row['staff_id'] == $row2['topic_id'])
                            $options .=" selected='selected'";
                        $options .=">" . htmlentities($row2['topic']) . "</option>";
                        
                        echo $options;
                    }
                ?>
            </select>
        </td>
    </tr>
<?php
$count++;
}

/* free result set */
$result->close();

/* close connection */
$mysqli->close();

?>
                </tbody><tfoot>
     <tr>
        <td colspan="6">
            Usuarios
        </td>
     </tr>
    </tfoot>
</table>

<p class="centered">
    <input class="button" type="submit" name="guardar" value="Guardar">
</p>

</form>

<?php
include(STAFFINC_DIR.'footer.inc.php');

if($_POST){
    echo "
    <script>
        $('#msg_notice').show();
    </script>";
}
?>
