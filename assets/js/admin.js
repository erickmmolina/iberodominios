jQuery(document).ready(function($) {
    // Activar sortable en la lista de populares
    $('#popular-tlds-list').sortable();

    $('#tld-select').select2({
        placeholder: 'Busca una TLD...',
        ajax: {
            url: IberodominiosAdmin.ajax_url,
            dataType: 'json',
            delay: 250,
            type: 'POST', 
            data: function(params) {
                return {
                    action: 'iberodominios_search_tlds',
                    q: params.term,
                    nonce: IberodominiosAdmin.nonce
                };
            },
            processResults: function(res) {
                // Aqui esperamos res.data sea un array simple
                if (res.success && Array.isArray(res.data)) {
                    var results = res.data.map(function(t) {
                        return { id: t, text: t };
                    });
                    return { results: results };
                } else {
                    return { results: [] };
                }
            }
        }
    });

    $('#tld-select').on('select2:select', function(e) {
        var tld = e.params.data.id;
        $('#popular-tlds-list').append('<li style="cursor:move;">'+tld+'<input type="hidden" name="iberodominios_popular_tlds[]" value="'+tld+'"></li>');
        $(this).val(null).trigger('change');
    });
});
