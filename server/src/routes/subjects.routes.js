import express from "express";
import { getSubjectsByStudentId } from "../services/subject.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const subjects = getSubjectsByStudentId(req.params.studentId);

  res.json({
    studentId: req.params.studentId,
    subjects
  });
});

export default router;