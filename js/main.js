function searchboxFocus()
{
	document.getElementById("searchbox").placeholder = "";
	document.getElementById("searchbox").style.width = "200px";
}

function searchboxBlur()
{
	document.getElementById("searchbox").placeholder = "search";
	document.getElementById("searchbox").style.width = "62px";
	document.getElementById("searchbox").value = "";
}

function mapsAnimation(mapid)
{
	document.getElementById(mapid).classList.add('map-imageroll-animated');
}