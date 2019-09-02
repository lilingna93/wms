const fetch = require('node-fetch');

let globalFetch = (...arg) => {
    let [api, params] = arg;
    return new Promise((resolve, reject) => {
        fetch(api, params).then(response => {
            if (response.ok) {
                response.json().then(data => {
                    resolve(data);
                })
            }
        })
    })
}


module.exports = {
    globalFetch
}