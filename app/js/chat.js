$(document).ready(function()
{

    var loaded = 0;
    var data = JSON.parse(useractiv);
    function select_user(id, is_small)
    {
        if(is_small == 1)
        {
            var profil_info = $("#current_profil_info_small"), profil_name = $("#profil_name_small"), profil_image = $("#profil_image_small"), profil_link = $("#profil_link_small"), form_block = $("#form_block_small"), chat_msg = $("#chat_msg_small");
            var next = $("#next_small"), text_content = $("#text_content_small"), user_id = $("#user_id_small"), messages = $("#messages_small");
            $("#profiles_small").hide();
            $("#messages_smallz").show();
            $("#hide_this").hide();
            $("#notif_button_small").hide();
        }
        else
        {
            var profil_info = $("#current_profil_info"), profil_name = $("#profil_name"), profil_image = $("#profil_image"), profil_link = $("#profil_link"), form_block = $("#form_block"), chat_msg = $("#chat_msg");
            var next = $("#next"), text_content = $("#text_content"), user_id = $("#user_id"), messages = $("#messages");
        }
        for(var i = 0; i < data.length; i++)
        {
            if (data[i]['id'] == id)
            {
                id_user = id;
                profil_info.find(".default_profil_info").hide();
                profil_name.show();
                profil_image.show();
                profil_link.show();
                form_block.show();
                chat_msg.show();
                profil_name.children("p").html(data[i]['login']);
                next.html("Afficher les prochains messages");
                text_content.css("border", "2px solid black");
                if(document.getElementById("current_profil_info")) document.getElementById("current_profil_info").className = "current_profil";
                if (data[i]['profil_pict'] == "#")
                    profil_image.children("img").attr("src", "/matcha/app/css/image/Photo-non-disponible.png");
                else
                    profil_image.children("img").attr("src", data[i]['profil_pict']);
                profil_link.children("a").attr("href", "/matcha/lookat/"+encodeURI(data[i]['login']));
                user_id.attr("value", id);
                messages.html("");
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
                        if (tab['status'] == "OK")
                        {
                            loaded = loaded + tab['msg'].length;
                            for (var i = 0; i < tab['msg'].length; i++)
                            {
                                var rl = "";
                                var isnew = "";
                                if (tab['msg'][i]['fromyou'] == "1")
                                {
                                    rl = "my_msg";
                                    if (tab['msg'][i]['new'] == "1")
                                        isnew = "notseen"
                                    else
                                        isnew = "seen";
                                }
                                else
                                {
                                    rl = "not_my_msg";
                                    if (tab['msg'][i]['new'] == "1")
                                        isnew = "new_msg"
                                    else
                                        isnew = "old_msg";
                                }
                                messages.prepend("<div class=\"message "+rl+"\"><li class=\""+isnew+"\" >"+escapeHTML(tab['msg'][i]['content'])+"</li></div>");
                            }
                            chat_msg.animate({ scrollTop: messages.height() }, 1000);
                        }
                    },
                    error: function (msg){
                        alert("Error : "+msg);
                    }
                });
                next.off("click");
                $count = 0;
                next.on("click", function(){
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
                                                rl = "my_msg";
                                                if (tab['msg'][i]['new'] == "1")
                                                    isnew = "notseen"
                                                else
                                                    isnew = "seen";
                                            }
                                            else
                                            {
                                                rl = "not_my_msg";
                                                if (tab['msg'][i]['new'] == "1")
                                                    isnew = "new_msg"
                                                else
                                                    isnew = "old_msg";
                                            }
                                            messages.prepend("<div class=\"message "+rl+"\"><li class=\""+isnew+"\" >"+escapeHTML(tab['msg'][i]['content'])+"</li>");
                                        }
                                    }
                                    loaded = loaded+tab['msg'].length;
                                    if (loaded == tab['counter'])
                                        next.html("Tout les messages ont été chargés");
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
            select_user(id, 0);
        })
    });

    $(".profil_list_user_small").each(function(){

        $(this).click(function(){
            var id = $(this).data('id');
            select_user(id, 1);
        })
    });

    $("#return_to_list").click(function(){
        $("#messages_smallz").hide();
        $("#profiles_small").show();
        $("#hide_this").show();
        $("#notif_button_small").show();
    })

    $("#form_messages_small").submit(function(ev){
        var txt = $("#text_content_small");
        var user_id = $("#user_id_small");
        txt.css("border", "2px solid black");
        if (txt.val() == "" || user_id.val() == "")
            txt.css("border", "2px solid red");
        else if (txt.val().length <= 2000)
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
                    $(".new_msg").removeClass("new_msg").addClass("old_msg");
                    var msg = $(document.getElementById("messages_small"));
                    console.log(status);
                    console.log(tab);
                    if(tab['error'] == "Message is empty")
                    {
                        console.log("Le message est vide");
                    }
                    else if (tab['status'] != "OK")
                    {
                        msg.append("<div class=\"message my_msg\"><li class=\"notseen\" >Erreur lors de l'envoi du messages, veuillez actualiser la page <br/ >erreur :"+tab['status']+"</li>");
                    }
                    else if (tab['error'] != "NO")
                    {
                        alert(tab['error']);
                    }
                    else
                    {
                        msg.append("<div class=\"message my_msg\"><li class=\"notseen\" >"+escapeHTML(tab['content'])+"</li>");
                    }
                    $("#chat_msg_small").animate({ scrollTop: $("#messages_small").height() }, 1000);
                }
            });
            txt.val("");
        }
        ev.preventDefault();
    });

    $("#form_messages").submit(function(ev){
        var txt = $("#text_content");
        var user_id = $("#user_id");
        txt.css("border", "2px solid black");
        if (txt.val() == "" || user_id.val() == "")
            txt.css("border", "2px solid red");
        else if (txt.val().length <= 2000)
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
                    $(".new_msg").removeClass("new_msg").addClass("old_msg");
                    var msg = $(document.getElementById("messages"));
                    if(tab['error'] == "Message is empty")
                    {
                        console.log("Le message est vide");
                    }
                    else if (tab['status'] != "OK")
                    {
                        msg.append("<div class=\"message my_msg\"><li class=\"notseen\" >Erreur lors de l'envoi du messages, veuillez actualiser la page <br/ >erreur :"+tab['status']+"</li>");
                    }
                    else if (tab['error'] != "NO")
                    {
                        alert(tab['error']);
                    }
                    else
                    {
                        msg.append("<div class=\"message my_msg\"><li class=\"notseen\" >"+escapeHTML(tab['content'])+"</li>");
                    }
                    $("#chat_msg").animate({ scrollTop: $("#messages").height() }, 1000);
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

    $("#notif_button_small").click(function(){
        $(this).hide();
        $("#profiles_small").hide();
        $("#chat_container").hide();
        $("#notif_container").show();
    });

    $("#chat_button").click(function(){
        $("#profiles_small").show();
        $("#notif_button_small").show();
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
                        create_notif(tab, likz, "append");
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
                        create_notif(tab, visitz, "append");
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
                        create_notif(tab, visitz, "prepend");
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
                        create_notif(tab, likz, "prepend");
                    }
                    if (tab['notif'].length == 0)
                        like_done = 1;
                },
                error: function(res, status, error){
                    console.log("Error "+error);
                }
            });
            count_like += 10;
        }
    });
});
