
function escapeHTML(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function addZero(i) {
  if (i < 10) {
      i = "0" + i;
  }
  return i;
}

function set_date(time, item)
{
  var date = new Date();
  date.setTime(time * 1000);
  var reald = new Date();
  var diff = (reald.getTime() / 1000 | 0) - time;
  var ret;
  var min = diff / 60 | 0;
  var hour = diff / 3600 | 0;

  if (diff <= 20)
    ret = "Il y a quelques secondes";
  else if (diff < 60)
    ret = "Il y a moins d'une minute";
  else if (diff >= 60 && min < 60)
  {
    if (min == 1)
      ret = "Il y a une minute";
    else
      ret = "Il y a "+min+" minutes";
  }
  else if (diff >= 3600 && hour < 24)
  {
    if (hour == 1)
      ret = "Il y a une heure";
    else
      ret = "Il y a "+hour+" heures";
  }
  else
  {
    var months = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"];
    ret = "Le "+date.getDate()+" "+months[date.getMonth()]+" "+date.getFullYear()+" à "+addZero(date.getHours())+"H"+addZero(date.getMinutes());
  }
  item.innerHTML = ret;
  setTimeout(function(){set_date(time, item)}, 4000);
}

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

            child[data['number']-1].replaceWith(elem);
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

function IsValidTagName(name) {
  var result = [];
  result['status'] = false;
  result['error'] = '';
  var size = false;
  var content = false;
  var regex = /^[a-z0-9]+$/i;

  if(regex.test(name) == true) {
    content = true;
  }
  else {
    result['error'] = 'Only Letters and/or numbers.';
  }

  if(name.length <= 7) {
    size = true;
  }
  else {
    result['error'] += 'Actual length is ' + name.length + ' max is 7.';
  }
  if( size == true && content == true)
  {
    result['status'] = true;
  }
  return  result;
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
  });
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
      if (xhr2.readyState == 4 && (xhr2.status != 200 && xhr2.status != 0)) {

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
      xhr2.send("subject=tagupdt&activeTag=" + encodeURIComponent(JSON.stringify(actives)));
    });
  var border = document.createElement('DIV');
    border.setAttribute('class', 'border col s3');
  var border2 = border.cloneNode();
  var autosearch = document.createElement('DIV');
    autosearch.setAttribute('class', 'input-field tagselector col s6 offset-s3');
    autosearch.setAttribute('id', 'auto-tag');
    autosearch.innerHTML = '<i class="material-icons prefix">textsms</i><input type="text" id="autocomplete-input" class="autocomplete"><label for="autocomplete-input">Choose tag</label>'
  var sender = document.createElement('BUTTON');
    sender.setAttribute('class','tag_select_button');
    sender.setAttribute('type', 'button');
    sender.innerHTML = 'send';
    sender.addEventListener("click", function() {
      var val = document.getElementById('autocomplete-input').value;
      var result = IsValidTagName(val);
      if(result['status'] == true) {
        var newtag = document.createElement('DIV');
        newtag.className = 'tagitem chip';
        newtag.id = 'tagitem';
        newtag.innerHTML = val+"<i class='material-icons'></i>";
        document.getElementById('active-tag').append(newtag);
      }
      else {
        var newerror = document.createElement('DIV');
        newerror.className = 'alert tagalert offset-s3 col s6';
        newerror.innerHTML = result['error'];
        document.getElementById('active-tag').append(newerror);
        $(newerror).delay(3000).hide("fast",function(){$(newerror).remove()});
      }
    });
  divcont.append(sender);

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
      str = '{';
      dat['taglist'].forEach(function (item, index) {
        str += '"' + item['name_tag'] + '" : null ,';
      });
      str = str.substring(0,str.length -1);
      str += '}';
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
  var marker = new google.maps.Marker({
    position: pos,
    map: map,
    title: 'You'
  });
   map.setCenter(pos);
   document.getElementById('map').style.visibility = 'hidden';
}

