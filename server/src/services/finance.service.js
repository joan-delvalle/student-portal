import { finances } from "../data/finance.data.js";

export function getFinanceByStudentId(studentId) {
  return finances.find(
    finance => finance.studentId === studentId
  );
}