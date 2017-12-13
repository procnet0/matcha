$(document).ready(function()
{
    
    var loaded = 0;
    var data = JSON.parse(useractiv);
    function select_user(id)
    {
        
        for(var i = 0; i < data.length; i++)
        {
            if (data[i]['id'] == id)
            {
                id_user = id;
                $("#current_profil_info").find(".default_profil_info").hide();
                $("#profil_name, #profil_image, #profil_link, #form_block, #chat_msg").show();
                $("#profil_name").children("p").html(data[i]['login']);
                $("#next").html("Afficher les prochains messages");
                $("#text_content").css("border", "2px solid black");
                if (data[i]['profil_pict'] == "#")
                    $("#profil_image").children("img").attr("src", "/matcha/app/css/image/Photo-non-disponible.png");
                else
                    $("#profil_image").children("img").attr("src", data[i]['profil_pict']);
                $("#profil_link").children("a").attr("href", "/matcha/lookat/"+encodeURI(data[i]['login']));
                $("#user_id").attr("value", id);
                document.getElementById("messages").innerHTML = "";
                loaded = 0;
                $.ajax({
                    url: 'msglist',
                    type: 'POST',
                    data: {
                        id: id,
                        nb: loaded
                    },
                    dataType: 'json',
                    success: function (tab, status){
                        console.log(tab['status']);
                        if (tab['status'] == "OK")
                        {
                            loaded = loaded + tab['msg'].length;
                            for (var i = 0; i < tab['msg'].length; i++)
                            {
                                var rl = "";
                                var isnew = "";
                                if (tab['msg'][i]['fromyou'] == "1")
                                {
                                    if (tab['msg'][i]['new'] == "1")
                                        isnew = "unread";
                                    else
                                        isnew = "read";
                                    rl = "right-msg";
                                }
                                else
                                {
                                    if (tab['msg'][i]['new'] == "1")
                                        isnew = "newmsg";
                                    else
                                        isnew = "oldmsg";
                                    rl = "left-msg";
                                }
                                $("#messages").prepend("<li class=\"message "+rl+" "+isnew+"\">"+escapeHTML(tab['msg'][i]['content'])+"</li>");
                            }
                        }
                    },
                    error: function (msg){
                        console.log("Error messages" + msg);
                    }
                });
                $("#next").off("click");
                $count = 0;
                $("#next").on("click", function(){
                    if (loaded != $count)
                    {
                        $.ajax({
                            url: 'msglist',
                            type: 'POST',
                            data: {
                                id: id,
                                nb: loaded
                            },
                            dataType: 'json',
                            success: function (tab, status){
                                if (tab['status'] == "OK")
                                {
                                    $count = tab['counter']                
                                    if (loaded != tab['counter'])
                                    {
                                        for (var i = 0; i < tab['msg'].length; i++)
                                        {
                                            var rl = "";
                                            var isnew = "";
                                            
                                            if (tab['msg'][i]['fromyou'] == "1")
                                            {
                                                if (tab['msg'][i]['new'] == "1")
                                                    isnew = "unread";
                                                else
                                                    isnew = "read";
                                                rl = "right-msg";
                                            }
                                            else
                                            {
                                                if (tab['msg'][i]['new'] == "1")
                                                    isnew = "newmsg";
                                                else
                                                    isnew = "oldmsg";
                                                rl = "left-msg";
                                            }
                                            $("#messages").prepend("<li class=\"message "+rl+" "+isnew+"\">"+escapeHTML(tab['msg'][i]['content'])+"</li>");
                                        }
                                    }
                                    loaded = loaded+tab['msg'].length;
                                    if (loaded == tab['counter'])
                                        $("#next").html("Tout les messages ont été chargés");
                                }
                            },
                            error: function (msg){
                                console.log("Error messages" + msg);
                            }
                        });
                    }
                });
                break;
            }
        }
    }

    

    $(".profil_list_user").each(function(){
        $(this).click(function(){
            var id = $(this).data('id');
            select_user(id);
        })
    });

    $("#form_messages").submit(function(ev){
        var txt = $("#text_content");
        var user_id = $("#user_id");
        txt.css("border", "2px solid black");
        if (txt.val() == "" || user_id.val() == "")
            txt.css("border", "2px solid red");
        else
        {
            $.ajax({
                url: 'messenger',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: user_id.val(),
                    content: txt.val()
                },
                success: function(tab, status){
                    if (tab['content'] == "Error")
                    {
                        $("#messages").append("<li class=\"message right-msg unread\">Erreur lors de l'envoi du messages, veuillez actualiser la page</li>");
                    }
                    else
                    {
                        $("#messages").append("<li class=\"message right-msg unread\">"+escapeHTML(tab['content'])+"</li>");
                    }
                }
            });
            txt.val("");
        }
        ev.preventDefault();
    });

    $("#notif_button").click(function(){
        $("#chat_container").hide();
        $("#notif_container").show();
    });

    $("#chat_button").click(function(){
        $("#chat_container").show();
        $("#notif_container").hide();
    });

    var like_visible = 0;
    $("#like_block").click(function(){
        if (like_visible != 1)
        {
            $.ajax({
                url: 'notif_list',
                type: 'POST',
                data: 'action=notif&type=1',
                dataType: 'json',
                success: function(tab, status){
                    var like_block = document.getElementById("like_block_content");
                    if (tab['news'].length > 0)
                    {
                        for (var i = 0; i < tab['news'].length; i++)
                        {
                            var htmlcode = "";
                            var notif = document.createElement("a");
                            notif.setAttribute("href", escapeHTML("/matcha/lookat/"+tab['news'][i]['login']));
                            notif.className = "collection-item avatar new_notif";
                            if(tab['news'][i]['profil_pict'] == "#")
                                htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
                            else
                                htmlcode += "<img src=\""+tab['news'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
                            htmlcode += "<span class=\"title\">"+escapeHTML(tab['news'][i]['login'])+"</span>";
                            if (tab['news'][i]['type'] == 1)
                                htmlcode += "<p>Like</p>";
                            else if (tab['news'][i]['type'] == 4)
                                htmlcode += "<p>Match</p>";
                            else
                                htmlcode += "<p>Unlike</p>";
                            notif.innerHTML = htmlcode;
                            notif.addEventListener("mouseover", set_old, false);
                            notif.id_notif = tab['news'][i]['id_notif'];
                            like_block.getElementsByTagName("UL")[0].appendChild(notif);
                        }
                    }
                    else
                    {
                        var msg = document.createElement("p");
                        msg.innerHTML = "Aucune nouvelle notification";
                        like_block.appendChild(msg);
                    }
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
        }
        like_visible = 1;
    });

    var visit_visible = 0;
    $("#visits_block").click(function(){
        if (visit_visible != 1)
        {
            $.ajax({
                url: 'notif_list',
                type: 'POST',
                data: 'action=notif&type=2',
                dataType: 'json',
                success: function(tab, status){
                    console.log(tab);
                    for (var i = 0; i < tab['news'].length; i++)
                    {
                        var htmlcode = "";
                        var notif = document.createElement("a");
                        notif.setAttribute("href", escapeHTML("/matcha/lookat/"+tab['news'][i]['login']));
                        notif.className = "collection-item avatar new_notif";
                        if(tab['news'][i]['profil_pict'] == "#")
                            htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
                        else
                            htmlcode += "<img src=\""+tab['news'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
                        htmlcode += "<span class=\"title\">"+escapeHTML(tab['news'][i]['login'])+"</span>";
                        notif.innerHTML = htmlcode;
                        notif.addEventListener("mouseover", set_old);
                        notif.id_notif = tab['news'][i]['id_notif'];
                        document.getElementById("visits_block_content").getElementsByTagName("UL")[0].appendChild(notif);   
                    }
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
        }
        visit_visible = 1;
    });


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
                    real.removeEventListener("mouseover", set_old);
                }
                else
                    console.log(text);
            },
            'text' 
        );
    }

    var notif_visite_visible = 0
    $(document.getElementById("next_visite")).click(function(){
        if (notif_visite_visible != 1)
        {
            $.ajax({
                url: 'notif_list',
                type: 'POST',
                data: {
                    action:'notif',
                    type: 2,
                    nb:0,
                    isold: 1
                },
                dataType: 'json',
                success: function(tab, status){
                    console.log(tab);
                    for (var i = 0; i < tab['olds'].length; i++)
                    {
                        var htmlcode = "";
                        var notif = document.createElement("a");
                        notif.setAttribute("href", escapeHTML("/matcha/lookat/"+tab['olds'][i]['login']));
                        notif.className = "collection-item avatar old_notif";
                        if(tab['olds'][i]['profil_pict'] == "#")
                            htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
                        else
                            htmlcode += "<img src=\""+tab['olds'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
                        htmlcode += "<span class=\"title\">"+escapeHTML(tab['olds'][i]['login'])+"</span>";
                        notif.innerHTML = htmlcode;
                        notif.id_notif = tab['olds'][i]['id_notif'];
                        document.getElementById("visits_block_content").getElementsByTagName("UL")[0].appendChild(notif);
                    }
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
        }
        notif_visite_visible = 1;
    });
});