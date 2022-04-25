import "cookies.js";

let url;

//отправка сообщения
document.getElementById("button").onclick = function () {
    var txt = {
        chat_id: getCookie('id_chat'),
        message: document.getElementById("message").value
    };

    url = 'http://195.140.146.82/messages/add';

    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(txt),
        contentType: "application/json",
        dataType: "json",
        headers: { 'Token': getCookie('token') },
        success: function (data, text, status) {
            window.location.href = 'chat.html';
        }
    });
};

//получение сообщений
function getMessages() {
    let chat_id = {
        chat_id: getCookie('id_chat')
    };

    url = 'http://195.140.146.82/messages/get';

    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(chat_id),
        contentType: "application/json",
        dataType: "json",
        headers: { 'Token': getCookie('token') },
        success: function (data, text, status) {
            if (data[0]['status'] == 200) {
                var me = getCookie('id_me');
                var messages = data[1];
                for (var i = 0; i < messages.length; i++) {
                    if (messages[i]['user'] == me) {
                        var blok = document.getElementById("blok");
                        var str = '<div class="u-align-left u-container-style u-expanded-width u-group u-shape-rectangle u-group-2"><div class="u-container-layout u-container-layout-4"><div class="u-container-style u-group u-shape-rectangle u-group-3"><div class="u-container-layout u-valign-top u-container-layout-5"><div class="u-border-1 u-border-grey-75 u-container-style u-custom-color-2 u-group u-radius-30 u-shape-round u-group-4"><div class="u-container-layout u-container-layout-6"><h3 class="u-text u-text-1">' + messages[i]['message'] + '<br></h3></div></div></div></div></div></div>';
                        blok.innerHTML += str;
                    }
                    else {
                        var blok = document.getElementById("blok");
                        var str = '<div class="u-align-right u-container-style u-expanded-width u-group u-shape-rectangle u-group-5"><div class="u-container-layout u-container-layout-7"><div class="u-container-style u-group u-shape-rectangle u-group-6"><div class="u-container-layout u-container-layout-8"><div class="u-border-1 u-border-grey-75 u-container-style u-custom-color-3 u-group u-radius-30 u-shape-round u-group-7"><div class="u-container-layout u-container-layout-9"><h3 class="u-text u-text-body-color u-text-2">' + messages[i]['message'] + '<br></h3></div></div></div></div></div></div>';
                        blok.innerHTML += str;
                    }
                }
            }
        }
    });
};

getMessages();

//назад
document.getElementById("exit").onclick = function () {
    
    window.location.href = 'my.html';
};

