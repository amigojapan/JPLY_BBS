const fs = require("fs");
const https = require("https");
const net = require("net");
const WebSocket = require("ws");

//const IRC_HOST = "irc.dal.net";
const IRC_HOST = "irc.libera.chat";
const IRC_PORT = 6667;

/* TLS options */
const server = https.createServer({
    key:  fs.readFileSync("/etc/letsencrypt/live/swiss.korman.es-0001/privkey.pem"),
    cert: fs.readFileSync("/etc/letsencrypt/live/swiss.korman.es-0001/cert.pem")
});

const wss = new WebSocket.Server({ server });

server.listen(9001, () => {
    console.log("WSS server running on wss://localhost:9001");
});

wss.on("connection", (ws) => {
    console.log("Browser connected");

    let irc = null;
    let channel = null;

    ws.on("message", (msg) => {
        const data = JSON.parse(msg);

        if (data.type === "connect") {
            channel = data.channel;

            irc = net.connect(IRC_PORT, IRC_HOST, () => {
                sendIRC(`NICK ${data.nick}`);
                sendIRC(`USER ${data.nick} 0 * :${data.nick}`);
            });

            irc.on("data", (data) => {
                const lines = data.toString().split("\r\n");
            
                for (const line of lines) {
                    if (!line) continue;
            
                    console.log("IRC:", line);
            
                    // 1) Respond to server PINGs (must always work)
                    if (line.startsWith("PING")) {
                        sendIRC("PONG " + line.substring(5));
                        continue;
                    }
            
                    // 2) Server welcome → safe to JOIN channel
                    if (line.includes(" 001 ")) {
                        sendIRC("JOIN #VoxAssist");
                        continue;
                    }
            
                    // 3) Channel fully joined → notify web client
                    if (line.includes(" 366 ") && line.includes("#VoxAssist")) {
                        if (ws && ws.readyState === ws.OPEN) {
                            ws.send(JSON.stringify({
                                type: "status",
                                value: "connected"
                            }));
                        }
                        continue;
                    }
            
                    // 4) Forward normal channel messages to client
                    // (keep or adapt this to your existing format)
                    if (line.includes("PRIVMSG #VoxAssist")) {
                        if (ws && ws.readyState === ws.OPEN) {
                            ws.send(JSON.stringify({
                                type: "irc",
                                raw: line
                            }));
                        }
                    }
                }
            });            
            
            

            irc.on("close", () => ws.close());
            irc.on("error", () => ws.close());
        }

        if (data.type === "msg" && irc) {
            sendIRC(`PRIVMSG ${channel} :${data.text}`);
        }
    });

    ws.on("close", () => {
        console.log("Browser disconnected");
    
        if (irc) {
            irc.removeAllListeners();
            irc.destroy();   // ← IMPORTANT (not end)
            irc = null;
        }
    });    

    function sendIRC(cmd) {
        if (irc) {
            console.log("SEND:", cmd);
            irc.write(cmd + "\r\n");
        }
    }
});