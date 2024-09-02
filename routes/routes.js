const express = require('express')
const router = express.Router()

router.route('/').get((req, res) => {
    res.json('wadidawww......')
})

router.route('/siswa-pesan')
    .post((req, res) => {
        const io = req.app.get('io')
        if (io) {
            io.emit('siswa-pesan')
            res.json({ success: true })
        } else {
            res.status(500).json({ error: 'euweuh socket.io' })
        }
    })

router.route('/usaha-pengajuan')
    .post((req, res) => {
        const io = req.app.get('io')
        if (io) {
            io.emit('usaha-pengajuan')
            res.json({ success: true })
        } else {
            res.status(500).json({ error: 'euweuh socket.io' })
        }
    })

module.exports = router