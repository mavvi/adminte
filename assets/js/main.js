$(document).ready(function () {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });

    // Dropdown menü için hover efekti
    $('.dropdown-toggle').hover(function() {
        $(this).next('.dropdown-menu').stop(true, true).delay(250).fadeIn(250);
    }, function() {
        $(this).next('.dropdown-menu').stop(true, true).delay(250).fadeOut(250);
    });

    // Mobil görünümde sidebar'ı otomatik gizle
    if (window.innerWidth <= 768) {
        $('#sidebar').addClass('active');
    }

    // Pencere boyutu değiştiğinde sidebar'ı kontrol et
    $(window).resize(function() {
        if (window.innerWidth <= 768) {
            $('#sidebar').addClass('active');
        } else {
            $('#sidebar').removeClass('active');
        }
    });

    // Tooltip'leri aktifleştir
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
}); 