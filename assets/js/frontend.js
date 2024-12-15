jQuery(document).ready(function($) {
    var offset = 0;
    var limit = 100;
    var currentMode = null;
    var currentDomain = null;
    var domainsToCheck = [];
    var exactDomainStatusShown = false;
    var exactDomainInfo = {};

    $('.iberodominios-search-form').on('submit', function(e) {
        e.preventDefault();
        var domain = $(this).find('input[name="domain"]').val().trim();
        if (!domain) {
            alert('Por favor, ingresa un dominio');
            return;
        }

        offset = 0;
        domainsToCheck = [];
        currentDomain = domain;
        exactDomainStatusShown = false;
        exactDomainInfo = {};

        $('.iberodominios-results').html(
            '<div class="iberodominios-loading-indicator">' +
            '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> ' +
            'Cargando disponibilidad...</div>'
        );

        loadDomains(domain, offset, limit);
    });

    function loadDomains(domain, offset, limit) {
        $.ajax({
            url: IberodominiosAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'iberodominios_check_domain',
                security: IberodominiosAjax.nonce,
                domain: domain,
                offset: offset,
                limit: limit
            },
            success: function(response) {
                $('.iberodominios-loading-indicator').remove();

                if (!response.success) {
                    $('.iberodominios-results').html('<div class="error">' + response.data.message + '</div>');
                    return;
                }

                var data = response.data;
                currentMode = data.mode;

                // Si es modo exact, primero mostramos el resultado exacto si no se mostró antes
                if (currentMode === 'exact' && !exactDomainStatusShown) {
                    exactDomainStatusShown = true;
                    var html = '';
                    if (data.status === 'available') {
                        html += '<div style="padding:10px;background:#e0f7e0;border:1px solid #0c0;margin-bottom:10px;">';
                        html += 'Your domain is available!<br><strong>' + data.domain + '</strong><br>Price: ' + data.price + ' ' + data.currency;
                        html += '</div>';
                    } else {
                        html += '<div style="padding:10px;background:#f7e0e0;border:1px solid #c00;margin-bottom:10px;">';
                        html += 'Sorry, ' + data.domain + ' is unavailable.';
                        html += '</div>';
                    }

                    html += '<h3>Other suggestions</h3>';
                    html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;">';
                    html += '<tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                    html += '</table>';

                    $('.iberodominios-results').html(html);
                } else if (currentMode === 'list' && offset === 0) {
                    // Modo list, primera carga
                    var html = '';
                    html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;">';
                    html += '<tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                    html += '</table>';
                    $('.iberodominios-results').html(html);
                }

                var $table = $('.iberodominios-table');
                if (data.results && data.results.length > 0) {
                    data.results.forEach(function(d) {
                        $table.append(createDomainRow(d.domain));
                        domainsToCheck.push(d.domain);
                    });
                }

                if (data.has_more) {
                    $('.iberodominios-results').append('<button class="iberodominios-load-more">Cargar más</button>');
                    $('.iberodominios-load-more').off('click').on('click', function() {
                        $(this).remove();
                        $('.iberodominios-results').append(
                            '<div class="iberodominios-loading-indicator">' +
                            '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> ' +
                            'Cargando más resultados...</div>'
                        );
                        offset += limit;
                        loadDomains(currentDomain, offset, limit);
                    });
                }

                loadAllIndividual();
            },
            error: function() {
                $('.iberodominios-results').html('<div class="error">Error desconocido al verificar el dominio.</div>');
            }
        });
    }

    function createDomainRow(domain) {
        // Añadimos el blur sólo al texto del dominio, no al icono
        // Clases utilizables desde Elementor:
        // - .domain-loading para el td
        // - .domain-wrapper para el texto del dominio (con blur)
        // - .loading-icon para el svg
        var svg = '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..." class="loading-svg">';
        var row = '<tr data-domain="' + domain + '">' +
                  '<td class="domain-loading" style="position:relative;">' +
                    '<span class="domain-wrapper" style="display:inline-block;filter:blur(1px);">' + domain + '</span>' +
                    '<span class="loading-icon" style="position:absolute;left:5px;top:5px;z-index:9999;">' + svg + '</span>' +
                  '</td>' +
                  '<td>-</td>' +
                  '<td>-</td>' +
                  '</tr>';
        return row;
    }
    

    function loadAllIndividual() {
        if (domainsToCheck.length === 0) return;
        // Cargamos uno a uno
        loadNextIndividual(0);
    }

    function loadNextIndividual(index) {
        if (index >= domainsToCheck.length) {
            return; // completado
        }
    
        var dom = domainsToCheck[index];
        var $row = $('.iberodominios-table tr[data-domain="'+dom+'"]');
        // Activar animación sólo para esta fila
        $row.addClass('loading-running');
    
        $.ajax({
            url: IberodominiosAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'iberodominios_check_single',
                security: IberodominiosAjax.nonce,
                domain: dom
            },
            success: function(response) {
                // Quitar el blur al terminar
                $row.find('.domain-wrapper').css('filter','none');
                $row.find('.loading-icon').remove();
                $row.removeClass('loading-running');
    
                if (response.success && response.data) {
                    var item = response.data;
                    var price = (item.status === 'free' && item.price) ? (item.price + ' ' + item.currency) : '-';
                    $row.find('td:nth-child(2)').text(item.status);
                    $row.find('td:nth-child(3)').text(price);
                } else {
                    $row.find('td:nth-child(2)').text('unavailable');
                    $row.find('td:nth-child(3)').text('-');
                }
                loadNextIndividual(index + 1);
            },
            error: function() {
                // En caso de error
                $row.find('.domain-wrapper').css('filter','none');
                $row.find('.loading-icon').remove();
                $row.removeClass('loading-running');
                $row.find('td:nth-child(2)').text('unavailable');
                $row.find('td:nth-child(3)').text('-');
                loadNextIndividual(index + 1);
            }
        });
    }
    

});
