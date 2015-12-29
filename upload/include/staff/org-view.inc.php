<?php
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($org)) die('Invalid path');

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%" class="has_bottom_border">
             <h2><a href="orgs.php?id=<?php echo $org->getId(); ?>"
             title="Reload"><i class="icon-refresh"></i> <?php echo $org->getName(); ?></a></h2>
        </td>
        <td width="50%" class="right_align has_bottom_border">
            <span class="action-button pull-right" data-dropdown="#action-dropdown-more">
                <i class="icon-caret-down pull-right"></i>
                <span ><i class="icon-cog"></i> <?php echo __('More'); ?></span>
            </span>
            <a id="org-delete" class="action-button pull-right org-action"
            href="#orgs/<?php echo $org->getId(); ?>/delete"><i class="icon-trash"></i>
            <?php echo __('Delete Organization'); ?></a>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <li><a href="#ajax.php/orgs/<?php echo $org->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i>
                    <?php echo __('Manage Forms'); ?></a></li>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="150"><?php echo __('Name'); ?>:</th>
                    <td><b><a href="#orgs/<?php echo $org->getId();
                    ?>/edit" class="org-action"><i
                    class="icon-edit"></i>&nbsp;<?php echo
                    $org->getName();
                    ?></a></td>
                </tr>
                <tr>
                    <th><?php echo __('Account Manager'); ?>:</th>
                    <td><?php echo $org->getAccountManager(); ?>&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="150"><?php echo __('Created'); ?>:</th>
                    <td><?php echo Format::db_datetime($org->getCreateDate()); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Last Updated'); ?>:</th>
                    <td><?php echo Format::db_datetime($org->getUpdateDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<div class="clear"></div>
<ul class="tabs">
    <li><a class="active" id="users_tab" href="#users"><i
    class="icon-user"></i>&nbsp;<?php echo __('Users'); ?></a></li>
    <li><a id="tickets_tab" href="#tickets"><i
    class="icon-list-alt"></i>&nbsp;<?php echo __('Tickets'); ?></a></li>
    <li><a id="notes_tab" href="#notes"><i
    class="icon-pushpin"></i>&nbsp;<?php echo __('Notes'); ?></a></li>
</ul>
<div class="tab_content" id="users">
<?php
include STAFFINC_DIR . 'templates/users.tmpl.php';
?>
</div>
<div class="tab_content" id="tickets"  style="display:none;">
<?php
include STAFFINC_DIR . 'templates/tickets.tmpl.php';
?>
</div>

<div class="tab_content" id="notes" style="display:none">
<?php
$notes = QuickNote::forOrganization($org);
$create_note_url = 'orgs/'.$org->getId().'/note';
include STAFFINC_DIR . 'templates/notes.tmpl.php';
?>
</div>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.org-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201, 204], function (xhr) {
            if (xhr.status == 204)
                window.location.href = 'orgs.php';
            else
                window.location.href = window.location.href;
         }, {
            onshow: function() { $('#org-search').focus(); }
         });
        return false;
    });
});
</script>

<script type="text/javascript" src="/ostickets/upload/js/autoNumeric.js"></script>