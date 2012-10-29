var dict = {};

function findKeyframesRule(rule)
    {
        var ss = document.styleSheets;
        var result = new Array();
        for (var j = 0; j < ss[0].cssRules.length; ++j) {
            if (ss[0].cssRules[j].name == rule)
                result.push(ss[0].cssRules[j]);
        }
        
        return result;
    }

function endAnimation(mapid, direction) {
        document.getElementById(mapid).className = "map-imageroll";
        position = dict[mapid] + direction;
        document.getElementById(mapid).style.left = (0 - (position * 900))+"px";
        dict[mapid] = position
    }

function startAnimation(mapid, direction) {
        mapid = mapid.slice(0,-1);
        elem = document.getElementById(mapid)
        elem.className = "map-imageroll";
        var keyframes = findKeyframesRule("scrolling");
        if (mapid in dict) {
                position = dict[mapid]
            } else {
                dict[mapid] = 0
            }

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
        setTimeout(function() {endAnimation(mapid, direction);}, 1200);      
    }