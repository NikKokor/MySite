function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, options = {}) {

    options = {
        path: '/',
        // при необходимости добавьте другие значения по умолчанию
        ...options
    };

    if (options.expires instanceof Date) {
        options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
        updatedCookie += "; " + optionKey;
        let optionValue = options[optionKey];
        if (optionValue !== true) {
            updatedCookie += "=" + optionValue;
        }
    }

    document.cookie = updatedCookie;
}

function deleteCookie(name) {
    setCookie(name, "", {
        'max-age': -1
    })
}

let url;

document.getElementById("button").onclick = function () {
    //отправка сообщения
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


function getMessages() {
    //получение сообщений
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

document.getElementById("exit").onclick = function () {
    //назад
    window.location.href = 'my.html';
};

