// ==========================================
// 1. Trigger Modals Open / Close Operations
// ==========================================

/**
 * Modal A: New Parent Activity Logging Window
 */
function openActivityModal(customerId, customerName) {
    $('#modalCustomerId').val(customerId);
    $('#modalCustomerName').html('<strong>' + escapeHtml(customerName) + '</strong>');
    $('#commentText').val(''); // Clear old inputs
    $('#activityModal').css('display', 'flex');
}

function closeActivityModal() {
    $('#activityModal').css('display', 'none');
}

/**
 * Modal B: Activity Details & Its Inner Comments Feed List
 */
function openActivityCommentsModal() {
    $('#activityDetailsModal').css('display', 'flex');
}

function closeActivityCommentsModal() {
    $('#activityDetailsModal').css('display', 'none');
    $('#newSubCommentText').val(''); // Clear input out cleanly
}

// Global utility helper to neutralize content injection layout attacks
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// ==========================================
// 2. Core Interactive State Management
// ==========================================
$(document).ready(function() {

    // A. Customer Level Base Logging Form (Main Panel Action)
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();

        var customerId   = $('#modalCustomerId').val();
        var commentStr   = $('#commentText').val();
        var selectedType = $('#commentTypeSelector').val() || 'COMMENT';
        var token        = $('#commentForm').attr('data-token') || $('#commentForm').data('token');

        var submissionUrl = '?do=addComment' +
            '&customerId=' + customerId +
            '&comment=' + encodeURIComponent(commentStr) +
            '&type=' + encodeURIComponent(selectedType) +
            '&_sec=' + encodeURIComponent(token);

        $.ajax({
            url: submissionUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(payload) {
                closeActivityModal();
                // Process clean framework snippet redraw update iterations
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

    // B. Consolidated Single Click Handler for Activity Rows (Launches Split Screen Thread View)
    $(document).on('click', '.interactive-activity-item', function(e) {
        e.stopPropagation();

        var $el        = $(this);
        var activityId = $el.attr('data-id') || $el.data('id');
        var type       = $el.attr('data-type') || 'LOG';
        var time       = $el.attr('data-time') || '';
        var detail     = $el.attr('data-detail') || '';

        // Safely project data targets directly into Thread Context displays
        $('#inspectActivityId').val(activityId);
        $('#inspectType').text(type);
        $('#inspectTime').text(time);
        $('#inspectDetailDisplay').html(detail);

        // Flash progress loading placeholder rows ahead of async fetch parsing sequences
        $('#modalCommentsFeed').html('<p style="color: #9ca3af; font-size: 0.85rem; font-style: italic; text-align: center; margin: auto; padding: 1rem;">Loading conversation entries...</p>');

        // show the modal overlay
        openActivityCommentsModal();

        // Fire nested data lookup fetch routine
        fetchCommentsForActivity(activityId);
    });

    // C. Internal Sub-thread Message Payload Lookup Fetch Loop
    function fetchCommentsForActivity(activityId) {
        $.ajax({
            url: '?do=getComments',
            type: 'GET',
            data: { activityId: activityId },
            dataType: 'json',
            success: function(data) {
                var container = $('#modalCommentsFeed');
                container.empty();

                if (data.comments && data.comments.length > 0) {
                    $.each(data.comments, function(idx, c) {
                        var commentHtml = `
                            <div style="background: #f3f4f6; padding: 0.6rem 0.8rem; border-radius: 6px; font-size: 0.85rem; color: #374151;">
                               <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.75rem; color: #9ca3af; font-weight: 500;">
                                  <span><i class="fa-solid fa-user-tie"></i> Operator #${c.user_id}</span>
                                  <span>${c.created_at}</span>
                               </div>
                               <div style="word-break: break-word; line-height: 1.3;">${escapeHtml(c.text)}</div>
                            </div>`;
                        container.append(commentHtml);
                    });
                    container.scrollTop(container[0].scrollHeight);
                } else {
                    container.html('<p style="color: #9ca3af; font-size: 0.85rem; font-style: italic; text-align: center; margin: auto; padding: 1rem;">No comments found matching this Activity.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Failed to query inner message payload arrays:", error);
            }
        });
    }

    // D. Sub-thread Inline Text Creation Form Submission Processing Logic
    $('#addActivityCommentForm').on('submit', function(e) {
        e.preventDefault();

        var activityId = $('#inspectActivityId').val();
        var txt        = $('#newSubCommentText').val();

        // Safe extraction approach to guarantee security token delivery survival lines
        var token      = $('#commentForm').attr('data-token') || $('#commentForm').data('token');

        if (!txt.trim()) return;

        $.ajax({
            url: '?do=addActivityComment',
            type: 'POST',
            data: {
                activityId: activityId,
                text: txt,
                _sec: token
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#newSubCommentText').val(''); // Safe textarea reset assignment
                    fetchCommentsForActivity(activityId); // Auto redraw tracking nodes inline seamlessly!
                } else {
                    alert(res.error || 'Unable to store thread message mapping error.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Critical API submission intercept layout crash:", error);
            }
        });
    });
});