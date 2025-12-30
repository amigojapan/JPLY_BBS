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

            irc.on("data", (buf) => {
                buf.toString().split("\r\n").forEach((line) => {
                    if (!line) return;

                    console.log("IRC:", line);

                    if (line.startsWith("PING")) {
                        sendIRC("PONG " + line.split(" ")[1]);
                        return;
                    }

                    ws.send(JSON.stringify({
                        type: "irc",
                        line
                    }));

                    if (line.includes(" 376 ") || line.includes(" 422 ")) {
                        sendIRC(`JOIN ${channel}`);
                    }
                });
            });

            irc.on("close", () => ws.close());
            irc.on("error", () => ws.close());
        }

        if (data.type === "msg" && irc) {
            sendIRC(`PRIVMSG ${channel} :${data.text}`);
        }
    });

    ws.on("close", () => {
        if (irc) irc.end();
        console.log("Browser disconnected");
    });

    function sendIRC(cmd) {
        if (irc) {
            console.log("SEND:", cmd);
            irc.write(cmd + "\r\n");
        }
    }
});