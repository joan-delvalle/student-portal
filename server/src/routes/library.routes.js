import express from "express";
import { getLibraryByStudentId } from "../services/library.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const records = getLibraryByStudentId(req.params.studentId);

  res.json({
    studentId: req.params.studentId,
    records
  });
});

export default router;