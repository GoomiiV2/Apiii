<html>
	<head>
		<title> Apiii : Firefall API Data [<?php echo $envNice ?>]</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="icon" type="image/png" href="images/Icon_thumper.png">
		<script src="js/jQuery-2.0.3.js"> </script>
		
		<script type="text/javascript">
			var clicky_site_ids = clicky_site_ids || [];
			clicky_site_ids.push(100696193);
			(function() {
			  var s = document.createElement('script');
			  s.type = 'text/javascript';
			  s.async = true;
			  s.src = '//static.getclicky.com/js';
			  ( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s );
			})();
		</script>
		
		<script>
			var pageData = null;
			var prodPageData = null;
			
			$.ajax(
			{
			  dataType: "json",
			  url: "assets/<?php echo $env ?>/api-<?php echo $env ?>.json",
			  data: null,
			  success: success,
			  async:false
			});
			
			function success(data)
			{
				pageData = data;
			}
			
			function successProd(data)
			{
				prodPageData = data;
			}
			
			if ("<?php echo $envNice ?>" == "Pts")
			{
				$.ajax(
				{
				  dataType: "json",
				  url: "assets/production/api-production.json",
				  data: null,
				  success: successProd,
				  async:false
				});
			}
		</script>
		
		<script>
		$(document).ready(function()
		{
			var apiView = $("#apiView");
			var subTitle = $("#subTitle");
			var verStr = pageData.info.ver+ ":" +pageData.info.level;
			subTitle.append(": "+verStr );
			delete pageData["info"];
			
			for (var key in pageData)
			{
				PrintCat(key, pageData[key]);
			}
			
			function PrintCat(key, data)
			{
				
				apiView.append(String.format('<h2 onClick="ToggleCat(\'{0}\')">{0}</h2>', key));
				var cat = apiView.append(String.format('<div id="{0}"></div>', key));
				var ordered = {};
				Object.keys(data).sort().forEach(function(lkey) {
					ordered[lkey] = data[lkey];
				});
				for(var k in ordered)
				{
					var wikiUrl = "http://firefall.gamepedia.com/"+key+"_"+k;
					
					var usage = "void? (Engine didn't say)";
					if (data[k].returnVal)
						usage = data[k].returnVal;
					var classes = "apiElm";
					
					if ("<?php echo $envNice ?>" == "Pts")
					{
						if (!CheckForFunc(prodPageData[key], k))
							classes += " apiElmNew";
					}
					
					$("#"+key).append(String.format('<a href="{0}"><div class="{1}">{2}.{3}{4}<span class="usage">Returns: {5}</span></div></a></br>', wikiUrl, classes, key, k, BuildArgs(data[k]), usage));
				}
				
				apiView.append(String.format('<div class="cat {0}"></div>', key));
			}
			
			function BuildArgs(data)
			{
				var str = "";
				if (data.args)
				{
					for (var i = 0; i < data.args.length; i++)
					{
						if (i != data.args.length-1)
							str += data.args[i] + ", ";
						else
							str += data.args[i];
					}
				}	
				return String.format("({0});", str);
			}
			
			function CheckForFunc(arr, func)
			{
				if (func in arr)
					return true;
				else
					return false;
			}
		});
		
		function ToggleCat(cat)
		{
			$("#"+cat).slideToggle(200);
		}
		
		String.format = function() 
		{
			// The string containing the format items (e.g. "{0}")
			// will and always has to be the first argument.
			var theString = arguments[0];
			// start with the second argument (i = 1)
			for (var i = 1; i < arguments.length; i++) 
			{
				// "gm" = RegEx options for Global search (more than one instance)
				// and for Multiline search
				var regEx = new RegExp("\\{" + (i - 1) + "\\}", "gm");
				theString = theString.replace(regEx, arguments[i]);
			}
			return theString;
		}
		</script>
		
	</head>
	<body onload="">
		<div id="title"> -= Apiii: <?php echo $envNice ?> =- <img id="oilspill" src="images/mascot.png"/> </div>
		<div id="subTitle"> Firefall &nbsp Lua  &nbsp API  &nbsp things </div>
		
		<div class="header" target="Usage"> -> Syntax Highlighters and Goodies
		<?php
			if ($envNice == "Pts")
				echo '<a href="live.php"><div id="pasteButton">View Live API</div></a>';
			else
				echo '<a href="pts.php"><div id="pasteButton">View PTS API</div></a>';
		?>
		</div>
		<div id="Usage" class="content" style="height: 96px;">
			<div class="assest">
				<a href="assets/<?php echo $env ?>/npp/lua.xml">
					<img src="images/npp.png" width="64px"/></br>
				</a>
					NotePad++
			</div>
			
			<div class="assest">
				<a href="assets/<?php echo $env ?>/api-<?php echo $env ?>.json">
					<img src="images/json.png" width="64px"/></br>
				</a>
					Json
			</div>
			
		</div>
		
		<div class="header" target="note"> -> Notes </div>
		<div id="note" class="content">
			The API here may not be complete but it should cover most of the functions and it should be up-to-date (it updates automatically :>).</br>
			Each function links to the Wiki page for it for more info, if the page doesn't exist why not be nice and make it? :></br>
			</br>
			<?php 
				if ($envNice == "Pts")
				{
					echo "Any line in red is function thats not on live so shinnnny :D </br></br>";
				}
			?>
			If you want an auto-complete file for your favorite editor feel free to write up a converter and message me with it on the forums.</br>
			<a href="converterExample.txt">Click here for an example</a></br>
			</br>
			For a list of events see <a href="http://forums.firefallthegame.com/community/threads/api-info-full-patch-1667.1124741/" target="_blank">here</a> that is if it is kept up-to-date.</br>
			</br>
			Thanks to Mavoc for the base addon side code for this :></br>
			</br>
			Contact: <a href="http://forums.firefallthegame.com/community/members/arkii.267799/" target="_blank">Arkii</a>, <a href="http://forums.firefall.com/community/members/darkcisum.584641/" target="_blank">DarkCisum</a>
		</div>
		
		<div class="header" target="apiView"> -> The Lua API </div>
		<div id="apiView" class="content">

		</div>
	</body>
</html>