function initMap() {
   // Try HTML5 geolocation.
   if(document.getElementById('map')) {
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
       document.getElementById('geoloc').value = data;
     }
   }
     xhr.open("POST", "updatePosition", true);
     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    if(input !== 'undefined') {
     xhr.send("input="+ encodeURIComponent(input));
    }
    else if(data !== 'undefined') {
      xhr.send("lng="+ encodeURIComponent(data['longitude']) + "&lat="+ encodeURIComponent(data['latitude']));
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

idarray = {};

function Tagchooserdisplay(e) {
  var contain = document.getElementById('tag-container');
  var chooser = document.getElementById('autotag');
  if(contain.style.visibility != 'visible') {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var dat = JSON.parse(xhr.responseText);
    idarray = {};
    dat['taglist'].forEach(function (item,index) {
      idarray[item['name_tag']] = item['id_tag'];
    });
        str = '{';
        dat['taglist'].forEach(function (item, index) {
          str += '"' + item['name_tag'] + '" : null ,';
        });
        str = str.substring(0,str.length -1);
        str += '}';
          $('#autocomplete-input').autocomplete({
            data: JSON.parse(str),
            limit: 20,
            onAutocomplete: function(val) {
              var container = document.getElementById('tag-container').getElementsByTagName('ul');
              var newitem = document.createElement('li');
              var innerdiv = document.createElement('DIV');
              var suppresor = document.createElement('I');
              suppresor.className = 'material-icons over';
              suppresor.innerHTML = 'close';

              innerdiv.className = 'tagitem';
              innerdiv.innerHTML = val;
              newitem.addEventListener('click', function(ev) { manageactivity(val,ev);});
              suppresor.addEventListener('click', function(ev) {ev.stopPropagation();supprelem(newitem,val,ev);});
              innerdiv.appendChild(suppresor);
              newitem.appendChild(innerdiv);
              container[0].appendChild(newitem);
            },
            minLength: 1,
          });
    }};
    xhr.open("POST", "getTagInfo", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("subject=tagmbt");
  contain.style.visibility = 'visible';
  chooser.style.visibility = 'visible';
  }
  else {
    contain.style.visibility = 'collapse';
    chooser.style.visibility = 'collapse';
  }
}

var tagarray = [];

function supprelem(item,val,ev) {
  item.parentElement.removeChild(item);
  var index = tagarray.indexOf(val);
  if( index !== -1) {
    tagarray.splice(index, 1);
  }
}

function manageactivity(name_tag, ev) {
 var clas = ev.target.className;
  if(clas.search('activated') === -1) {
  ev.target.setAttribute('class', ev.target.className + ' activated');
  ev.target.style.background = 'radial-gradient(circle at center, transparent ,  rgba(0,255,0, 0.8) 76%, transparent)';
  if(tagarray.indexOf(name_tag) === -1){
    tagarray.push(name_tag);
    }
  }
  else {
    ev.target.className = clas.replace(' activated', '');
    ev.target.style.background = 'radial-gradient(circle at center, transparent ,  rgba(255,255,255, 0.8) 76%, transparent)';
    var index = tagarray.indexOf(name_tag);
    if( index !== -1) {
      tagarray.splice(index, 1);
    }
  }
}

var triator = {'order': undefined, 'by': undefined};
var ordertab = [];

function locationOf(element, array, comparer, start, end) {
    if (array.length === 0)
        return -1;

    start = start || 0;
    end = end || array.length;
    var pivot = (start + end) >> 1;

    var c = comparer(element, array[pivot]);
    if (end - start <= 1) return c == -1 ? pivot - 1 : pivot;

    switch (c) {
        case -1: return locationOf(element, array, comparer, start, pivot);
        case 0: return pivot;
        case 1: return locationOf(element, array, comparer, pivot, end);
    };
};

