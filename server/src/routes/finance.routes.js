import express from "express";
import { getFinanceByStudentId } from "../services/finance.service.js";

const router = express.Router();

router.get("/:studentId", (req, res) => {
  const finance = getFinanceByStudentId(req.params.studentId);

  if (!finance) {
    return res.status(404).json({
      type: "about:blank",
      title: "Finance Record Not Found",
      status: 404,
      detail: "The finance record does not exist."
    });
  }

  res.json(finance);
});

export default router;