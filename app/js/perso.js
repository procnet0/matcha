
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
        console.log(link);
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
    if(menucontext){
    menucontext.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+ mainbox['0'].offsetWidth+'px;');
    }
  })
  var mainbox = document.getElementsByTagName('main');
    menucontext.setAttribute('class', 'section menucontext');
    menucontext.setAttribute('id', 'menucontext');
    menucontext.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+ mainbox['0'].offsetWidth+'px;');
  var row = document.createElement('DIV');
    row.setAttribute('class', 'row');
  var row2 = row.cloneNode(false);
    row.setAttribute('style', 'height: 80%; min-height: 250px;')
  var activetag =  document.createElement('DIV');
    activetag.setAttribute('class', 'col s12 tag-box');
    activetag.setAttribute('id', 'active-tag');

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
      var xhr2 = new XMLHttpRequest();
      xhr2.onreadystatechange = function() {
      if (xhr2.readyState == 4 && (xhr2.status == 200 || xhr2.status == 0)) {
      //  console.log(JSON.parse(xhr2.responseText));
      }};

      var actives = [];
      for(var i = 0, len = acnl.length; i < len; i++) {

        if( acnl[i].className.indexOf("tagitem") === -1 ) {
          continue;
        }
        actives.push({id_tag:acnl[i].id, name: acnl[i].innerText});
      }
      xhr2.open("POST", "updateTagInfo", true);
      xhr2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr2.send("subject=tagupdt&activeTag=" + JSON.stringify(actives));
    });
  var border = document.createElement('DIV');
    border.setAttribute('class', 'border col s3');
  var border2 = border.cloneNode();
  var autosearch = document.createElement('DIV');
    autosearch.setAttribute('class', 'input-field tagselector col s6 offset-s3');
    autosearch.setAttribute('id', 'auto-tag');
    autosearch.innerHTML = '<i class="material-icons prefix">textsms</i><input type="text" id="autocomplete-input" class="autocomplete"><label for="autocomplete-input">Choose tag</label><button type="button" id="tagselectbut">send</button>'


  row.append(activetag);
  row2.append(border);
  row2.append(divcont);
  activetag.append(autosearch);
  divcont.append(validator);
  divcont.append(menuclose);
  row2.append(border2);
  menucontext.append(row);
  menucontext.append(row2);

  mainbox['0'].prepend(menucontext);

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
  if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
    var dat = JSON.parse(xhr.responseText);
    if(dat['active']) {
      dat['active'].forEach(function (item, index) {
        activetag.innerHTML = activetag.innerHTML + "<div class='tagitem chip' id='tagitem"+item['id_tag']+"'>"+item['name_tag']+"<i class='material-icons'></i></div>"
      });}
      console.log(dat['taglist']);
      str = '{';
      dat['taglist'].forEach(function (item, index) {
        str += '"' + item['name_tag'] + '" : null ,';
      });
      str = str.substring(0,str.length -1);
      str += '}';

      console.log(str);
        $('#autocomplete-input').autocomplete({
          data: JSON.parse(str),
          limit: 20,
          onAutocomplete: function(val) {

          },
          minLength: 1,
        });
    addEventListenerByClass('tagitem', 'click', function (event) {
      event.target.parentElement.removeChild(event.target);
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

function updateMap(data) {
  if(typeof data.geoplugin_latitude !== 'undefined' && typeof data.geoplugin_longitude !== 'undefined')
  {
    var pos = {lat: parseFloat(data.geoplugin_latitude), lng: parseFloat(data.geoplugin_longitude)};
  }
  else {
    var pos = {lat: data.coords.latitude, lng: data.coords.longitude};
  }
  if(document.getElementById('latitude') && document.getElementById('longitude'))
  {
    document.getElementById('latitude').value = pos['lat'];
    document.getElementById('longitude').value = pos['lng'];
  }
  var map = new google.maps.Map(document.getElementById('map'), {
    center: {lat: 48.895443, lng: 2.318076},
    zoom: 15
  });
  var marker = new google.maps.Marker({position: pos,map: map,title: 'You'});
   map.setCenter(pos);
}

function initMap() {
   // Try HTML5 geolocation.
   if (navigator.geolocation) {
     navigator.geolocation.getCurrentPosition(updateMap,function (data) {
       $.getJSON("http://www.geoplugin.net/json.gp?jsoncallback=?",updateMap);
   });
 }
   // Else get from api.
   else {
     $.getJSON("http://www.geoplugin.net/json.gp?jsoncallback=?",updateMap);
   }
 }

function updateLocation() {
   var input = document.getElementById('geoloc');
   if(input !== 'undefined') {
     input = input.value;
   }
   else if (document.getElementById('latitude') !== 'undefined' && document.getElementById('longitude') !== 'undefined') {
     var data = [];
     data.push({ latitude:document.getElementById('latitude').value , longitude: document.getElementById('longitude').value});

   }
   var xhr = new XMLHttpRequest();
   xhr.onreadystatechange = function() {
     if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
       var data = xhr.responseText;
       //var data = JSON.parse(xhr.responseText);
       console.log(data);
     }
   }
     xhr.open("POST", "updatePosition", true);
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    if(input !== 'undefined') {
     xhr.send("input="+ encodeURIComponent(input));
     console.log(input);
    }
    else if(data !== 'undefined') {
      xhr.send("lng="+ encodeURIComponent(data['longitude']) + "&lat="+ encodeURIComponent(data['latitude']));
      console.log(data);
    }
 }

function initsliders() {
  var agepicker = document.getElementById('age-picker');
  var rangepicker = document.getElementById('range-picker');
  var rangepop = document.getElementById('range-popularity');
  noUiSlider.create(agepicker, {
    start: [ 18, 100 ],
    behaviour: 'snap',
    connect: true,
    tooltips: [ wNumb({ decimals: 0 ,step: 1}), wNumb({ decimals: 0 ,step: 1})],
    range: {
      'min': 18,
      'max': 100
    },
    format: wNumb({
      decimals: 0
    })
  });


  noUiSlider.create(rangepicker, {
    start: 5,
    behaviour: 'snap',
    connect: [true,false],
    tooltips: [wNumb({ decimals: 0 ,step: 1})],
    range: {
      'min': 5,
      'max': 100
    },
    format: wNumb({
      decimals: 0
    })
  });

    noUiSlider.create(rangepop, {
      start: [ 0, 100 ],
      behaviour: 'snap',
      connect: true,
      tooltips: [wNumb({ decimals: 0 ,step: 1}),wNumb({ decimals: 0 ,step: 1})],
      range: {
        'min': 0,
        'max': 100
      },
      format: wNumb({
        decimals: 0
      })
    });
}

function Tagchooserdisplay(targetname) {
  var contain = document.getElementById(targetname);
  if(contain.style.visibility != 'visible') {
  contain.style.visibility = 'visible';
  }
  else {
    contain.style.visibility = 'collapse';
  }
}

function manageactivity(name_tag, ev) {

 var clas = ev.target.className;
  if(clas.search('activated') == -1) {
  ev.target.setAttribute('class', ev.target.className + ' activated');
  ev.target.style.background = 'radial-gradient(circle at center, transparent ,  rgba(0,255,0, 0.8) 76%, transparent)';
  }
  else {
    ev.target.className = clas.replace(' activated', '');
    ev.target.style.background = 'radial-gradient(circle at center, transparent ,  rgba(255,255,255, 0.8) 76%, transparent)';
  }
}

function activefilter(filter_name) {
  console.log(filter_name);

}

var extracted = 0;

function startsearch(status) {
  if(status === 'new') {
    extracted = 0;
    document.getElementById('search_content_area').innerHTML = '';
  }
  var age = document.getElementById('age-picker');
  var range = document.getElementById('range-picker');
  var rangeorigin = document.getElementById('range-origin');
  var pop = document.getElementById('range-popularity');
  var tags = document.getElementsByClassName('tagitem activated');

  var actives = [];
  if(tags) {
    for (var tag in tags) {
      if (tags.hasOwnProperty(tag)) {
      actives.push(tags[tag].innerHTML.replace(/ |\n|\r/g, ""));
      console.log(actives);
      }
    }
  }
  if(age && range && pop)
  {
    age = age.noUiSlider.get();
    range =  range.noUiSlider.get();
    pop = pop.noUiSlider.get();
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({'address': rangeorigin.value}, function(results, status) {
      if(status === 'OK') {
          var address = [];

          address.push({'lat' :results['0'].geometry.location.lat()});
          address.push({'lng' : results['0'].geometry.location.lng()});
          address.push({'address' : results['0']['formatted_address']});
          address = JSON.stringify(address);
      }
      else {
        var address = [];
      }
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
        var data = JSON.parse(xhr.responseText);
        var num = extracted;
        var numtmp = 0;
        var connect = '';
        extracted += data['extracted'];
        var resultzone = document.getElementById('search_content_area');
        data['result'].forEach(function (element) {
          var newelem = document.createElement('DIV');
          num += 1;
          var tag = data['result'][numtmp]['tags'];
          if(tag !== null){
            nbtag = tag.split(",").length;
          }
          else {
            nbtag = 0;
          }

          if(data['online'][data['result'][numtmp]['id_user']] == 'yes') {
            connect = "<i class='material-icons green-text'>lens</i>";
          }
          else {
            connect = "<i class='material-icons red-text'>lens</i>";
          }
          newelem.innerHTML = "<div class='row' id='num"+num+"'><div class='col s2 miniProfilPict'><img src='"+data['result'][numtmp]['profil_pict']+"' class='miniProfilPict'><div class='flex'><p class='nameContainer'>"+data['result'][numtmp]['prenom']+" "+data['result'][numtmp]['nom'].substring(0,1)+". </p>"+connect+"</div></div><div class='col s2'>"+data['result'][numtmp]['age']+" </div><div class='col s2'>"+ data['result'][numtmp]['dist']+'</div><div class="col s2">'+ data['result'][numtmp]['score']+'</div><div class="col s1"> '+ data['result'][numtmp]['nb'] +"</div><div class='col s1'><a href='/matcha/lookat/"+data['result'][numtmp]['login']+"'><i class='material-icons'>unfold_more</i></a></div></div>";

          numtmp += 1;
          resultzone.appendChild(newelem);
        });
        console.log(data);
      }
    }
    xhr.open("POST", "recherche", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("age="+ encodeURIComponent(age) +"&range="+ encodeURIComponent(range)+ "&pop="+encodeURIComponent(pop)+"&area="+encodeURIComponent(address)+"&tags="+encodeURIComponent(actives)+"&extracted="+encodeURIComponent(extracted));
    });
  }
}

