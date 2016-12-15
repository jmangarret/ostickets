$(document).ready(function() 
{
  var animating = false,
  submitPhase1 = 1100,
  submitPhase2 = 400,
  logoutPhase1 = 800,
  $login = $(".login"),
  $app = $(".app");
  
  function ripple(elem, e) 
  { 
    $(".ripple").remove();
    var elTop = elem.offset().top,
    elLeft = elem.offset().left,
    x = e.pageX - elLeft,
    y = e.pageY - elTop;
    var $ripple = $("<div class='ripple'></div>");
    $ripple.css({top: y, left: x});
    elem.append($ripple);
  };
  
  $(document).on("click", ".login__submit", function(e) 
  {
    if (animating) return;
    animating = true;
    var that = this;
    ripple($(that), e);
    $(that).addClass("processing");
    setTimeout(function() 
    {
      $(that).addClass("success");

      setTimeout(function() 
      {
        $app.show();
        $app.css("top");
        $app.addClass("active");
      }, submitPhase2 - 70);

      setTimeout(function()   
      {
        var ltipo = $("#TipoUsuario").val();
        var luser = $("#NombreUsuario").val();
        var lpasswd = $("#PassUsuario").val();

        if(luser =='')
        {
          swal("Ingresar Credenciales", "Debe ingresar sus datos de acceso", "error");
        }
        else  if(ltipo == 0)
        {
          swal("Acceso denegado", "Verifique su tipo de usuario", "error");
        }

        else
        { 
          $.ajax(
          {
            type: "POST",
            url: "login.php",
            data: "ltipo="+ltipo+"&luser="+luser+"&lpasswd="+lpasswd,
            /*success: function(html)
            {
              alert(html);
              /*if(html=='true') {
              window.location="principal.php";
              }else{
              $("#conectando").html(html);
              }
            }*/
          });
        }

        animating = false;
        $(that).removeClass("success processing");
      }, submitPhase2);
    }, submitPhase1);
  });
});