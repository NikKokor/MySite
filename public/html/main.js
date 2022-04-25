document.getElementById("button").onclick = function () {
    let user = {
        username: document.getElementById("username").value,
        password: document.getElementById("password").value
    };

    console.log(user);

    let url = 'http://195.140.146.82/login';


    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(user),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function (data) {

            document.cookie = "token=" + data['token'] + "; path=/";
            console.log(document.cookie);
	    window.location.href = 'my.html';
        }
    });
};
