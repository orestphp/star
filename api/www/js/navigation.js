$(document).ready(function() {
    var currentPath = window.location.pathname.toLowerCase().replace(/\/+$/, "");

    if (currentPath === '' || currentPath === '/' || currentPath === '/index.php') {
        $('#dashboard-nav .nav-link').removeClass('active');
    } else {
        $('#dashboard-nav .nav-link').each(function() {
            var linkPath = this.pathname.toLowerCase().replace(/\/+$/, "");
            if (currentPath === linkPath || (linkPath !== "" && currentPath.indexOf(linkPath) !== -1)) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    }
});