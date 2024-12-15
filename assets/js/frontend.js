jQuery(document).ready(function($) {
    var offset = 0;
    var limit = 100;
    var currentMode = null;
    var currentDomain = null;
    var domainsToCheck = [];

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

                var html = '';

                if (data.mode === 'list') {
                    // Si offset = 0, creamos la tabla, si no, añadimos filas
                    if (offset === 0) {
                        if (data.results && data.results.length > 0) {
                            html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;">';
                            html += '<tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                            html += '</table>';
                            $('.iberodominios-results').html(html);
                        } else {
                            $('.iberodominios-results').html('<p>No results found.</p>');
                            return;
                        }
                    }

                    // Añadir filas
                    var $table = $('.iberodominios-table');
                    data.results.forEach(function(d) {
                        $table.append(createDomainRow(d.domain));
                        domainsToCheck.push(d.domain);
                    });

                    if (data.has_more) {
                        $('.iberodominios-results').append('<button class="iberodominios-load-more">Cargar más</button>');
                        $('.iberodominios-load-more').off('click').on('click', function() {
                            $(this).remove();
                            offset += limit;
                            $('.iberodominios-results').append(
                                '<div class="iberodominios-loading-indicator">' +
                                '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> ' +
                                'Cargando más resultados...</div>'
                            );
                            loadDomains(currentDomain, offset, limit);
                        });
                    }

                    // Ahora cargamos estado/precio uno a uno
                    loadAllIndividual();

                } else if (data.mode === 'exact') {
                    if (offset === 0) {
                        // Resultado exacto
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
                        html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;"><tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                        html += '</table>';
                        $('.iberodominios-results').html(html);
                    }

                    var $table = $('.iberodominios-table');
                    data.results.forEach(function(s) {
                        $table.append(createDomainRow(s.domain));
                        domainsToCheck.push(s.domain);
                    });

                    if (data.has_more) {
                        $('.iberodominios-results').append('<button class="iberodominios-load-more">Cargar más</button>');
                        $('.iberodominios-load-more').off('click').on('click', function() {
                            $(this).remove();
                            offset += limit;
                            $('.iberodominios-results').append(
                                '<div class="iberodominios-loading-indicator">' +
                                '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> ' +
                                'Cargando más sugerencias...</div>'
                            );
                            loadDomains(currentDomain, offset, limit);
                        });
                    }

                    // Cargar estado/precio uno a uno
                    loadAllIndividual();
                }

            },
            error: function() {
                $('.iberodominios-results').html('<div class="error">Error desconocido al verificar el dominio.</div>');
            }
        });
    }

    function createDomainRow(domain) {
        // Fila inicial con indicadores por separado
        var svg = '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..." style="width:16px;height:16px;vertical-align:middle;">';
        var row = '<tr data-domain="' + domain + '">' +
                  '<td>' + domain + '</td>' +
                  '<td class="loading-cell">' + svg + ' Cargando estado</td>' +
                  '<td class="loading-cell">' + svg + ' Cargando precio</td>' +
                  '</tr>';
        return row;
    }

    function loadAllIndividual() {
        // Ahora hacemos llamadas AJAX individuales para cada dominio
        // domainsToCheck ya contiene todos los dominios
        if (domainsToCheck.length === 0) return;
        loadNextIndividual(0);
    }

    function loadNextIndividual(index) {
        if (index >= domainsToCheck.length) {
            return; // completado
        }

        var dom = domainsToCheck[index];
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
                if (response.success && response.data) {
                    var item = response.data;
                    var $row = $('.iberodominios-table tr[data-domain="' + item.domain + '"]');
                    if ($row.length > 0) {
                        $row.find('td:nth-child(2)').removeClass('loading-cell').text(item.status);
                        var price = (item.status === 'free' && item.price) ? (item.price + ' ' + item.currency) : '-';
                        $row.find('td:nth-child(3)').removeClass('loading-cell').text(price);
                    }
                } else {
                    // Si falla, marcamos como unavailable
                    var $row = $('.iberodominios-table tr[data-domain="' + dom + '"]');
                    if ($row.length > 0) {
                        $row.find('td:nth-child(2)').removeClass('loading-cell').text('unavailable');
                        $row.find('td:nth-child(3)').removeClass('loading-cell').text('-');
                    }
                }
                loadNextIndividual(index + 1);
            },
            error: function() {
                // En caso de error, marcamos unavailable
                var $row = $('.iberodominios-table tr[data-domain="' + dom + '"]');
                if ($row.length > 0) {
                    $row.find('td:nth-child(2)').removeClass('loading-cell').text('unavailable');
                    $row.find('td:nth-child(3)').removeClass('loading-cell').text('-');
                }
                loadNextIndividual(index + 1);
            }
        });
    }

});