var ageCompare = function (a, b) {
  var aval = $(a).find('.col-age').html();
  var bval = $(b).find('.col-age').html();
  if (aval < bval) return -1;
  if (aval > bval) return 1;
  return 0;
};

var distCompare = function (a, b) {
  var aval = $(a).find('.col-km').html();
  var bval = $(b).find('.col-km').html();
  if (aval < bval) return -1;
  if (aval > bval) return 1;
  return 0;
};

var scoreCompare = function (a, b) {
  var aval = $(a).find('.col-score').html();
  var bval = $(b).find('.col-score').html();
  if (aval < bval) return -1;
  if (aval > bval) return 1;
  return 0;
};

var tagsCompare = function (a, b) {
  var aval = $(a).find('.col-tags').html();
  var bval = $(b).find('.col-tags').html();
  if (aval < bval) return -1;
  if (aval > bval) return 1;
  return 0;
};

function insertSorted(DomElem) {
 switch (triator['order']) {
  case 'age':
   ordertab.splice(locationOf(DomElem,ordertab,ageCompare) + 1, 0 , DomElem);
  break;
  case 'distance':
    ordertab.splice(locationOf(DomElem,ordertab,distCompare) + 1, 0 , DomElem);
  break;
  case 'score':
    ordertab.splice(locationOf(DomElem,ordertab,scoreCompare) + 1, 0 , DomElem);
  break;
  case 'tags':
    ordertab.splice(locationOf(DomElem,ordertab,tagsCompare) + 1, 0 , DomElem);
  break;
  }
}

function SortOrderTab()
{
  switch (triator['order']) {
   case 'age':
    ordertab.sort(ageCompare);
   break;
   case 'distance':
     ordertab.sort(distCompare);
   break;
   case 'score':
     ordertab.sort(scoreCompare);
   break;
   case 'tags':
     ordertab.sort(tagsCompare);
   break;
   }
}

function sortresult(action,ev) {
  var valid = ['age','score','tags','distance'].indexOf(action);
  if(valid >= 0 && valid <= 3)
  {
    triator['order'] = action;
    var targ = ev.target;
    var inner = ev.target.innerText;
    if(inner.search('down') != -1) {
      ev.target.innerText = inner.replace('down' , 'up');
      triator['by'] = '+';
    }
    else {
      ev.target.innerText = inner.replace('up' , 'down');
      triator['by'] = '-';
    }
    SortOrderTab();
    var resultzone = document.getElementById('search_content_area');
    $(ordertab).remove();
    if(triator['by'] === '+') {
    $(ordertab).each(function(key,elem) {$(resultzone).append(elem);});
    }
    else {
      $(ordertab).each(function(key,elem) {$(resultzone).prepend(elem);});
    }
  }
}

function filterDiv(divlist,callback) {
  $.each(divlist, callback);
}

var requestor = undefined;
var filtrator = {'0':undefined,'1':undefined,'2':undefined,'3':undefined};

function tagIsInArray(filtre, tags) {
  var count = 0;
  $.each(filtre, function(key, elem){

    if(idarray[elem] !== undefined)
    {
      if(tags.indexOf(idarray[elem]) >= 0)
      {
        count += 1;
      }
    }
  });
  if(count == filtre.length)
  {
    return true;
  }
  else {
    return false;
  }
}

function activefilter(filter_name) {
  var valid = ['Age','Pop','Tags','Area'].indexOf(filter_name);
  if(valid != -1 && valid < 4) {
    var container = document.getElementById("search_content_area");
    var divlist = $("div[id^='user']");
    if(container !== 'undefined' && divlist !== 'undefined') {
      switch(valid) {
        case 0:
        var range = document.getElementById('age-picker').noUiSlider.get();
        filtrator['0'] = range;
        break;
        case 1:
        var range = document.getElementById('range-popularity').noUiSlider.get();
        filtrator['1'] = range;
        break;
        case 2:
        filtrator['2'] = tagarray;
        break;
        case 3:
        var range = document.getElementById('range-picker').noUiSlider.get();
        filtrator['3'] = range;
        break;
      }
      filterDiv(divlist,checkFilter);
    }
  }
}

