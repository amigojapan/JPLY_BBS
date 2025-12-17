-----------------------------------------------------------------------------------------
--
-- main.lua
--
-----------------------------------------------------------------------------------------

-- Your code here
local webView = native.newWebView( display.contentCenterX, display.contentCenterY, display.contentWidth, display.contentHeight )
if system.getInfo("platform") == "android" then
    webView:setNativeProperty("http.agent", "system")
end
webView:request( "https://amjp.psy-k.org/JPLY_BBS/TerminalEmulator.html", system.ResourceDirectory )
local onKeyEvent
onKeyEvent = function( event )
    local phase = event.phase
    local keyName = event.keyName
    if( (keyName == "back") and (phase == "down") ) then
        webView:back()
        --Runtime:removeEventListener( "key", onKeyEvent );
        --goToHome()
    end
    return true
end
Runtime:addEventListener( "key", onKeyEvent );

local function onAlertComplete( event )
    if event.action == "clicked" then
        local i = event.index
        if i == 1 then  -- "OK" button
            local paymentUrl = "https://amjp.psy-k.org/s-found2/list_apps.php"
            system.openURL("googlechrome://navigate?url=" .. paymentUrl)
        end
    end
end

local function webListener(event)
    if event.url and string.match(event.url, "googlepay") then
        --system.openURL(event.url)
        -- Check if Chrome is installed
        if not system.canOpenURL("googlechrome://") then
            -- Show alert if Chrome is not installed
            native.showAlert(
                "Install Chrome",
                "Please install Google Chrome to make Google pay payments. You can install it from the Google Play Store.",
                {"OK"}
            )
        else
            -- Chrome is installed, proceed with payment logic
            -- For example, open the payment page in Chrome
            native.showAlert(
                "try again in google Chrome",
                "Now invoking google chrome, please press purchase again from the list from within chrome.",
                {"OK"},
                onAlertComplete
            )
        end
        return true  -- Cancel loading in web view
    end
end
webView:addEventListener("urlRequest", webListener)