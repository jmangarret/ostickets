$(document).ready(function() {
    $.ajax({
        data: {select: "nrodeticket"},
        type: "POST",
        url: 'include/select-tickets.php',
        success: function(response){           
            $("#nrodeticket").append(response);
        }
    });    

    $.ajax({
        data: {select: "paymentmethod"},
        type: "POST",
        url: 'include/crm/selects-crm.php',
        success: function(response){                                                                  
            $("#paymentmethod").append(response);
        }
    });
	
    $.ajax({
        data: {select: "bancoemisor"},
        type: "POST",
        url: 'include/crm/selects-crm.php',
        success: function(response){                                                                  
            $("#bancoemisor").append(response);
        }
    });
	
	$.ajax({
        data: {select: "bancoreceptor"},
        type: "POST",
        url: 'include/crm/selects-crm.php',
        success: function(response){                                                                  
            $("#bancoreceptor").append(response);
        }
    });
	
	$.ajax({
        data: {select: "currency"},
        type: "POST",
        url: 'include/crm/selects-crm.php',
        success: function(response){                                                                  
            $("#currency").append(response);
        }
    });
	
});
            
        