function sortresult(action,ev) {
  var targ = ev.target;
  if(targ.nodeName == 'I') {
    var inner = ev.target.innerText;
    if(inner.search('down') != -1) {
      ev.target.innerText = inner.replace('down' , 'up'); }
    else {
    ev.target.innerText = inner.replace('up' , 'down');
    }
  }

}

function likeuser(login, ev) {
 var target = ev.target;
 var xhr = new XMLHttpRequest();
 xhr.onreadystatechange = function() {
    if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var data = JSON.parse(xhr.responseText);
      console.log(data);
      var loveshower = document.getElementById('love');
      if(data['likes']){
        if(data['likes']['toyou'] && data['likes']['fromyou']){
          loveshower.innerText = 'favorite';
        }
        else if(data['likes']['toyou'] && !data['likes']['fromyou']){
          loveshower.innerText = 'favorite_border';
        }
        else if(!data['likes']['toyou'] && data['likes']['fromyou']){
          loveshower.innerText = 'favorite_border';
        }
        else{
          loveshower.innerText = '';
        }
      }

      if(!data['already']) {
        target.innerText = 'Unlike';
      }
      else {
        target.innerText = 'like';
      }
    }
 };
 xhr.open("POST", "likeUser", true);
 xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
 xhr.send("action=like&to="+ encodeURIComponent(login));
}

