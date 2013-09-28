var imagewidth = 900;  // width of the displayed images, in pixels
var scrolltime = 1000; // time of the scroll animation, in milliseconds
var fadetime = 300;    // time of the fade animation, in milliseconds

var posdict = {};  // ex. posdict["map-1"] == 3
                   // the map-1 imageroll is currently showing the third image

var lockdict = {}; // ex. lockdict["map-1-right"] == 1
                   // clicking the right arrow on the map-1 imageroll won't
                   // have any effect

var lendict = {};  // ex. lendict["map-1"] == 4
                   // the map-1 imageroll consists of 4 images in total

function initialize(mapid) {
    // function is ran every time an imageroll is loaded
    // mapid: string (ex. "map-1")

    // sets a default position for each imageroll
    posdict[mapid] = 0;

    elem = document.getElementById(mapid);
    elem.style.transform = "translateX(0)";
    console.log(elem.style.transform);
    elem.style.webkitTransform = "translateX(0)";

    // makes the appropriate arrows visible
    if (lendict[mapid] > 1) arrowFade(mapid, "in", "right");

    // locks hidden arrows so that clicking on them won't do anything
    setLocks(mapid);

    return;
}

function setLocks(mapid) {
    // function locks all arrows that don't lead to any images
    // mapid: string (ex. "map-1")

    lockdict[mapid+"-left"] = 1;
    lockdict[mapid+"-right"] = 1;

    if (posdict[mapid] < lendict[mapid] - 1) lockdict[mapid+"-right"] = 0;
    if (posdict[mapid] > 0)                  lockdict[mapid+"-left"] = 0;

    return;
}

function arrowFade(mapid, direction, arrow) {
    // function fades the arrows in or out
    // mapid: string (ex. "map-1")
    // direction: string ("in" or "out")
    // arrow: string ("left" or "right")

    arrowelem = document.getElementById(mapid+"-"+arrow);

    if (direction == "out") {
        arrowelem.className = "map-"+arrow+"arrow map-fadeout";
        setTimeout(function() {arrowelem.className = "map-"+arrow+"arrow map-fadeout map-arrow-disabled";}, fadetime/3);
    } else if (direction == "in") {
        arrowelem.className = "map-"+arrow+"arrow map-fadein map-arrow-disabled";
        setTimeout(function() {arrowelem.className = "map-"+arrow+"arrow map-fadein";}, fadetime/3);
    }

    return;
}

function startImagerollScrolling(arrowid, direction) {
    // function animates imageroll
    // arrowid: string (ex. "map-1-right")
    // direction: integer (-1 for moving left; 1 for moving right)

    // don't start animating if the clicked arrow is locked
    if (lockdict[arrowid] == 1) return;

    // mapid: string (ex. "map-1")
    mapid = arrowid.slice(0,arrowid.search("left")+arrowid.search("right"));

    lockdict[mapid+"-left"] = 1;
    lockdict[mapid+"-right"] = 1;

    // fade out any currently visible arrows
    if (posdict[mapid] < lendict[mapid] - 1) arrowFade(mapid, "out", "right");
    if (posdict[mapid] > 0)                  arrowFade(mapid, "out", "left");

    posdict[mapid] += direction;

    // set transform values based on scroll direction
    elem = document.getElementById(mapid);
    if (elem.style.transform) {elem.style.transform = "translateX(" + (parseInt(elem.style.transform.slice(11), 10)+direction*-900).toString() + "px)";}
    if (elem.style.webkitTransform) {elem.style.webkitTransform = "translateX(" + (parseInt(elem.style.webkitTransform.slice(11), 10)+direction*-900).toString() + "px)";}

    // fade in the appropriate arrows
    if (posdict[mapid] < lendict[mapid] - 1) setTimeout(function() {arrowFade(mapid, "in", "right");}, scrolltime+100-fadetime);
    if (posdict[mapid] > 0)                  setTimeout(function() {arrowFade(mapid, "in", "left");},  scrolltime+100-fadetime);

    // set the new imagelocks
    setTimeout(function() {setLocks(mapid);}, scrolltime+100);

    return;
}

function animateDataPanel(elemid) {
    // function starts the animation on the data panels on the maps list
    // elemid: string (ex. "maps-moreinfo-1")

    direction = elemid.slice(4, 8);             // either "more" or "less"
    idnumber = elemid.slice(13, elemid.length); // map-moreinfo and map-lessinfo
                                                // are both 12 characters long

    document.getElementById("map-data-" + idnumber).className = "map-data map-data-" + direction;
    document.getElementById("map-actionbar-" + idnumber).className = "map-actionbar map-actionbar-" + direction;

    return;
}
