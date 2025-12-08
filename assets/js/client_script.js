$(document).ready(function() {
    $('#nav-item-bars').on('click', function() {

        var body = $('body');
        if (body.hasClass('sidebar-collapse')) {
            $('#profile-image').show();
            $('#profile-image-small').hide();
        } else {
            $('#profile-image').hide();
            $('#profile-image-small').show();

        }

    });
});