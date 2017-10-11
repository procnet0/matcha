
function setAsProfil(ev) {

  var active = document.getElementsByClassName('carousel-item active');
  var link = '';
  if(active && active['0']) {var link = active['0'].firstChild.src;}
   var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
  		if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {

        var div = document.getElementById('set_answer');
        var newo = false;
        if (!div)
        {
          newo = true;
          var div = document.createElement('div');
          div.setAttribute('class', 'alertdiv');
          div.setAttribute('id', 'set_answer');
        }
        if(xhr.responseText == true){
          div.innerHTML = 'Profile pict updated';
        }
        else {
          div.innerHTML = '';
        }
        if(newo = true)
        {
            document.getElementById('pict_selector').parentElement.appendChild(div);
        }}};

    xhr.open("POST", "setAsProfil", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("profil_pict="+ encodeURIComponent(link));
}

function getBase64(file) {
   var reader = new FileReader();
   reader.readAsDataURL(file);
   reader.onload = function () {
     return (reader.result);
   };
   reader.onerror = function (error) {
     console.log('Error: ', error);
   };
}

function AddOrChangePicture(ev) {
  ev.preventDefault();

  var newone = ev.target.files['0'];
  var active = document.getElementsByClassName('carousel-item active');
  if(newone && newone.type.match('image/*')) {
    var reader = new FileReader();
    reader.readAsDataURL(newone);
    reader.onload = function () {
      link = reader.result;
      var old = '';
      if(active && active['0'])  {old = active['0'].firstChild.src;}
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
          //console.log(xhr.responseText);
          var data = JSON.parse(xhr.responseText);
          if(data['status'] != "Invalid file.") {

          var assoc = { "1": "#one!","2": "#two!","3": "#three!","4": "#four!","5": "#five!"}
          var carousel = document.getElementById('carousel');

          var child = document.getElementsByClassName('carousel-item');
          var elem = document.createElement('a');
          elem.setAttribute('class', 'carousel-item');
          elem.innerHTML= '<img src=\"' + data['src'] +'\">';
          elem.setAttribute('href', assoc[data['number']]);
          if(child.length < 5)
          {
          carousel.appendChild(elem);
          }
          else {
          child[data['number']- 1].replaceWith(elem);
          }
          $('.carousel').removeClass('initialized');
          $('.carousel').carousel();
          }
         }
        };
        xhr.open("POST", "updateAccountPict", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("newone="+ encodeURIComponent(link)+"&old="+ encodeURIComponent(old));
      };
      reader.onerror = function (error) {
      console.log('Error: ', error);
        };
    }
}

function OpenTagMenu() {

  var menucontext = document.createElement('DIV');
  window.addEventListener('resize', function() {
    var menucontext = document.getElementById('menucontext');
    var mainbox = document.getElementsByTagName('main');
    menucontext.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+ mainbox['0'].offsetWidth+'px;');
  })


  var mainbox = document.getElementsByTagName('main');
  menucontext.setAttribute('class', 'container section menucontext');
  menucontext.setAttribute('id', 'menucontext');
  menucontext.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+ mainbox['0'].offsetWidth+'px;');

  var row = document.createElement('DIV');
  row.setAttribute('class', 'row');
  var row2 = row.cloneNode(false);

  var activetag =  document.createElement('DIV');
  activetag.setAttribute('class', 'col s6');
  activetag.setAttribute('id', 'active-tag');


  var inactivetag =  document.createElement('DIV');
  inactivetag.setAttribute('class', 'col s6');
  inactivetag.setAttribute('id', 'inactive-tag');

  var menuclose = document.createElement('button');
  menuclose.setAttribute('type', 'button');
  menuclose.setAttribute('class', 'closer');
  menuclose.innerHTML = 'Close';
  menuclose.addEventListener('click', function(){
    var target = document.getElementById('menucontext');
    target.parentNode.removeChild(target);
  })



  var validator = document.createElement('button');
  validator.setAttribute('type', 'button');
  validator.setAttribute('class', 'validator');
  validator.innerHTML = 'Validate';
  validator.addEventListener('click', function(){
    var active = document.getElementById('active-tag');
    var inactive = document.getElementById('inactive-tag');
  })


  row.append(activetag);
  row.append(inactivetag);

  row2.append(menuclose);
  row2.append(validator);
  menucontext.append(row);
  menucontext.append(row2);

  mainbox['0'].prepend(menucontext);
}
