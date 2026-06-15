// Open Modal Context Window
function openCommentModal(customerId, customerName) {
    $('#modalCustomerId').val(customerId);
    $('#modalCustomerName').html('Logging timeline entry note for: <strong>' + customerName + '</strong>');
    $('#commentText').val(''); // Clear old values

    // 💡 Switch this on as flex layout so inner contents render properly inside the screen viewport center
    $('#commentModal').css('display', 'flex');
}

function closeCommentModal() {
    $('#commentModal').css('display', 'none');
}

// Close Modal
function closeCommentModal() {
    $('#commentModal').css('display', 'none');
}

$(document).ready(function() {
// Intercept form submissions inside the dialogue window block
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();

        var customerId = $('#modalCustomerId').val();
        var commentStr = $('#commentText').val();

        // 💡 1. Grab the active option value from your new dropdown select box
        var selectedType = $('#commentTypeSelector').val() || 'COMMENT';

        // 💡 2. Append the &type= parameter securely to your query string line
        var submissionUrl = '?do=addComment' +
            '&customerId=' + customerId +
            '&comment=' + encodeURIComponent(commentStr) +
            '&type=' + encodeURIComponent(selectedType);

        $.ajax({
            url: submissionUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(payload) {
                closeCommentModal();
                // Redraw our isolated activity panel template snippets instantly
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
                console.error("Failed to append manual activity note record:", error);
            }
        });
    });
});

// Close Inspection Modal Window
function closeActivityModal() {
    $('#activityDetailsModal').css('display', 'none');
}

$(document).ready(function() {
    // 1. Row Click: Load values into the editable inspection window
// Catch row clicks inside your dynamic history log stream
    $(document).on('click', '.interactive-activity-item', function(e) {
        e.stopPropagation();

        // 💡 Use .attr() instead of .data() to ensure a fresh read from the DOM
        var id     = $(this).attr('data-id');
        var type   = $(this).attr('data-type') || 'LOG';
        var time   = $(this).attr('data-time') || '';
        var detail = $(this).attr('data-detail') || '';

        // Debug check: Verify the ID is no longer undefined or empty in your console
        console.log("Selected Activity ID to edit:", id);

        // Assign values to the hidden form elements inside the modal window
        $('#inspectActivityId').val(id);
        $('#inspectType').text(type);
        $('#inspectTime').text(time);
        $('#inspectDetail').val(detail);

        // Unhide modal overlay container view box
        $('#activityDetailsModal').css('display', 'flex');
    });

    // 2. Form Submission: Send modifications back via AJAX signal
    $('#updateActivityForm').on('submit', function(e) {
        e.preventDefault();

        var id        = $('#inspectActivityId').val();
        var updateStr = $('#inspectDetail').val();

        var submissionUrl = '?do=updateComment&activityId=' + id + '&detail=' + encodeURIComponent(updateStr);

        $.ajax({
            url: submissionUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(payload) {
                closeActivityModal();
                // Dynamically redraw the snippets
                if (payload && payload.snippets) {
                    for (var snippetId in payload.snippets) {
                        var $container = $('#' + snippetId);
                        if (!$container.length) {
                            $container = $('[id$="' + snippetId.replace('snippet--', '') + '"]');
                        }
                        $container.html(payload.snippets[snippetId]);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("Failed to update log comment status changes:", error);
            }
        });
    });
});
