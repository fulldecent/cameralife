var http_request = false;
var callback = false;
var response;
function makeRequest(url, formid, callme) {
    http_request = false;
    callback = callme;
    if (window.XMLHttpRequest) {
        http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
            http_request.overrideMimeType('text/xml');
        }
    } else if (window.ActiveXObject) {
        try {
            http_request = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
            try {
                http_request = new ActiveXObject('Microsoft.XMLHTTP');
            } catch (e) {
            }
        }
    }
    if (!http_request) {
        alert('Giving up :( Cannot create an XMLHTTP instance');
        return false;
    }
    var inputs;
    var posts = [];
    var post = '';
    var children = document.getElementById(formid).getElementsByTagName('input');
    for (var j = 0, child; child = children[j]; j++) {
        if (child.name && child.value) {
            if (child.name == 'target') { 
                posts.push('target=ajax'); }
            else { 
                posts.push(child.name + '=' + encodeURI(child.value)); }
        }
    }
    http_request.onreadystatechange = alertContents;
    http_request.open('POST', url, true);
    http_request.setRequestheader('Content-Type', 'application/x-www-form-urlencoded');
    http_request.send(posts.join('&'));
}
function alertContents() {
    if (http_request.readyState == 4) {
        if (http_request.status == 200) {
            response = http_request.responseXML;
            setTimeout(callback, 0);
        } else {
            alert('There was a problem with the request.');
        }
    }
}