const express = require("express");
const router = express.Router();

router.route("/").get((_, res) => {
    res.json("wadidawww......");
});

router.route("/remind-fetch").post((req, res) => {
    const io = req.app.get("io");
    const { userId } = req.body;

    if (!io) {
        res.status(500).json({ error: "socket.io not found" });
    }

    if (!userId) {
        res.status(422).json({ error: "userId is missing" });
    }

    io.emit("remindFetch", { userId });
    res.status(200).json({ message: `event emitted.` });
});

module.exports = router;
