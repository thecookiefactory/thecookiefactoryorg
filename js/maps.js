var posdict = {};
var lockdict = {};
var lendict = {};

function initialize(mapid) {
    posdict[mapid] = 0;

    if (lendict[mapid] > 1) {
        arrowFade(mapid, "in", "right");
    }

    setLocks(mapid)

    return;
}

function setLocks(mapid) {
    lockdict[mapid+"-left"] = 1
    lockdict[mapid+"-right"] = 1

    if (posdict[mapid] < lendict[mapid] - 1) {
        lockdict[mapid+"-right"] = 0
    }

    if (posdict[mapid] > 0) {
        lockdict[mapid+"-left"] = 0
    }

    return;
}

function findKeyframesRule(rule) {
    var ss = document.styleSheets;
    var result = new Array();
    for (var j = 0; j < ss[0].cssRules.length; ++j) {
        if (ss[0].cssRules[j].name == rule)
            result.push(ss[0].cssRules[j]);
    }
    
    return result;
}

function endImagerollScrolling(mapid, direction) {
    document.getElementById(mapid).className = "map-imageroll";
    document.getElementById(mapid).style.left = (0 - (posdict[mapid] * 900))+"px";
    setLocks(mapid);
}

function startImagerollScrolling(arrowid, direction) {
    if (lockdict[arrowid] == 1) {
        return;
    }

    mapid = arrowid.slice(0,arrowid.search("left")+arrowid.search("right"));

    lockdict[mapid+"-left"] = 1
    lockdict[mapid+"-right"] = 1

    if (posdict[mapid] < lendict[mapid] - 1) {
        arrowFade(mapid, "out", "right"); 
    }

    if (posdict[mapid] > 0) {
        arrowFade(mapid, "out", "left");
    }

    elem = document.getElementById(mapid);
    elem.className = "map-imageroll";
    var keyframes = findKeyframesRule("scrolling");
    posdict[mapid] += direction;

    for (var i = 0; i < keyframes.length; ++i) {
        if (keyframes[i].deleteRule) {
            keyframes[i].deleteRule(0);
            keyframes[i].deleteRule(1);
        } else {
            keyframes[i].removeRule("from");
            keyframes[i].removeRule("to");
        }
        keyframes[i].insertRule("from { transform: translateX(0px); -webkit-transform: translateX(0px); -moz-transform: translateX(0px); -o-transform: translateX(0px); }");
        keyframes[i].insertRule("to { transform: translateX("+direction*-900+"px); -webkit-transform: translateX("+direction*-900+"px); -moz-transform: translateX("+direction*-900+"px); -o-transform: translateX("+direction*-900+"px); }");
    }

    elem.className = "map-imageroll map-scrolling";

    if (posdict[mapid] < lendict[mapid] - 1) {
        setTimeout(function() {arrowFade(mapid, "in", "right");}, 900); 
    }

    if (posdict[mapid] > 0) {
        setTimeout(function() {arrowFade(mapid, "in", "left");}, 900);  
    }
    
    setTimeout(function() {endImagerollScrolling(mapid, direction);}, 1200);          
}

function arrowFade(mapid, direction, arrow) {
    // mapid: string (ex. "map-1")
    // direction: string ("in" or "out")
    // arrow: string ("left" or "right")

    arrowelem = document.getElementById(mapid+"-"+arrow);
    arrowelem.className = "map-"+arrow+"arrow map-fade"+direction;

    if (direction == "out") {
        arrowelem.className = "map-"+arrow+"arrow map-fadeout";
        setTimeout(function() {arrowelem.className = "map-"+arrow+"arrow map-fadeout map-arrow-disabled";}, 100);
    }

    if (direction == "in") {
        arrowelem.className = "map-"+arrow+"arrow map-fadein map-arrow-disabled";
        setTimeout(function() {arrowelem.className = "map-"+arrow+"arrow map-fadein"}, 200);
    }

    return;
}