function blockuser(login,ev) {
 var main = document.getElementById('maincontainer');
 var xhr = new XMLHttpRequest();
 xhr.onreadystatechange = function() {
    if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var data= JSON.parse(xhr.responseText);
      if(data['status'] == 'OK') {
        window.location.replace('/matcha/');
      }
      else {
        console.log(xhr.responseText);
      }
    }
  };
  xhr.open("POST", "blockUser", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("action=block&to="+ encodeURIComponent(login));
}

function openblockpanel(login,ev) {
   console.log(login);
  if(!document.getElementById('block-menu')) {
    var contextmenu = document.createElement('DIV');
    contextmenu.setAttribute('id', 'block-menu');
    contextmenu.innerHTML = '<p class="white-text" id="infoblock"> You are about to block this user, you won\'t be able to see him again, never ever, ever. If you are sure click on the \'bye bye\' button</p>';
    contextmenu.className='centeritem absolute round';
    var send = document.createElement('BUTTON');
    send.className = 'round';
    send.id = 'blocksender';
    send.innerText= 'bye bye';
    send.addEventListener('click', function(eve){ blockuser(login,eve)});
    contextmenu.append(send);
    document.getElementById('maincontainer').prepend(contextmenu);
  }
  else {
    document.getElementById('block-menu').remove();
  }
}

