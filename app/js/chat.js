$(document).ready(function()
{
    var data = JSON.parse(useractiv);
    function select_user(id)
    {
        for(var i = 0; i < data.length; i++)
        {
            if (data[i]['id'] == id)
            {
                console.log(data[i]);
                $("#current_profil_info").find(".default_profil_info").hide();
                $("#profil_name, #profil_image, #profil_link").show();
                $("#profil_name").children("p").html(data[i]['login']);
                if (data[i]['profil_pict'] == "#")
                    $("#profil_image").children("img").attr("src", "/matcha/app/css/image/Photo-non-disponible.png");
                else
                    $("#profil_image").children("img").attr("src", data[i]['profil_pict']);
                $("#profil_link").children("a").attr("href", "/matcha/lookat/"+data[i]['login']);
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

    $("#notif_button").click(function(){
        $("#chat_container").css("display", "none");
        $("#notif_container").css("display", "block");
    });

    $("#chat_button").click(function(){
        $("#chat_container").css("display", "block");
        $("#notif_container").css("display", "none");
    });
});