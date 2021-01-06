$(document).ready(function(){
    $('.btn-delete').click(function(e){
        e.preventDefault();

        var fila = $(this).parents('tr');
        var id = fila.data('id');

        var form = $('#form-delete');
        var url = form.attr('action').replace(':USER_ID', id);
        var data = form.serialize();

        var respuesta = confirm('¿Estás seguro/a?');
        if (respuesta) {
            $.post(url, data, function(result){
                if (result.removed == 1)
                {
                    fila.fadeOut();
                    $('#mensajeAlerta').removeClass('hidden');
                    $('#mensajeParaUsuario').text(result.mensaje);

                    var totalClientes = $('#totalRegistros').text();
                    if ($.isNumeric(totalClientes))
                        $('#totalRegistros').text(totalClientes-1);
                    else
                        $('#totalRegistros').text(result.totalRegistros);
                }
                else
                {
                    $('#mensajeAlertaError').removeClass('hidden');
                    $('#mensajeErrorParaUsuario').text(result.mensaje);
                }
            }).fail(function() {
                alert('ERROR');
                fila.show();
            });
        }
    });
});