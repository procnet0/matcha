$(document).ready(function()
{
    var loaded = 0;
    var data = JSON.parse(useractiv);
    function select_user(id)
    {
        loaded = 0;
        for(var i = 0; i < data.length; i++)
        {
            if (data[i]['id'] == id)
            {
                $("#current_profil_info").find(".default_profil_info").hide();
                $("#profil_name, #profil_image, #profil_link, #form_block, #chat_msg").show();
                $("#profil_name").children("p").html(data[i]['login']);
                if (data[i]['profil_pict'] == "#")
                    $("#profil_image").children("img").attr("src", "/matcha/app/css/image/Photo-non-disponible.png");
                else
                    $("#profil_image").children("img").attr("src", data[i]['profil_pict']);
                $("#profil_link").children("a").attr("href", "/matcha/lookat/"+data[i]['login']);
                $("#user_id").attr("value", id);

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
                                $("#messages").prepend("<li class=\"message "+rl+" "+isnew+"\">"+tab['msg'][i]['content']+"</li>");
                            }
                        }
                    },
                    error: function (msg){
                        console.log("Error messages" + msg);
                    }
                });
                $("#next").click(function(){
                    loaded = loaded + 10;
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
                                    $("#messages").prepend("<li class=\"message "+rl+" "+isnew+"\">"+tab['msg'][i]['content']+"</li>");
                                }
                            }
                        },
                        error: function (msg){
                            console.log("Error messages" + msg);
                        }
                    });
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
                    if (tab['content'] == "OK")
                    {
                        $("#messages").append("<li class=\"message right-align new\">"+txt.val()+"</li>");
                        console.log(txt.val());
                        txt.val("");
                    }
                }
            });
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
                    for (var i = 0; i < tab['news'].length; i++)
                    {
                        var htmlcode = "";
                        var notif = document.createElement("LI");
                        notif.className = "collection-item avatar new_notif";
                        if(tab['news'][i]['profil_pict'] == "#")
                            htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
                        else
                            htmlcode += "<img src=\""+tab['news'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
                        htmlcode += "<span class=\"title\">"+tab['news'][i]['login']+"</span>";
                        if (tab['news'][i]['type'] == 1)
                            htmlcode += "<p>Like</p>";
                        else if (tab['news'][i]['type'] == 4)
                            htmlcode += "<p>Match</p>";
                        else
                            htmlcode += "<p>Unlike</p>";
                        notif.innerHTML = htmlcode;
                        notif.addEventListener("mouseover", set_old, false);
                        notif.id_notif = tab['news'][i]['id_notif'];
                        document.getElementById("like_block_content").getElementsByTagName("UL")[0].appendChild(notif);
                    }
                    console.log(tab);
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
                    for (var i = 0; i < tab['news'].length; i++)
                    {
                        var htmlcode = "";
                        var notif = document.createElement("LI");
                        notif.className = "collection-item avatar new_notif";
                        if(tab['news'][i]['profil_pict'] == "#")
                            htmlcode += "<img src=\"/matcha/app/css/image/Photo-non-disponible.png\" alt=\"\" class=\"circle\">";
                        else
                            htmlcode += "<img src=\""+tab['news'][i]['profil_pict']+"\" alt=\"\" class=\"circle\">";
                        htmlcode += "<span class=\"title\">"+tab['news'][i]['login']+"</span>";
                        notif.innerHTML = htmlcode;
                        notif.addEventListener("mouseover", set_old);
                        notif.id_notif = tab['news'][i]['id_notif'];
                        document.getElementById("visits_block_content").getElementsByTagName("UL")[0].appendChild(notif);
                    }
                    console.log(tab);
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
        //console.log(evt.currentTarget);
        var real = evt.currentTarget;
        $.post(
            'set_new_to_old',
            'action=newold&notif=' + real.id_notif,
            function (text){
                if (text == "ok")
                {
                    console.log(real);
                    real.className = "collection-item avatar old_notif";
                    real.removeEventListener("mouseover", set_old);
                }
                else
                    console.log(text);
            },
            'text' 
        );
    }
});