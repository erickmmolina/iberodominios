jQuery(document).ready(function($) {
    // Ejemplo: Al hacer click en un botón Buscar, hacemos AJAX
    // Supongamos que existe un form con class .iberodominios-search-form y un input name="domain"
    $('.iberodominios-search-form').on('submit', function(e) {
        e.preventDefault();
        var domain = $(this).find('input[name="domain"]').val().trim();
        if (!domain) {
            alert('Por favor, ingresa un dominio');
            return;
        }

        $.ajax({
            url: IberodominiosAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'iberodominios_check_domain',
                security: IberodominiosAjax.nonce,
                domain: domain
            },
            beforeSend: function() {
                // Podrías mostrar un loader
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    // Actualizar la interfaz con data (precio, disponibilidad, etc.)
                    // Por ejemplo, mostramos el resultado en un div .iberodominios-results
                    $('.iberodominios-results').html('<p>Dominio: '+data.domain+' - Status: '+data.status+'</p>');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('Error desconocido al verificar el dominio.');
            }
        });
    });
});
