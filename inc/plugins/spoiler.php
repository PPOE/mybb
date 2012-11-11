<?php
$plugins->add_hook("parse_message", "spoiler_run");
function spoiler_info()
{
	return array(
		"name"		=> "Spoiler BBCode",
		"description"	=> "Hides text specified in the [spoiler] tag.",
		"website"		=> "http://www.sephiroth.ws",
		"author"		=> "Sephiroth",
		"authorsite"	=> "http://www.sephiroth.ws",
		"version"		=> "1.6",
		"guid"		=> "795e4538050784720a1196a8b6e8065f",
		"compatibility" => "16*"
	);
}
function spoiler_activate()
{
}
function spoiler_deactivate()
{
}
function spoiler_run($message)
{

	// Assign pattern and replace values.
	$pattern = array("#\[spoiler=(?:&quot;|\"|')?(.*?)[\"']?(?:&quot;|\"|')?\](.*?)\[\/spoiler\](\r\n?|\n?)#si", "#\[spoiler\](.*?)\[\/spoiler\](\r\n?|\n?)#si");

	$replace = array("<div><div class=\"spoiler_header\">$1 <a href=\"javascript:void(0);\" onclick=\"javascript:if(parentNode.parentNode.getElementsByTagName('div')[1].style.display=='block'){parentNode.parentNode.getElementsByTagName('div')[1].style.display='none';this.innerHTML='(Einblenden)';}else {parentNode.parentNode.getElementsByTagName('div')[1].style.display='block';this.innerHTML='(Ausblenden)';}\">(Einblenden)</a></div><div class=\"spoiler_body\" style=\"display: none;\">$2</div></div>", "<div><div class=\"spoiler_header\">Spoiler <a href=\"javascript:void(0);\" onclick=\"javascript:if(parentNode.parentNode.getElementsByTagName('div')[1].style.display=='block'){parentNode.parentNode.getElementsByTagName('div')[1].style.display='none';this.innerHTML='(Einblenden)';}else {parentNode.parentNode.getElementsByTagName('div')[1].style.display='block';this.innerHTML='(Ausblenden)';}\">(Einblenden)</a></div><div class=\"spoiler_body\" style=\"display: none;\">$1</div></div>");

	while(preg_match($pattern[0], $message) or preg_match($pattern[1], $message))
	{
		$message = preg_replace($pattern, $replace, $message);
	}
	$find = array(
		"#<div class=\"spoiler_body\">(\r\n?|\n?)#",
		"#(\r\n?|\n?)</div>#"
	);

	$replace = array(
		"<div class=\"spoiler_body\">",
		"</div>"
	);
	$message = preg_replace($find, $replace, $message);
	return $message;
}
?>