function checkFilter(key, DomElem) {
  var tohide = 0;
  var age = $(DomElem).find('.col-age').html();
    if(filtrator['0'] !== undefined && (age < filtrator['0']['0']  && age > filtrator['0']['1']))
    {
      tohide += 1;
    }
  var score = $(DomElem).find('.col-score').html();
    if(filtrator['1'] !== undefined && (score < filtrator['1']['0']  && score > filtrator['1']['1']))
    {
      tohide +=1;
    }
  var tags = $(DomElem).find('.col-tags').attr('data');
    if(filtrator['2'] !== undefined && tagIsInArray(tagarray,tags) == false)
    {
      tohide +=1;
    }
  var dist = $(DomElem).find('.col-km').html();
    if(filtrator['3'] !== undefined  && (dist > filtrator['3']['0']))
    {
      tohide +=1;
    }
  if(tohide >= 1) {
    $(DomElem).hide();
  }
  else {
    $(DomElem).show();
  }
}

var extracted = 0;

function startsearch(status) {
  if(status === 'new') {
    extracted = 0;
    document.getElementById('search_content_area').innerHTML = '';
  }
  requestor = undefined;
  filtrator = {'0':undefined,'1':undefined,'2':undefined,'3':undefined};
  triator = {'order': undefined, 'by': undefined};
  ordertab = [];
  var age = document.getElementById('age-picker');
  var range = document.getElementById('range-picker');
  var rangeorigin = document.getElementById('range-origin');
  var pop = document.getElementById('range-popularity');
  var tags = tagarray;

  var actives = [];
  if(tags) {
    for (var tag in tags) {
      if (tags.hasOwnProperty(tag)) {
      actives.push(tags[tag]);
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
        if(data['result'] !== undefined) {
        data['result'].forEach(function (element) {
          var newelem = document.createElement('DIV');
          num += 1;
          newelem.setAttribute('id', 'user'+num);
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
          newelem.innerHTML = "<div class='row UserVignet' id='row"+num+"'><a href='/matcha/lookat/"+data['result'][numtmp]['login']+"'><div class='user_container'><div class='col s2 miniProfilPict'><img src='"+data['result'][numtmp]['profil_pict']+"' class='miniProfilPict'><div class='flex'><p class='nameContainer'>"+data['result'][numtmp]['prenom']+" "+data['result'][numtmp]['nom'].substring(0,1)+". </p>"+connect+"</div></div><div class='col s2 col-age'>"+data['result'][numtmp]['age']+" </div><div class='col s2 col-km'>"+ data['result'][numtmp]['dist']+'</div><div class="col s2 col-score">'+ data['result'][numtmp]['score']+'</div><div class="col s1 col-tags"  data="'+ tag +'"> '+ data['result'][numtmp]['nb'] +"</div><div class='col s1'></div></div></a></div>";
          numtmp += 1;
          resultzone.appendChild(newelem);
          ordertab.push(newelem);
        });
        }
        requestor = data['paramenter'];
        infiniteScroll();
      }
    }
    xhr.open("POST", "recherche", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("age="+ encodeURIComponent(age) +"&range="+ encodeURIComponent(range)+ "&pop="+encodeURIComponent(pop)+"&area="+encodeURIComponent(address)+"&tags="+encodeURIComponent(actives)+"&extracted="+encodeURIComponent(extracted));
    });
  }
}

