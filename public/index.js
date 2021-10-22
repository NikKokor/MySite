//import axiosa from "../node_modules/";

(function putReturn(data = {}) {
    return axiosa({
        method: 'POST',
        url: "http://195.140.146.82/logbook/return",
        headers: "'Content-Type': 'application/json'",
        data: data
    });
})('putReturn')

function putReturn(data = {}) {
    return axiosa({
        method: 'POST',
        url: "http://195.140.146.82/logbook/return",
        headers: "'Content-Type': 'application/json'",
        data: data
    });
}

function put(u_id, b_id) {
    let body = {
        user_id: u_id,
        book_id: b_id
    };
    return JSON.stringify(body);
}
