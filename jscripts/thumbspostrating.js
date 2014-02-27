/**
 * Thumbs Post Rating 1.3 by TY Yew
 * thumbspostrating.js
 */


function thumbRate(tu,td,pid)
{
	new Ajax.Request('xmlhttp.php?action=tpr&tu=' + tu + '&td=' + td + '&pid=' + pid + "&ajax=1&my_post_key=" + my_post_key,{onComplete:thumbResponse});
	return false;
}

function thumbResponse(request)
{
	if(error = request.responseText.match(/<error>(.*)<\/error>/))
		alert("An error occurred when rating the post.\n\n" + error[1]);		
	else
	{
		response = request.responseText.split('||');
		if(response[0] != 'success')
			alert("An unknown error occurred when rating the post.");
		else
		{
			var pid = parseInt(response[1]);
			var x=document.getElementById('tpr_stat_' + pid).rows[0].cells;	
			if( response[4] == 1 )
			{
				x[1].innerHTML = '<div class="tpr_thumb tu1"></div>';
				x[2].innerHTML = '<div class="tpr_thumb td0"></div>';
			}
			else if( response[4] == -1 )
			{
				x[1].innerHTML = '<div class="tpr_thumb tu0"></div>';
				x[2].innerHTML = '<div class="tpr_thumb td1"></div>';
			}
			else if( response[4] == 0 )
			{
				x[1].innerHTML = '<a href="JavaScript:void(0);" class="tpr_thumb tu2" title="Rate thumbs up" onclick="return thumbRate(1,0,' + pid + ')" ></a>';
				x[2].innerHTML = '<a href="JavaScript:void(0);" class="tpr_thumb td2" title="Rate thumbs down" onclick="return thumbRate(0,1,' + pid + ')" ></a>';
			}
			else
			{
				alert('Error: Invalid rating input.')
			}

			x[0].innerHTML = parseInt(response[2]);
			x[3].innerHTML = parseInt(response[3]);

			if(response[5] == 'show_undo')
			{
				document.getElementById('tpr_remove_' + pid).innerHTML = '<a href="JavaScript:void(0);" onclick="thumbRate(0,0,' + pid + ')" >Undo rating</a><br />';
			}

			if(response[5] == 'hide_undo')
			{
				document.getElementById('tpr_remove_' + pid).innerHTML = '';
			}
		}
	}
	return false;
}