function infiniteScroll() {

  var interval = setTimeout(function() {

    var e =  document.getElementById('search_content_area');
     if(e.scrollTop + e.clientHeight >= e.scrollHeight)
     {
       if(requestor !== undefined && requestor !== '')
       {
         var xhr = new XMLHttpRequest();
         xhr.onreadystatechange = function() {
           if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
             var data = JSON.parse(xhr.responseText);
             var num = extracted;
             var numtmp = 0;
             var connect = '';
             extracted += data['extracted'];
             var resultzone = document.getElementById('search_content_area');
             if(data['result'] !== undefined) {
             data['result'].forEach(function (element) {
               var newelem = document.createElement('DIV');
               num += 1;
               newelem.setAttribute('id', 'user'+num);
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
               newelem.innerHTML = "<div class='row UserVignet' id='row"+num+"'><a href='/matcha/lookat/"+data['result'][numtmp]['login']+"'><div class='user_container'><div class='col s2 miniProfilPict'><img src='"+data['result'][numtmp]['profil_pict']+"' class='miniProfilPict'><div class='flex'><p class='nameContainer'>"+data['result'][numtmp]['prenom']+" "+data['result'][numtmp]['nom'].substring(0,1)+". </p>"+connect+"</div></div><div class='col s2 col-age'>"+data['result'][numtmp]['age']+" </div><div class='col s2 col-km'>"+ data['result'][numtmp]['dist']+'</div><div class="col s2 col-score">'+ data['result'][numtmp]['score']+'</div><div class="col s1 col-tags" data="' + tag + '"> '+ data['result'][numtmp]['nb'] +"</div><div class='col s1'></div></div></a></div>";

               resultzone.appendChild(newelem);
               if(triator['order'] === undefined && triator['by'] === undefined)
               {ordertab.push(newelem);}
               else
               {insertSorted(newelem);}
               checkFilter(0, $(newelem));
               numtmp += 1;

             });
            }
             if(triator['order'] !== undefined && triator['by'] !== undefined)
             {
               $(ordertab).remove();
               if(triator['by'] === '+') {
               $(ordertab).each(function(key,elem) {$(resultzone).append(elem);});
               }
               else {
                 $(ordertab).each(function(key,elem) {$(resultzone).prepend(elem);});
               }
             }
             requestor = data['paramenter'];
             if(data['extracted'] == 5){
             setTimeout(infiniteScroll(),1000);
              }
           }
         }
         xhr.open("POST", "recherche", true);
         xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
         xhr.send("age="+ encodeURIComponent(requestor['age']) +"&range="+ encodeURIComponent(requestor['range'])+ "&pop="+encodeURIComponent(requestor['pop'])+"&area="+encodeURIComponent(requestor['area'])+"&tags="+encodeURIComponent(requestor['tags'])+"&extracted="+encodeURIComponent(extracted));
       }
       return;
     }
     infiniteScroll();
     return;
  } , 1000);
}

