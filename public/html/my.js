import "cookies.js";

//переход в чат
function fun(pers) {
    return function () {
        setCookie('id_me', me['id']);
        setCookie('id_user', pers['id']);

        url = 'http://195.140.146.82/chat/get';

        var user_id = {
            user_id: pers['id']
        };

        $.ajax({
            type: "POST",
            url: url,
            data: JSON.stringify(user_id),
            contentType: "application/json",
            dataType: "json",
            headers: { 'Token': getCookie('token') },
            success: function (data, status, st) {
                if (data['status'] == 200) {
                    setCookie('id_chat',data['chat']);
                    window.location.href = 'chat.html';
                }
                else {
                    url = 'http://195.140.146.82/chat/add';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: JSON.stringify(user_id),
                        contentType: "application/json",
                        dataType: "json",
                        headers: { 'Token': getCookie('token') },
                        success: function (data) {
                            url = 'http://195.140.146.82/chat/get';

                            $.ajax({
                                type: "POST",
                                url: url,
                                data: JSON.stringify(user_id),
                                contentType: "application/json",
                                dataType: "json",
                                headers: { 'Token': getCookie('token') },
                                success: function (data) {
                                    setCookie('id_chat',data['chat']);
                                    window.location.href = 'chat.html';
                                }
                            });

                        }
                    });
                }
            }
        });
    }
};

document.cookie = "id_me=" + "";
document.cookie = "id_user=" + "";
document.cookie = "id_chat=" + "";

var users = [];
var me = [];


let url = 'http://195.140.146.82/user/get_me';
//получение всех пользователей
$.ajax({
    type: "GET",
    url: url,
    contentType: "application/json",
    headers: { 'Token': getCookie('token') },
    success: function (data) {
        me = data;

        document.getElementById("me").textContent = me['username'];

        url = 'http://195.140.146.82/user/get_all';

        $.ajax({
            type: "GET",
            url: url,
            contentType: "application/json",
            success: function (data) {
                users = data;

                for (var i = 0; i < users.length; i++) {
                    if (users[i]['username'] == me['username']) {
                        users.splice(i, 1);
                    }
                }

                for (var i = 0; i < users.length; i++) {
                    var blok = document.getElementById("blok");
                    var str = '<div class="u-border-2 u-border-grey-75 u-container-style u-group u-group-4"><div class="u-container-layout u-container-layout-7"><a class="u-align-right u-btn u-button-style u-btn-4" id="chat_' + i + '">Написать<br></a><h3 class="u-align-left u-text u-text-default u-text-3">' + users[i]['username'] + '</h3></div></div>';

                    blok.innerHTML += str;

                }

                var pers = [];

                for (var i = 0; i < users.length; i++) {
                    pers[i] = document.getElementById('chat_' + i) || [];
                }

                for (var i = 0; i < pers.length; i++) {
                    pers[i].onclick = fun(users[i]);
                }
            }
        });
    }
});

//изменение username
document.getElementById("put_username").onclick = function () {
    let user = {
        username: document.getElementById("username").value
    };

    console.log(user);

    let url = 'http://195.140.146.82/user/put';


    $.ajax({
        type: "PUT",
        url: url,
        data: JSON.stringify(user),
        contentType: "application/json",
        dataType: "json",
        headers: { 'Token': getCookie('token') },
        success: function (data) {
            window.location.href = 'my.html';
        }
    });
};

//изменение password
document.getElementById("put_password").onclick = function () {
    let user = {
        old_password: document.getElementById("old_password").value,
        new_password: document.getElementById("new_password").value
    };

    let url = 'http://195.140.146.82/user/put';

    $.ajax({
        type: "PUT",
        url: url,
        data: JSON.stringify(user),
        contentType: "application/json",
        dataType: "json",
        headers: { 'Token': getCookie('token') },
        success: function (data) {
            window.location.href = 'main.html';
        }
    });
};

//выход из аккаунта
document.getElementById("exit").onclick = function () {
    deleteCookie('token');
    window.location.href = 'main.html';
};
