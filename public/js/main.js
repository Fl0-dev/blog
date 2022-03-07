
let btn = document.querySelector("#button");
let results = document.querySelector('#results');
const offset = 5;

btn.addEventListener("click", () => {
    fetch('http://127.0.0.1:8000/api?offset='+offset)
        .then(
            function (response) {
                return response.text()
            }
        )
        .then(
            function (content) {
                results.innerHTML += content
            }
        )
})

let input = document.querySelector("input");
let search = document.querySelector('#search');

input.addEventListener("keyup", ()=> {
    fetch()
})
