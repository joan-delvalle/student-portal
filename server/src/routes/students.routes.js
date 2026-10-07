import express from "express";
import { getStudentById } from "../services/student.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const student = getStudentById(req.params.studentId);

  if (!student) {
    return res.status(404).json({
      type: "about:blank",
      title: "Student Not Found",
      status: 404,
      detail: "The requested student does not exist."
    });
  }

  res.json(student);
});

export default router;