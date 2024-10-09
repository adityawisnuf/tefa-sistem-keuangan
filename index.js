const express = require("express");
const cors = require("cors");
const http = require("http");
const socketIo = require("socket.io");
const router = require("./routes/routes");

const port = 9000;
const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: true,
        methods: ["*"],
    },  
});

app.set("io", io);
app.use(cors({ credentials: true, origin: true }), express.json(), router);

const clientConnections = {};

io.on("connection", (socket) => {
    socket.on("userConnected", (userId, socketId) => {
        clientConnections[userId] = socketId;
        console.log("user connected: ", socketId);
    });

    socket.on("remindFetch", (data) => {
        try {
            const targetSocketId = clientConnections[data.userId];
            if (!targetSocketId) {
                console.log(`User with ID ${data.userId} not found`);
                return;
            }

            io.to(targetSocketId).emit("remindFetch");
        } catch (error) {
            console.error(`Error emitting remindFetch event: ${error}`);
        }
    });
});

server.listen(port, () => {
    console.log(`Server started at: http://0.0.0.0:${port}`);
});
