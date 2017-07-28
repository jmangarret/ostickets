$(document).ready(function() {

    $.ajax({
        data: {select: "nrodeticket"},
        type: "POST",
        url: 'include/select_tickets.php',
        success: function(response){                   
            $("#nrodeticket").append(response);
            $("#nrodeticket").select2();
        }
    });    

    $.ajax({
        data: {select: "paymentmethod"},
        type: "POST",
        url: 'include/crm/selects_crm.php',
        success: function(response){                                                                  
            $("#paymentmethod").append(response);
            $("#paymentmethod").select2();
        }
    });
	
    $.ajax({
        data: {select: "bancoemisor"},
        type: "POST",
        url: 'include/crm/selects_crm.php',
        success: function(response){                                                                  
            $("#bancoemisor").append(response);
            $("#bancoemisor").select2();
        }
    });
	
	$.ajax({
        data: {select: "bancoreceptor"},
        type: "POST",
        url: 'include/crm/selects_crm.php',
        success: function(response){                                                                  
            $("#bancoreceptor").append(response);
            $("#bancoreceptor").select2();
        }
    });
	
	$.ajax({
        data: {select: "currency"},
        type: "POST",
        url: 'include/crm/selects_crm.php',
        success: function(response){                                                                  
            $("#currency").append(response);
            $("#currency").select2();
        }
    });
	
});
            
        