
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

function addEventListenerByClass(className, event, fn) {
    var list = document.getElementsByClassName(className);
    for (var i = 0, len = list.length; i < len; i++) {
        list[i].addEventListener(event, fn, false);
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
    row.setAttribute('style', 'height: 80%; min-height: 250px;')
  var activetag =  document.createElement('DIV');
    activetag.setAttribute('class', 'col s6 tag-box');
    activetag.setAttribute('id', 'active-tag');
  var inactivetag =  document.createElement('DIV');
    inactivetag.setAttribute('class', 'col s6 tag-box');
    inactivetag.setAttribute('id', 'inactive-tag');

  var tagunit = document.createElement('DIV');
  var menuclose = document.createElement('button');
    menuclose.setAttribute('type', 'button');
    menuclose.setAttribute('class', 'closer');
    menuclose.innerHTML = 'Close';
    menuclose.addEventListener('click', function(){

    var target = document.getElementById('menucontext');
    target.parentNode.removeChild(target);
  })
  var divcont = document.createElement('DIV');
    divcont.setAttribute('class', 'bot-divcont col s6');
  var validator = document.createElement('button');
    validator.setAttribute('type', 'button');
    validator.setAttribute('class', 'validator');
    validator.innerHTML = 'Validate';
    validator.addEventListener('click', function()
    {
      var acnl = document.getElementById('active-tag').childNodes;
      var innl = document.getElementById('inactive-tag').childNodes;
      var xhr2 = new XMLHttpRequest();
      xhr2.onreadystatechange = function() {
      if (xhr2.readyState == 4 && (xhr2.status == 200 || xhr2.status == 0)) {
        //console.log(JSON.parse(xhr2.responseText));
      }};

      var actives = [];
      for(var i = 0, len = acnl.length; i < len; i++) {
        actives.push({id_tag:acnl[i].id, name: acnl[i].innerText});
      }
      var inactives = [];
      for(var i = 0, len = innl.length; i < len; i++) {
          inactives.push({id_tag:innl[i].id, name: innl[i].innerText});
      }
      xhr2.open("POST", "updateTagInfo", true);
      xhr2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr2.send("subject=tagupdt&activeTag=" + JSON.stringify(actives) + "&inactiveTag=" + JSON.stringify(inactives));
    });
  var border = document.createElement('DIV');
    border.setAttribute('class', 'border col s3');
  var border2 = border.cloneNode();

  row.append(activetag);
  row.append(inactivetag);
  row2.append(border);
  row2.append(divcont);
  divcont.append(validator);
  divcont.append(menuclose);
  row2.append(border2);
  menucontext.append(row);
  menucontext.append(row2);

  mainbox['0'].prepend(menucontext);

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
  if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
    var data = JSON.parse(xhr.responseText);
    if(data['active']) {
      data['active'].forEach(function (item, index) {
        activetag.innerHTML = activetag.innerHTML + "<div class='tagitem chip' id='tagitem"+item['id_tag']+"'>"+item['name_tag']+"<i class='material-icons'></i></div>"
      });}
    if(data['inactive']) {
      data['inactive'].forEach(function (item, index) {
        inactivetag.innerHTML = inactivetag.innerHTML + "<div class='tagitem chip' id='tagitem"+item['id_tag']+"'>"+item['name_tag']+"<i class='material-icons'></i></div>"
      });}
    addEventListenerByClass('tagitem', 'click', function (event) {
      parent = event.target.parentNode;
      if(parent.id == "inactive-tag") {
        document.getElementById('active-tag').append(event.target);
      }
      if(parent.id == "active-tag") {
        document.getElementById('inactive-tag').append(event.target);
      }
    });
  }};
  xhr.open("POST", "getTagInfo", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("subject=tagmbt");
}

function ShowMap() {
  var map = document.getElementById('map');
  if(map.style.visibility == 'hidden') {
  map.style.visibility = 'visible';}
  else {
    map.style.visibility= 'hidden';
  }
}

function initMap() {
  var uluru = {lat: -25.363, lng: 131.044};
  var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 12,
      center: uluru
   });
       var marker = new google.maps.Marker({
         position: uluru,
         map: map
 });
}
