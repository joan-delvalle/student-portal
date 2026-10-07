import { students } from "../data/students.data.js";

export function getStudentById(studentId) {
  return students.find(
    student => student.studentId === studentId
  );
}