function openreportpanel(login, ev) {
  if(!document.getElementById('report-menu')) {
    var contextmenu = document.createElement('DIV');
    contextmenu.setAttribute('id', 'report-menu');
    contextmenu.innerHTML = '<select id="reportstatus"><option value="1" selected>Message indesirable</option><option value="2">Fake profil</option><option value="3">Photo non conforme</option><option value="4">Other</option></select><textarea id="textreport" placeholder="reasons of report"></textarea>';
    var send = document.createElement('BUTTON');
    send.className = 'round';
    send.id = 'reportsender';
    send.innerText= 'send';
    send.addEventListener('click', function(eve){ reportuser(login,eve)});
    contextmenu.append(send);
    document.getElementById('maincontainer').prepend(contextmenu);
  }
  else {
    document.getElementById('report-menu').remove();
  }
}

function reportuser(login, ev) {
  var text = document.getElementById('textreport');
  var select = document.getElementById('reportstatus');
  if(text && select)
  {
  var type = select.options[select.selectedIndex].value;
  var content = text.value;
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var data = xhr.responseText;
    }
  }
  xhr.open("POST", "reportUser", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("action=report&type="+ encodeURIComponent(type)+"&content="+ encodeURIComponent(content)+"&to="+ encodeURIComponent(login));
  ev.target.parentNode.remove();
  }
}

function sendmsg(login, ev) {
  console.log (login);
}

function openblockmanager(ev) {
  var menucontext2 = document.createElement('DIV');
  var mainbox = document.getElementsByTagName('main');
    menucontext2.innerHTML = '<div class="col s12" style="text-align:center;color:lightgrey;">Clicker sur un utilisateur pour arreter de le blocker</div><div class="col s12" id="listcontainer"></div>';
    menucontext2.setAttribute('class', 'section menucontext');
    menucontext2.setAttribute('id', 'menucontext2');
    menucontext2.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+ mainbox['0'].offsetWidth+'px;');
    mainbox['0'].prepend(menucontext2);
    window.addEventListener('resize', function() {
        var menucontext2 = document.getElementById('menucontext2');
        var mainbox = document.getElementsByTagName('main');
        if(menucontext2){
          menucontext2.setAttribute('style', 'height:'+ mainbox['0'].offsetHeight+'px;width:'+  mainbox['0'].offsetWidth+'px;');
        }
      });

      var menuclose = document.createElement('button');
        menuclose.setAttribute('type', 'button');
        menuclose.setAttribute('class', 'closer');
        menuclose.setAttribute('id', 'blocklistcloser');
        menuclose.innerHTML = 'Close';
        menuclose.setAttribute('style', 'margin-left: 48%;');
        menuclose.addEventListener('click', function(){

        var target = document.getElementById('menucontext2');
        target.parentNode.removeChild(target);
      })
      menucontext2.append(menuclose);

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var data = JSON.parse(xhr.responseText);
    console.log(data);
    var listfield = document.getElementById('listcontainer');

    if(undefined === data['error'] && listfield)
    data.forEach(function (element) {
      var vignet = document.createElement('DIV');
      vignet.className = 'chip'
      var suppr = document.createElement('I');
      suppr.className = 'close material-icons';
      suppr.innerHTML = 'close';

      suppr.addEventListener('click', function(ev) {
        var xhr = new XMLHttpRequest();
        if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
          console.log(JSON.parse(xhr.responseText)['STATUS']);
        }
        xhr.open("POST", "removeblock", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("subject=blkrmv&target="+element['login']);
      });
      vignet.innerHTML =  element['login'];
      vignet.append(suppr);
      listfield.append(vignet);
    });}};
  xhr.open("POST", "get_block_list", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("subject=blklst");
}
