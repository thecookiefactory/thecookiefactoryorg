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

function checkInputBox(elem) {
    if (elem.value) {
        elem.style.color = "#4188D2";
        elem.style.textAlign = "center";
    } else {
        elem.style.color = "#141414";
        elem.style.textAlign = "left";
    }
}

function filterInput(event) {
    if (!String.fromCharCode(event.which).match(/\w/)) return false;
}
