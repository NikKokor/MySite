import axios from "axios";
const axios = require('axios');

(function putReturn(data = {}) {
    return axios({
        method: 'POST',
        url: "http://195.140.146.82/logbook/return",
        headers: "'Content-Type': 'application/json'",
        data: data
    });
})('putReturn')

function putReturn(data = {}) {
    return axios({
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
