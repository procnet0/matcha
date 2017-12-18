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
                                    rl = "right-align";
                                else
                                    rl = "left-align";
                                if (tab['msg'][i]['new'] == "1")
                                    isnew = "newmsg"
                                else
                                    isnew = "oldmsg";
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
                                                rl = "right-align";
                                            else
                                                rl = "left-align";
                                            if (tab['msg'][i]['new'] == "1")
                                                isnew = "newmsg"
                                            else
                                                isnew = "oldmsg";
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
                        $("#messages").append("<li class=\"message right-align new\">Erreur lors de l'envoi du messages, veuillez actualiser la page</li>");
                    }
                    else
                    {
                        $("#messages").append("<li class=\"message right-align new\">"+escapeHTML(tab['content'])+"</li>");
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
                data: 'action=notif&type=1&nb=0',
                dataType: 'json',
                success: function(tab, status){
                    var like_block = document.getElementById("like_block_content");
                    if (tab['notif'].length > 0)
                    {
                        var likz = document.getElementById("collection_like");
                        create_notif(tab, likz);
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
                data: 'action=notif&type=2&nb=0',
                dataType: 'json',
                success: function(tab, status){
                    if (tab['notif'].length > 0)
                    {
                        var visitz = document.getElementById("collection_visit");
                        create_notif(tab, visitz);
                    }
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
        }
        visit_visible = 1;
    });

    var count_visit = 10;
    var visite_done = 0;
    $(document.getElementById("next_visite")).click(function(){
        if (visite_done == 0)
        {
            $.ajax({
                url: 'notif_list',
                type: 'POST',
                data: {
                    action:'notif',
                    type: 2,
                    nb:count_visit,
                },
                dataType: 'json',
                success: function(tab, status){
                    if (tab['notif'].length > 0)
                    {
                        var visitz = document.getElementById("collection_visit");
                        create_notif(tab, visitz);
                    }
                    if (tab['notif'].length == 0)
                        visite_done = 1;
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
            count_visit += 10;
        }
    });

    var count_like = 10;
    var like_done = 0;
    $(document.getElementById("next_likes")).click(function(){
        if (like_done == 0)
        {
            $.ajax({
                url: 'notif_list',
                type: 'POST',
                data: {
                    action:'notif',
                    type: 1,
                    nb:count_like,
                },
                dataType: 'json',
                success: function(tab, status){
                    if (tab['notif'].length > 0)
                    {
                        var likz = document.getElementById("collection_like");
                        create_notif(tab, likz);
                    }
                    if (tab['notif'].length == 0)
                        like_done = 1;
                },
                error: function(res, status, error){
                    console.log(error);
                }
            });
            count_like += 10;
        }
    });
});