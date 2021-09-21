import { createServer } from "http";
import { Server } from "socket.io";

const httpServer = createServer();
const io = new Server(httpServer, {
  // ...
});

io.on("connection", (socket) => {
  
    socket.join(socket.handshake.param.post_id);

});

io.of('/posts').on('push-comment', (data) => {

    io.of('/posts').in(data.post_id).fetchSockets().forEach((socket) => emit('comment', data.comment));

});

io.of('/posts').on('push-reply', (data) => {

    io.of('/posts').in(data.post_id).fetchSockets().forEach((socket) => emit('replay', data.reply));

});

httpServer.listen(3000);

console.log("server listen on 3000");