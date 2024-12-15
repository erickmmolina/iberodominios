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
                    if (offset === 0) {
                        // Crear tabla (aunque no haya resultados, por consistencia)
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

                } else if (data.mode === 'exact') {
                    if (offset === 0) {
                        // Mostrar resultado exacto
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
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(function(s) {
                            $table.append(createDomainRow(s.domain));
                            domainsToCheck.push(s.domain);
                        });
                    }

                    if (data.has_more) {
                        $('.iberodominios-results').append('<button class="iberodominios-load-more">Cargar más</button>');
                        $('.iberodominios-load-more').off('click').on('click', function() {
                            $(this).remove();
                            $('.iberodominios-results').append(
                                '<div class="iberodominios-loading-indicator">' +
                                '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> ' +
                                'Cargando más sugerencias...</div>'
                            );
                            offset += limit;
                            loadDomains(currentDomain, offset, limit);
                        });
                    }

                    loadAllIndividual();
                }

            },
            error: function() {
                $('.iberodominios-results').html('<div class="error">Error desconocido al verificar el dominio.</div>');
            }
        });
    }

    function createDomainRow(domain) {
        // Ahora solo se muestra el dominio con blur y un icono de carga sobre él.
        var svg = '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..." style="width:16px;height:16px;vertical-align:middle;margin-right:5px;">';
        // Agregamos una clase loading a la fila o a la celda del dominio
        // Le ponemos blur a la celda del dominio hasta que cargue
        var row = '<tr data-domain="' + domain + '">' +
                  '<td class="domain-loading" style="position:relative;filter:blur(1px);">' + domain + 
                  '<span class="loading-icon" style="position:absolute;left:5px;top:5px;">' + svg + '</span>' +
                  '</td>' +
                  '<td>-</td>' +
                  '<td>-</td>' +
                  '</tr>';
        return row;
    }

    function loadAllIndividual() {
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
                var $row = $('.iberodominios-table tr[data-domain="' + dom + '"]');
                if (response.success && response.data) {
                    var item = response.data;
                    if ($row.length > 0) {
                        // Quitar blur y el icono de carga del dominio
                        $row.find('td.domain-loading').css('filter','none').find('.loading-icon').remove();
                        // Actualizar estado y precio
                        $row.find('td:nth-child(2)').text(item.status);
                        var price = (item.status === 'free' && item.price) ? (item.price + ' ' + item.currency) : '-';
                        $row.find('td:nth-child(3)').text(price);
                    }
                } else {
                    // Error o sin data, marcar como unavailable
                    if ($row.length > 0) {
                        $row.find('td.domain-loading').css('filter','none').find('.loading-icon').remove();
                        $row.find('td:nth-child(2)').text('unavailable');
                        $row.find('td:nth-child(3)').text('-');
                    }
                }
                loadNextIndividual(index + 1);
            },
            error: function() {
                var dom = domainsToCheck[index];
                var $row = $('.iberodominios-table tr[data-domain="' + dom + '"]');
                if ($row.length > 0) {
                    $row.find('td.domain-loading').css('filter','none').find('.loading-icon').remove();
                    $row.find('td:nth-child(2)').text('unavailable');
                    $row.find('td:nth-child(3)').text('-');
                }
                loadNextIndividual(index + 1);
            }
        });
    }

});
