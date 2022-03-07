
let btn = document.querySelector("#button");
let results = document.querySelector('#results');
let offset = 5;

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
    offset += 5
})

let input = document.querySelector("#input");
let searchList = document.querySelector('#searchList');

input.addEventListener("keyup", ()=> {
    fetch('apiSearch/'+ input.value)
        .then(response => {
            console.log(response)
                return response.json()
            }
        )
        .then(tab => {
            searchList.innerHTML="";
                for(const post of tab) {
                    let option = document.createElement('option');
                    option.value = post;
                    searchList.append(option);
                }
        })
})
