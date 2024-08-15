const express = require('express')
const router = express.Router()

router.route('/').get((req, res) => {
    res.json('wadidawww......')
})

router.route('/send-saldo')
    .post((req, res) => {
        const io = req.app.get('io')
        const saldo = 1000;
        if (io) {
            // if(!saldo || isNaN(saldo)) 
            //     return res.status(422)
            //     .json({success: false, message: 'tidak ada saldo untuk dikirim'})

            io.emit('message', JSON.stringify({saldo: saldo}))
            res.json({ success: true, new_saldo: saldo })
        } else {
            res.status(500).json({ error: 'eweh socket.io' })
        }
    })

module.exports = router