jQuery(document).ready(function($) {
    var batchSize = 20; 
    var domainsToCheck = [];
    var currentBatchIndex = 0;

    $('.iberodominios-search-form').on('submit', function(e) {
        e.preventDefault();
        var domain = $(this).find('input[name="domain"]').val().trim();
        if (!domain) {
            alert('Por favor, ingresa un dominio');
            return;
        }

        // Mostrar el mismo mensaje "Cargando disponibilidad..." con SVG en cualquier caso
        $('.iberodominios-results').html(
            '<div class="iberodominios-loading-indicator">'+
            '<img src="'+IberodominiosAjax.plugin_url+'assets/svg/infinity.svg" alt="Cargando..."> '+
            'Cargando disponibilidad...</div>'
        );

        $.ajax({
            url: IberodominiosAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'iberodominios_check_domain',
                security: IberodominiosAjax.nonce,
                domain: domain
            },
            success: function(response) {
                // Quitar el indicador al recibir respuesta
                // Lo volveremos a mostrar si es modo list y necesitamos cargar batches
                $('.iberodominios-loading-indicator').remove();

                if (!response.success) {
                    $('.iberodominios-results').html('<div class="error">'+response.data.message+'</div>');
                    return;
                }

                var data = response.data;
                var html = '';

                if (data.mode === 'list') {
                    if (data.results && data.results.length > 0) {
                        // Re-mostrar el indicador
                        html += '<div class="iberodominios-loading-indicator">';
                        html += '<img src="'+IberodominiosAjax.plugin_url+'assets/svg/infinity.svg" alt="Cargando..."> Cargando disponibilidad...';
                        html += '</div>';

                        html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;">';
                        html += '<tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                        data.results.forEach(function(d){
                            domainsToCheck.push(d.domain);
                            html += '<tr data-domain="'+d.domain+'">';
                            html += '<td>'+d.domain+'</td>';
                            html += '<td class="loading-cell">...</td>';
                            html += '<td class="loading-cell">...</td>';
                            html += '</tr>';
                        });
                        html += '</table>';
                        $('.iberodominios-results').html(html);

                        loadAvailabilityInBatches();
                    } else {
                        $('.iberodominios-results').html('<p>No results found.</p>');
                    }
                } else if (data.mode === 'exact') {
                    // Mostrar resultado del dominio exacto
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
                    html += '<div class="iberodominios-loading-indicator">';
                    html += '<img src="' + IberodominiosAjax.plugin_url + 'assets/svg/infinity.svg" alt="Cargando..."> Cargando disponibilidad...';
                    html += '</div>';
                    html += '<table class="iberodominios-table" style="width:100%;border-collapse:collapse;"><tr><th>Domain</th><th>Status</th><th>Price</th></tr>';
                
                    if (data.suggestions && data.suggestions.length > 0) {
                        data.suggestions.forEach(function (s) {
                            html += '<tr data-domain="' + s.domain + '">';
                            html += '<td>' + s.domain + '</td>';
                            // Status y price se rellenarán después con AJAX batch
                            html += '<td class="loading-cell">...</td>';
                            html += '<td class="loading-cell">...</td>';
                            html += '</tr>';
                        });
                        html += '</table>';
                    } else {
                        html += '<tr><td colspan="3" style="text-align:center;">No suggestions found.</td></tr>';
                        html += '</table>';
                    }
                
                    $('.iberodominios-results').html(html);
                
                    // Ahora llamas a una función similar para el batch de estas sugerencias
                    domainsToCheck = data.suggestions.map(s => s.domain);
                    currentBatchIndex = 0; 
                    loadAvailabilityInBatches();
                }
                
            },
            error: function() {
                $('.iberodominios-results').html('<div class="error">Error desconocido al verificar el dominio.</div>');
            }
        });
    });

    function loadAvailabilityInBatches() {
        if (currentBatchIndex >= domainsToCheck.length) {
            // Todos cargados
            $('.iberodominios-loading-indicator').remove();
            return;
        }

        var end = Math.min(currentBatchIndex + batchSize, domainsToCheck.length);
        var batch = domainsToCheck.slice(currentBatchIndex, end);

        $.ajax({
            url: IberodominiosAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'iberodominios_check_batch',
                security: IberodominiosAjax.nonce,
                domains: batch
            },
            success: function(response) {
                // Aquí response.data es un objeto con { data: [...] }
                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(item) {
                        var $row = $('.iberodominios-table tr[data-domain="'+item.domain+'"]');
                        if ($row.length > 0) {
                            $row.find('td:nth-child(2)').removeClass('loading-cell').text(item.status);
                            var price = (item.status === 'free' && item.price) ? item.price + ' ' + item.currency : '-';
                            $row.find('td:nth-child(3)').removeClass('loading-cell').text(price);
                        }
                    });
                }
                
                currentBatchIndex = end;
                loadAvailabilityInBatches();
            },
            error: function() {
                currentBatchIndex = end;
                loadAvailabilityInBatches();
            }
        });
    }

});
