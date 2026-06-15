$(document).ready(function() {
    $(document).on('click', '.customer-row', function(e) {
        e.preventDefault();

        var targetUrl = $(this).data('href');
        if (!targetUrl || targetUrl === '#') return;

        $('.customer-row').removeClass('selected-row');
        $(this).addClass('selected-row');

        $.ajax({
            url: targetUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }, // Signals Nette to send back a JSON snippet payload
            success: function(payload) {
                if (payload && payload.snippets) {
                    for (var id in payload.snippets) {
                        var $container = $('#' + id);
                        if ($container.length) {
                            $container.html(payload.snippets[id]);
                        } else {
                            // Wildcard fallback pattern mapping selector if Nette alters ID structural prefixing
                            $('[id$="' + id.replace('snippet--', '') + '"]').html(payload.snippets[id]);
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX historical activity load failed:", error);
            }
        });
    });
});