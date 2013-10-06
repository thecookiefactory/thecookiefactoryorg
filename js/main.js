function searchboxFocus() {
    document.getElementsByClassName("searchbox")[0].placeholder = "";
    document.getElementsByClassName("searchbox")[0].style.width = "200px";
}

function searchboxBlur() {
    document.getElementsByClassName("searchbox")[0].placeholder = "search";
    document.getElementsByClassName("searchbox")[0].style.width = "62px";
    document.getElementsByClassName("searchbox")[0].value = "";
}

function showLoginBar() {
    document.getElementsByClassName("nav-actionbar")[0].style.display = "none";
    document.getElementsByClassName("nav-loginbar")[0].style.display = "block";
}

function hideLoginBar() {
    document.getElementsByClassName("nav-actionbar")[0].style.display = "block";
    document.getElementsByClassName("nav-loginbar")[0].style.display = "none";
}

function jumpToWrapper() {
    window.location = "#wrapper";
}

function checkInputBox(elem) {
    if (elem.value) {
        elem.style.color = "#4188D2";
        elem.style.textAlign = "center";
    } else {
        elem.style.color = "#141414";
        elem.style.textAlign = "left";
    }

    filterInput(elem);
}

function filterInput(elem) {
    var error = "";

    if (elem.value.match(/\W/)) error += "Your username can contain English letters, numbers, and underscores only. ";
    if (!elem.value.match(/.{2,10}/)) error += "Your username must be 2 to 10 characters long. ";

    var ajax = new XMLHttpRequest();
    ajax.open("GET", "inc/checkuser.php?name=" + elem.value, true);
    ajax.send();
    ajax.onreadystatechange = function(){
        if (ajax.readyState === 4 && ajax.status === 200 && ajax.responseText != "0") {
            error += "Sorry, that username is already taken. ";
        }
    };

    elem.setCustomValidity(error);
}

function searchRedirect() {
    window.location = "/search" + document.getElementById("searchbox").value + "/";
}
