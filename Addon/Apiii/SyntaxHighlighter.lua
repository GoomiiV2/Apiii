require "string"
--require "math"
require "table"
require "lib/lib_Slash"

-- ------------------------------------------
-- CONSTANTS
-- ------------------------------------------
local HOST = "YOURHOST/upload.php";
local SECERT = ""; -- Top notch security right here >,>

local blacklist = 
{
	["System.Logout"]=true,
	["System.Shutdown"]=true,
	["System.Reboot"]=true,
	["Sinvironment.GetNumFixedCamera"]=true,
	["Vehicle.GetAvailableComponents"]=true,
	["System.ReloadUI"]=true,
	["System.TakeScreenshot"]=true,
	["System.TestResolution"]=true
};

-- ------------------------------------------
-- VARIABLES
-- ------------------------------------------
local list = {}

-- ------------------------------------------
-- EVENT FUNCTIONS
-- ------------------------------------------
function OnComponentLoad()
	LIB_SLASH.BindCallback({slash_list="/apii", description="Export API", func=OnPlayerReady});
end

function OnPlayerReady()	
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Starting Api dump"});
	
	local opHost = System.GetOperatorSetting("ingame_host");
	local host = "";
	if (opHost:find("https://ingame-publictest.firefall.com/")) then
		host = "http://operator-v01-uw2-publictest.firefall.com/api/v1/products/Firefall_PublicTest";
	else
		host = "http://operator.firefall.com/api/v1/products/Firefall_Beta";
	end
	
	local status, err = pcall(HTTP.IssueRequest);
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="status: "..tostring(status).." Error: "..tostring(err)});
	
	HTTP.IssueRequest(host, "GET", nil, CB_Version);
end

-- ------------------------------------------
-- GENERAL FUNCTIONS
-- ------------------------------------------
function GetFunctions(group, namespace)
	list[namespace] = {}
	local str = ""
	for name, func in pairs(group) do
		if type(func) == "function" then
			table.insert(list[namespace], name)
		end
	end
end

function CB_Version(args, error)	

	local lastVer = Component.GetSetting("version");
	if (lastVer == args.build) then
		return;
	end
		
	Component.SaveSetting("version", args.build);
		
	local data = {};
	data.info = {};
	data.info.key = SECERT;
	data.info.env = args.environment;
	data.info.ver = args.build;
	data.info.level = args.patch_level;
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Version data: "..tostring(data)});
	
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Got version, now dumping functions....."});
	-- Too much data for one post :<
	table.insert(list, data);
	table.insert(list, GetNamespaceUsageString(ActivityDirector, "ActivityDirector"));
	table.insert(list, GetNamespaceUsageString(Chat, "Chat"));
	table.insert(list, GetNamespaceUsageString(Component, "Component"));
	table.insert(list, GetNamespaceUsageString(Database, "Database"));
	table.insert(list, GetNamespaceUsageString(Encounter, "Encounter"));
	table.insert(list, GetNamespaceUsageString(Friends, "Friends"));
	table.insert(list, GetNamespaceUsageString(Game, "Game"));
	table.insert(list, GetNamespaceUsageString(HTTP, "HTTP"));
	table.insert(list, GetNamespaceUsageString(Lobby, "Lobby"));
	table.insert(list, GetNamespaceUsageString(Market, "Market"));
	table.insert(list, GetNamespaceUsageString(Paperdoll, "Paperdoll"));
	table.insert(list, GetNamespaceUsageString(Playback, "Playback"));
	table.insert(list, GetNamespaceUsageString(Player, "Player"));
	table.insert(list, GetNamespaceUsageString(Radio, "Radio"));
	--table.insert(list, GetNamespaceUsageString(Replay, "Replay"));
	table.insert(list, GetNamespaceUsageString(Sinvironment, "Sinvironment"));
	table.insert(list, GetNamespaceUsageString(Squad, "Squad"));
	table.insert(list, GetNamespaceUsageString(System, "System"));
	table.insert(list, GetNamespaceUsageString(TwitchTV, "TwitchTV"));
	table.insert(list, GetNamespaceUsageString(Vehicle, "Vehicle"));
	table.insert(list, GetNamespaceUsageString(Voip, "Voip"));
	table.insert(list, GetNamespaceUsageString(X360, "X360"));
	--table.insert(list, GetNamespaceUsageString(Mail, "Mail"));
	table.insert(list, {"END"});
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Function dump done :>"});
	
	local count = 1;
	function SendNext()
		log(tostring(list[count]));
		HTTP.IssueRequest(HOST, "POST", list[count], function(args, err)
			if (count <= #list) then
				Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] "..count.." out of "..#list.." sent."});
				SendNext();
				count = count + 1;
			else
				Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Api transfer complete, Yayifications!"});
				list = {};
			end
		end);
	end
	Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="[Apiii] Starting Api transfer"});
	SendNext();
	
	--Component.GenerateEvent('MY_SYSTEM_MESSAGE', {text="Apiii Sent off the update :>"});
end

function CB_Result(args, error)

end

-- ------------------------------------------
-- UTILITY/RETURN FUNCTIONS
-- ------------------------------------------
function GetNamespaceUsageString(group, namespace)
	local data = {};
	data[namespace] = {};
	
  if group ~= nil then
  	for name, func in pairs(group) do
			if type(func) == "function" then
				data[namespace][name] = {};
			
				-- Don't call things like System.Shutdown, thats not good.
				if not (blacklist[namespace.."."..name]) then
			
					log(namespace.."."..name);
					local status, err = pcall(func);
					if status == false then
						data[namespace][name] = GetFunctionInfo(err);			
					end
				end
      end
		end
	end
	
	return data;
end

-- Pffft who needs Lua patterns :p
function GetFunctionInfo(useStr)
	-- Remove " < and >
	useStr = useStr:gsub("\"", "'");
	useStr = useStr:gsub("<", "");
	useStr = useStr:gsub(">", "");
	
	local useString = useStr;
	
	useStr = useStr:sub(8); -- Remove "Usage: "
	local temp = useStr:find(" = ") or 0;
	local returnVal = useStr:sub(0, temp);
	useStr = useStr:sub(temp+3);
	temp = useStr:find("%(") or 0;
	local args = useStr:sub(temp+1, -2);
	useStr = useStr:sub(temp+1);
	
	local data = {};
	data.usageString = useString;
	data.returnVal = returnVal;
	data.args = {};
	for token in string.gmatch(args, "[^,%s]+") do
		table.insert(data.args, token);
	end
	return data;
end
