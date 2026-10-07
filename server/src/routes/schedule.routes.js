import express from "express";
import { getScheduleByStudentId } from "../services/schedule.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const schedule = getScheduleByStudentId(req.params.studentId);

  res.json({
    studentId: req.params.studentId,
    schedule
  });
});

export default router;