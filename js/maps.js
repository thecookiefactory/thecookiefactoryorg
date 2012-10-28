var dict = {};

function findKeyframesRule(rule)
    {
        var ss = document.styleSheets;
        for (var i = 0; i < ss.length; ++i) {
            for (var j = 0; j < ss[i].cssRules.length; ++j) {
                if (ss[i].cssRules[j].type == window.CSSRule.WEBKIT_KEYFRAMES_RULE && ss[i].cssRules[j].name == rule)
                    return ss[i].cssRules[j];
            }
        }
        
        return null;
    }

function endAnimation(mapid, direction) {
        document.getElementById(mapid).style.webkitAnimationName = "none";
        position = dict[mapid] + direction;
        document.getElementById(mapid).style.left = (0 - (position * 900))+"px";
        dict[mapid] = position
    }

function startAnimation(mapid, direction) {
        document.getElementById(mapid).style.webkitAnimationName = "none";
        var keyframes = findKeyframesRule("scrolling");
        if (mapid in dict) {
                position = dict[mapid]
            } else {
                dict[mapid] = 0
            }
        keyframes.deleteRule("from");
        keyframes.deleteRule("to");
        keyframes.insertRule("from { -webkit-transform: translateX(0px); }");
        keyframes.insertRule("to { -webkit-transform: translateX("+direction*-900+"px); }");
        
        // assign the animation to our element (which will cause the animation to run)
        document.getElementById(mapid).style.webkitAnimationName = "scrolling";
        setTimeout(function() {endAnimation(mapid);}, 1200);      
    }