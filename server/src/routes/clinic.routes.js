import express from "express";
import { getClinicByStudentId } from "../services/clinic.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const records = getClinicByStudentId(req.params.studentId);

  res.json({
    studentId: req.params.studentId,
    records
  });
});

export default router;