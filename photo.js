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