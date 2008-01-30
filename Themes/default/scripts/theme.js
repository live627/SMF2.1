// Add a fix for code stuff?
if ((is_ie && !is_ie4) || is_safari13beta || is_ff)
	add_load_event(smf_codeBoxFix);

// The purpose of this code is to fix the height of overflow: auto div blocks, because IE can't figure it out for itself.
function smf_codeBoxFix()
{
	var codeFix = document.getElementsByTagName("div");

	for (var i = codeFix.length - 1; i > 0; i--)
	{
		if (is_safari13beta)
		{
			if ((codeFix[i].className == "code" || codeFix[i].className == "post" || codeFix[i].className == "signature") && codeFix[i].offsetHeight < 20)
				codeFix[i].style.height = (codeFix[i].offsetHeight + 20) + "px";
		}
		else if (is_ff)
		{
			if (codeFix[i].className == "code" && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0))
				codeFix[i].style.overflow = "scroll";
		}
		else
		{
			if (codeFix[i].currentStyle.overflow == "auto" && (codeFix[i].currentStyle.height == "" || codeFix[i].currentStyle.height == "auto") && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0) && (codeFix[i].offsetHeight != 0 || codeFix[i].className == "code"))
				codeFix[i].style.height = (codeFix[i].offsetHeight + 36) + "px";
		}
	}
}

function smfFooterHighlight(element, value)
{
	element.src = smf_images_url + "/" + (value ? "h_" : "") + element.id + ".gif";
}