function likeuser(login, ev) {
 var target = ev.target;
 var xhr = new XMLHttpRequest();
 xhr.onreadystatechange = function() {
    if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
      var data = JSON.parse(xhr.responseText);
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
    $(document).on('click',function (event) {
      if(!$(event.target).closest($(contextmenu)).length && !$(event.target).closest($(ev.target)).length){
        document.getElementById('block-menu').remove();
        $(document).off('click');
      }
    });
  }
  else {
    document.getElementById('block-menu').remove();
    $(document).off('click');
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
    $(document).on('click',function (event) {
      if(!$(event.target).closest($(contextmenu)).length && !$(event.target).closest($(ev.target)).length){
        document.getElementById('report-menu').remove();
        $(document).off('click');
      }
    });
  }
  else {
    document.getElementById('report-menu').remove();
    $(document).off('click');
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
        if (xhr.readyState == 4 && (xhr.status != 200 && xhr.status != 0)) {
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


function set_old(evt)
{
    var real = evt.currentTarget;
    $.post(
        'set_new_to_old',
        'action=newold&notif=' + real.id_notif,
        function (text){
            if (text == "ok")
            {
              real.className = "collection-item avatar old_notif";
            }
            else
              alert("Problem with post return Error:"+text);
        },
        'text'
    );
    real.removeEventListener("mouseover", set_old);
}

function create_notif(tab, item, str, type)
{
  var date = new Date();
  for (var i = 0; i < tab['notif'].length; i++)
  {
    if (type == null || type == tab['notif'][i]['type'] || tab['notif'][i]['type'] == 4 || tab['notif'][i]['type'] == 5)
    {
      var htmlcode = "";
      var notif = document.createElement("a");
      notif.setAttribute("href", escapeHTML("/matcha/lookat/"+tab['notif'][i]['login']));
      if (tab['notif'][i]['new'] == "1")
      {
          notif.className = "collection-item avatar new_notif";
          notif.addEventListener("mouseover", set_old);
          notif.id_notif = tab['notif'][i]['id_notif'];
      }
      else
          notif.className = "collection-item avatar old_notif";
      if(tab['notif'][i]['profil_pict'] == "#")
          htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
      else
          htmlcode += "<img src=\""+tab['notif'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
      htmlcode += "<span class=\"title\">"+escapeHTML(tab['notif'][i]['login'])+"</span>";
      if (tab['notif'][i]['type'] == 1)
          htmlcode += "<p>Like</p>";
      else if (tab['notif'][i]['type'] == 4)
          htmlcode += "<p>Match</p>";
      else if (tab['notif'][i]['type'] == 5)
          htmlcode += "<p>Unlike</p>";
      var test = document.createElement("p");
      set_date(tab['notif'][i]['timeof'], test);
      notif.innerHTML = htmlcode;
      notif.appendChild(test);
      if (str == "append")
        item.appendChild(notif);
      else if (str == "prepend")
        item.prepend(notif);
    }
  }
}

id_user = -1;

function add_new_notif(item, off, type){
  $.ajax({
    url: '/matcha/last_notif',
    type:'POST',
    dataType:'json',
    data: {
      action:"getnewnotif",
      offset:off
    },
    success: function(tab){
      create_notif(tab, item, "prepend", type);
    }
  })

}

function autonotif() {
  $.ajax({
      url: '/matcha/notif',
      type: 'POST',
      dataType: 'json',
      data:{
        id: id_user
      },
      success: function(data){
        var notif_other_nb = $(document.getElementById("notif"));
        if (notif_other_nb)
        {
          if(data['nb_other'] != 0 ) {
            notif_other_nb.html(data['nb_other']);
            notif_other_nb.css('visibility', 'visible');
          }
          else
            notif_other_nb.html("0");
        }
        var msg_nb_notif = $(document.getElementById("msg_nbr"));
        if (msg_nb_notif)
        {
          if(data['nb_msg'] != 0 ) {
            msg_nb_notif.html(data['nb_msg']);
            msg_nb_notif.css('visibility', 'visible');
          }
          else
          {
            msg_nb_notif.html("0");
          }
        }
        if (document.getElementById("notif_container"))
        {
            document.getElementById("likebadge").innerHTML = data['nb_like'];
            document.getElementById("visitesbadge").innerHTML = data['nb_visits'];
        }
        if (data['notif'] && data['notif'].length != 0)
        {
          var li_like = document.getElementById("li_like");
          if (li_like && li_like.className == "active")
          {
            var likz = document.getElementById("collection_like");
            add_new_notif(likz, data['previous_off'], 1);
          }
          var li_visit = document.getElementById("li_visit");
          if (li_visit && li_visit.className == "active")
          {
            var visitz = document.getElementById("collection_visit");
            add_new_notif(visitz, data['previous_off'], 2);
          }
          for (var i = 0; i < data['notif'].length; i++)
          {
            if(data['notif'][i]['type'] == 1)
              Materialize.toast(data['notif'][i]['login']+" vous a like !", 3000);
            else if(data['notif'][i]['type'] == 2)
            {
              if (data['notif'][i]['nb_notif'] > 1)
                Materialize.toast(data['notif'][i]['nb_notif']+" nouvelles visites par " + data['notif'][i]['login'], 3000);
              else
                Materialize.toast("Une nouvelle visite par " + data['notif'][i]['login'], 3000);
            }
            else if(data['notif'][i]['type'] == 3 && data['notif'][i]['id_user'] != id_user)
            {
              if ($("[data-id='"+data['notif'][i]['id_user']+"']"))
              {
                var toto = $("[data-id='"+data['notif'][i]['id_user']+"']").find(".user_new_msg");
                var b = parseInt(data['notif'][i]['nb_notif']);
                if (toto.html() != 0)
                {
                  var sum = parseInt(toto.html()) + b;
                  toto.html(sum);
                }
                else if (toto.html() > 99)
                  toto.html("99+");
                else
                  toto.html(b);
              }
              if (data['notif'][i]['nb_notif'] > 1)
                Materialize.toast(data['notif'][i]['login']+" vous a envoyé "+data['notif'][i]['nb_notif']+" nouveaux messages", 3000);
              else
                Materialize.toast(data['notif'][i]['login']+" vous a envoyé un nouveau message", 3000);
            }
            else if(data['notif'][i]['type'] == 4)
              Materialize.toast(data['notif'][i]['login']+" vous a match !", 3000);
            else if(data['notif'][i]['type'] == 5)
              Materialize.toast(data['notif'][i]['login']+" vous a unlike :(", 3000);
          }
        }
        if (data['msg'] && data['msg'].length != 0) {
          $(".notseen").removeClass("notseen").addClass("seen");
          
          for(i = 0; i < data['msg'].length; i++){
            $("#messages").append("<div class=\"message not_my_msg\"><li class=\"new_msg\" >"+escapeHTML(data['msg'][i]['content'])+"</li>");
          }
          $("#chat_msg").animate({ scrollTop: $("#messages").height() }, 1000)
        }
      }
    });
  setTimeout(autonotif , 3000);
}

var SideMode = 2;

function initSidNav() {
  var Width = window.innerWidth;
  if(Width <= 600) {
    SideMode = 1;
    SetNavBar(1);
  }
  else {
    SideMode = 2;
    SetNavBar(2);
  }
  window.addEventListener("resize",function() {
    var width = window.innerWidth;

    if(width <= 600 && SideMode != 1) {
      SideMode = 1;
      SetNavBar(1);
     }
     else if(width > 600 && SideMode != 2) {
       SideMode = 2;
       SetNavBar(2);
     }
  });
}

function SetNavBar(type) {
  var container  = document.getElementById('header-menu-container');
  var connected = container.getAttribute('connected');
  if(connected !== null && (connected == 1))
  {
    if(type == 1) {
      var sidebar = document.createElement('div');
      sidebar.setAttribute('data-activates', 'slide-out');
      sidebar.setAttribute('id','sidebar');

      var menutxt = document.createElement("span");
      menutxt.setAttribute("class", "sidebar_style");
      menutxt.innerHTML = "MENU";

      var triangle = document.createElement("span");
      triangle.setAttribute("class", "sidebar_style triangle");
      triangle.innerHTML = "<i class=\"medium material-icons\">chevron_right</i>";

      sidebar.append(menutxt);
      sidebar.append(triangle);

      var sidenav = document.createElement('div');
      sidenav.setAttribute('class', 'side-nav');
      sidenav.setAttribute('id', 'slide-out');

      var divlist = container.children;
      $(divlist).each(function(index) { $(this).addClass('side-nav-object')});
      $(sidenav).append($(divlist));
      container.parentElement.append(sidenav);
      container.append(sidebar);
      $(sidebar).sideNav();
    }
    else if(type == 2)
    {
      var sidenav = document.getElementById('slide-out');
      var sidebar = document.getElementById('sidebar');
      if(sidenav && sidebar) {
        var divlist = sidenav.children;
        $(divlist).each(function(index) { $(this).removeClass('side-nav-object')});
        $(container).append($(divlist));

        sidenav.parentElement.removeChild(sidenav);
        sidebar.parentElement.removeChild(sidebar);
      }
    }
  }
}
