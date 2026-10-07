import express from "express";
import {
  getAnnouncements,
  getAnnouncementById
} from "../services/announcement.service.js";

const router = express.Router();

router.get("/", (req, res) => {
  res.json(getAnnouncements());
});

router.get("/:id", (req, res) => {
  const announcement = getAnnouncementById(req.params.id);

  if (!announcement) {
    return res.status(404).json({
      type: "about:blank",
      title: "Announcement Not Found",
      status: 404,
      detail: "The requested announcement does not exist."
    });
  }

  res.json(announcement);
});

export default router;