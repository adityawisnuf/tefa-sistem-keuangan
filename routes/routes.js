const express = require("express");
const router = express.Router();

router.route("/").get((_, res) => {
    res.json("wadidawww......");
});

router.route("/siswa-transaksi").post((req, res) => {
    const io = req.app.get("io");
    const { role, roleId } = req.body;

    if (!io) {
        res.status(500).json({ error: "socket.io not found" });
    }

    if (!role || !roleId) {
        res.status(422).json({ error: "role or roleId is missing" });
    }

    io.emit("siswa-transaksi", { role, roleId });
    res.status(200).json({ message: `event emitted to ${role}` });
});

router.route("/usaha-transaksi").post((req, res) => {
    const io = req.app.get("io");
    const { roleId } = req.body;

    if (!io) {
        res.status(500).json({ error: "socket.io not found" });
    }

    if (!roleId) {
        res.status(422).json({ error: "roleId is missing" });
    }

    io.emit("usaha-transaksi", { roleId });
    res.status(200).json({ message: `event emitted to ${role}` });
});

router.route("/usaha-pengajuan").post((req, res) => {
    const io = req.app.get("io");
    const { roleId } = req.body;

    if (!io) {
        res.status(500).json({ error: "socket.io not found" });
    }

    if (!roleId) {
        res.status(422).json({ error: "roleId is missing" });
    }

    io.emit("usaha-pengajuan", { roleId });
    res.status(200).json({ message: `event emitted to ${role}` });
});

router.route("/bendahara-pengajuan").post((req, res) => {
    const io = req.app.get("io");
    const { role, roleId } = req.body;

    if (!io) {
        res.status(500).json({ error: "socket.io not found" });
    }

    if (!role || !roleId) {
        res.status(422).json({ error: "role or roleId is missing" });
    }

    io.emit("bendahara-pengajuan", { role, roleId });
    res.status(200).json({ message: `event emitted to ${role}` });
});

module.exports = router;
