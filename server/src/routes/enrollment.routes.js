import express from "express";
import {
  getEnrollment,
  createEnrollment
} from "../services/enrollment.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const enrollment = getEnrollment(req.params.studentId);

  if (!enrollment) {
    return res.status(404).json({
      type: "about:blank",
      title: "Enrollment Not Found",
      status: 404,
      detail: "The enrollment record does not exist."
    });
  }

  res.json(enrollment);
});

router.post("/:studentId", (req, res) => {
  const { semester, schoolYear, subjects } = req.body;

  if (!semester || !schoolYear || !Array.isArray(subjects)) {
    return res.status(400).json({
      type: "about:blank",
      title: "Invalid Enrollment",
      status: 400,
      detail: "semester, schoolYear, and subjects are required."
    });
  }

  const enrollment = createEnrollment(
    req.params.studentId,
    {
      semester,
      schoolYear,
      subjects
    }
  );

  res.status(201).json(enrollment);
});

export default router;