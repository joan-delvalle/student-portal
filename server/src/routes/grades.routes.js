import express from "express";
import { getGradesByStudentId } from "../services/grades.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const grades = getGradesByStudentId(req.params.studentId);

  res.json({
    studentId: req.params.studentId,
    grades
  });
});

export default router;