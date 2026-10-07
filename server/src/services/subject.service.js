import { subjects } from "../data/subjects.data.js";

export function getSubjectsByStudentId(studentId) {
  return subjects.filter(
    subject => subject.studentId === studentId
  );
}