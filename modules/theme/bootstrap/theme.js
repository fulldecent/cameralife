$(document).keydown(function (e) {
    if (document.activeElement.nodeName === 'INPUT' || document.activeElement.nodeName === 'TEXTAREA') {
        return;
    }
    if (e.keyCode == 37) {
        href = $('link[rel=\'prev\']').prop('href');
        if (href) { 
            location.href = href; }
        return false;
    } else if (e.keyCode == 39) {
        href = $('link[rel=\'next\']').prop('href');
        if (href) { 
            location.href = href; }
        return false;
    }
});
$('body').on('swipeleft', function () {
    href = $('link[rel=\'next\']').prop('href');
    if (href) { 
        location.href = href; }
    return false;
});
$('body').on('swiperight', function () {
    href = $('link[rel=\'prev\']').prop('href');
    if (href) { 
        location.href = href; }
    return false;
});