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
                var info = document.getElementById("current_profil_info");
                info.innerHTML = "";
                info.innerHTML += '<img style="width:100px;" src="'+data[i]['profil_pict']+'"/>';
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
});