$(document).ready(function() {
    var searchTimeout = null;

    // Search via AJAX
    function triggerFilterUpdate() {
        var $form     = $('#customerFilterForm');
        var targetUrl = $form.attr('action') || window.location.pathname;
        var formData  = $form.serialize();

        // Deselect any highlighted customer rows instantly on the UI
        $('.customer-row').removeClass('selected-row');

        $.ajax({
            url: targetUrl,
            type: 'GET',
            data: formData,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(payload) {
                if (payload && payload.snippets) {
                    for (var id in payload.snippets) {
                        var $container = $('#' + id);
                        if ($container.length) {
                            $container.html(payload.snippets[id]);
                        } else {
                            $('[id$="' + id.replace('snippet--', '') + '"]').html(payload.snippets[id]);
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("Automated customer filter update failed:", error);
            }
        });
    }

    // Instant execution when a dropdown status or sort order changes
    $('#customerFilterForm select').on('change', function() {
        triggerFilterUpdate();
    });

    // Debounced execution for typing in the text input box
    $('#customerFilterForm input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);

        // Wait 300ms after the user stops typing to make request
        searchTimeout = setTimeout(function() {
            triggerFilterUpdate();
        }, 300);
    });

    // Prevent hit of enter key inside search box from reloading the full page
    $('#customerFilterForm').on('submit', function(e) {
        e.preventDefault();
    });

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
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(payload) {
                if (payload && payload.snippets) {
                    for (var id in payload.snippets) {
                        var $container = $('#' + id);
                        if ($container.length) {
                            $container.html(payload.snippets[id]);
                        } else {
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

