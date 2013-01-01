function searchboxFocus() {
    document.getElementById("searchbox").placeholder = "";
    document.getElementById("searchbox").style.width = "200px";
}

function searchboxBlur() {
    document.getElementById("searchbox").placeholder = "search";
    document.getElementById("searchbox").style.width = "62px";
    document.getElementById("searchbox").value = "";
}

function showLoginBar() {
    document.getElementById("nav-actionbar").style.display = "none";
    document.getElementById("nav-loginbar").style.display = "block";
}

function hideLoginBar() {
    document.getElementById("nav-actionbar").style.display = "block";
    document.getElementById("nav-loginbar").style.display = "none";
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

function filterInput(elem, isregister) {
    var error = "";

    if (elem.name == "username") {
        if (elem.value.match(/\W/)) error += "Your username can contain English letters, numbers, and underscores only. ";
        if (!elem.value.match(/.{2,10}/)) error += "Your username must be 2 to 10 characters long. ";
        if (isregister) {
        var ajax = new XMLHttpRequest();
        ajax.open("GET", "inc/checkuser.php?name=" + elem.value, true);
        ajax.send();
        }
    } else if (elem.name == "password") {
        if (!elem.value.match(/.{6,30}/)) error += "Your password must be 6 to 30 characters long. ";
    } else if (elem.name == "email") {
        if (!elem.value.match(/\S+@\S+\.\S{2}/)) error += "You must enter a valid email address. ";
    }

    if (ajax) {
        ajax.onreadystatechange = function(){
            if (ajax.readyState === 4 && ajax.status === 200 && ajax.responseText != "0") {
                error += "Sorry, that username is already taken. ";
            }
            elem.setCustomValidity(error);
        }
    } else {
        elem.setCustomValidity(error);
    }
}
