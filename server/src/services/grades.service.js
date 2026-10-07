import { grades } from "../data/grades.data.js";

export function getGradesByStudentId(studentId) {
  return grades.filter(
    grade => grade.studentId === studentId
  );
}