function toggleshowrename(){
  if (document.getElementById('rename').style.display==''){
    document.getElementById('rename').style.display='none';
  }
  else{
    document.getElementById('rename').style.display='';
    focusTo($id('formtitle'),0);
  }
  return false;
}

function overstar(num){
  var box = document.getElementById("rating")
  var buttons = box.getElementsByTagName("img")
  for (var i=0, j=buttons.length; i<j; i++)
  {
    var x = buttons[i].src
    x = x.replace(/unlit$/, "lit")
    if (i>=num)
    x = x.replace(/lit$/, "unlit")

    buttons[i].src = x
  }
}

function nostar(){
  var box = document.getElementById("rating")
  var buttons = box.getElementsByTagName("img")
  for (var i=0, j=buttons.length; i<j; i++)
  {
    var x = buttons[i].src
    x = x.replace(/unlit$/, "lit")
    if (i>=rating)
    x = x.replace(/lit$/, "unlit")
    buttons[i].src = x
  }
}

function fadein(porig, pnext){
  document.getElementById('prevphoto').style.display='none'
  document.getElementById('nextphoto').src=document.getElementById('nextphoto').src.replace(/thumbnail/,"scaled")

  a = document.getElementById('curphoto')
  b = document.getElementById('nextphoto')

  a.width = porig*.10 + a.width*.90 - 5
  b.width = pnext*4*.10 + b.width*.90 + 5

//  a.style.width = a.width - 10
  if (a.width <= porig){
    a.style.width = porig
    b.style.width = pnext
    window.location = b.parentNode.getAttribute('href')
  }else{
    setTimeout("fadein("+porig+","+pnext+")", 100)
  }
}

document.onkeyup = function(e) { // key pressed
    if(document.activeElement.nodeName === "INPUT"
    || document.activeElement.nodeName === "TEXTAREA") {
        return; // abort if focusing input box
    }

    var elems = document.getElementsByTagName("link"),
        links = {};

    for(var i = 0; i < elems.length; i++) { // filter link elements
        var elem = elems[i];
        if(elem.rel === "prev") { // add prev to links object
            links.prev = elem;
        } else if(elem.rel === "next") { // ad next to links object
            links.next = elem;
        }
    }

    if(e.keyCode === 37) { // left key
        location.href = links.prev.href;
    } else if(e.keyCode === 39) { // right key
        location.href = links.next.href;
    }
};
