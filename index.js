//INDEX.JS
const express = require('express')
const cors = require('cors')
const http = require('http')
const socketIo = require('socket.io')
const router = require('./routes/routes')

const port = 9000
const app = express()

const server = http.createServer(app)

const io = socketIo(server, {
    cors: {
        origin: true,
        methods: ["*"]
    }
})
app.set('io', io)

app.use(cors({ credentials: true, origin: true }), express.json(), router)

io.on('connection', (socket) => {
  console.log('aya client konek yeuh')

  socket.on('siswa-transaksi-kantin', () => {
    io.emit('siswa-transaksi-kantin')
  })

  socket.on('siswa-transaksi-laundry', () => {
    io.emit('siswa-transaksi-laundry')
  })

  socket.on('usaha-pengajuan', () => {
    io.emit('usaha-pengajuan')
  })

  socket.on('disconnect', () => {
    console.log('client diskonek')
  })
})

server.listen(port, () => {
    console.log(`Server started at: http://0.0.0.0:${port}`)
})

