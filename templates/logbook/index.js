import axios from "axios";

export function putReturn(data = {}) {
    return axios({
        method: 'POST',
        url: "http://195.140.146.82/logbook/return",
        headers: "'Content-Type': 'application/json'",
        data: data
    });
}