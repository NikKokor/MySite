//регистрация
document.getElementById("button").onclick = function () {
    let user = {
        username: document.getElementById("username").value,
        password: document.getElementById("password").value
    };

    console.log(user);

    let url = 'http://195.140.146.82/user/reg';


    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(user),
        contentType: "application/json",
        dataType: "json",
        success: function (data) {
            console.log(200);
        }
    });
};
