<?php

require('admin.inc.php');

$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('agent'));

if($_POST) {

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
                $submenu = $value;
                $count++;
            }
            else{
                $menu = $value;

                $query = "SELECT * FROM  `ost_list_items_custom` WHERE submenu_id = $submenu";
                $result = $mysqli->query($query);

                $rowcount = mysqli_num_rows($result);

                $query2 = "SELECT * FROM  `ost_help_topic` WHERE topic_id = $menu";
                $result2 = $mysqli->query($query2);
                $row2 = $result2->fetch_array();
                $fil = mysqli_num_rows($result2);
                if($fil > 0)
                    $name = $row2[16];
                else
                    $name = "";

                $query3 = "SELECT * FROM  `ost_list_items` WHERE id = $submenu";
                $result3 = $mysqli->query($query3);
                $row3 = $result3->fetch_array();

                if($rowcount > 0){
                    $mysqli->query("UPDATE ost_list_items_custom SET menu_id=$menu,menu_value='$name' WHERE submenu_id = $submenu");
                }
                else if($menu > 0){
                    $mysqli->query("INSERT INTO ost_list_items_custom VALUES (NULL,$menu,'$name',$submenu,'".$row3[3]."')");
                }

                $count = 1;
            }
        }
    }
}

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');

?>

<div id="msg_notice" style="display:none;">Successfully updated</div>

<div class="pull-left" style="width:700;padding-top:5px;">
    <h2>Items</h2>
</div>

<?php

$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$query = "  SELECT 
                a.id as id_list,a.value,b.* 
            FROM 
                ost_list_items a 
                LEFT JOIN ost_list_items_custom b ON a.id = b.submenu_id 
            WHERE 
                a.list_id = 6 
            ORDER BY 
                b.menu_value,
                a.value ASC";

$result = $mysqli->query($query);

while($row = $result->fetch_array())
    $rows[] = $row;

$rowcount = mysqli_num_rows($result);

$query2 = "SELECT topic_id,topic FROM `ost_help_topic` WHERE `isactive` = 1 AND ispublic = 1";
$result2 = $mysqli->query($query2);

while($row2 = $result2->fetch_array())
    $rows2[] = $row2;

?>

<form action"" method="POST">

<?php csrf_token(); ?>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption>Mostrando <?=$rowcount?> items</caption>
    <thead>
        <tr>
            <th width="180"><a class="asc" href="users_staffs.php?&amp;order=DESC&amp;sort=name">SubMenu</a></th>
            <th width="80"><a href="users_staffs.php?&amp;order=DESC&amp;sort=type">Menu</a></th>
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
            <?=$row['value']?>
            <input type="hidden" name="<?=$count?>submenu" id="<?=$count?>submenu" value="<?=$row['id_list']?>"> 
        </td>
        <td>
            <?php if($row['menu_value'] == "") echo " — Ninguno —"; else echo $row['menu_value']; ?>
        </td>
        <td>
            <select name="<?=$count?>menu" id="<?=$count?>menu">
                <option value="0"> — Ninguno —</option>
                <?php
                    foreach($rows2 as $row2){
                        $options = "<option value='".$row2['topic_id']."'";
                        if ($row['menu_id'] == $row2['topic_id'])
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
    </tbody>
    <tfoot>
     <tr>
        <td colspan="6">
            Items
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

<script type="text/javascript">
    $(document).ready(function() {
        $("#info").focus();
    });
</script>