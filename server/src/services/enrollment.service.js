import { enrollments } from "../data/enrollment.data.js";

export function getEnrollment(studentId) {
  return enrollments.find(
    enrollment => enrollment.studentId === studentId
  );
}

export function createEnrollment(studentId, data) {
  const enrollment = {
    studentId,
    semester: data.semester,
    schoolYear: data.schoolYear,
    status: "Pending",
    subjects: data.subjects || []
  };

  enrollments.push(enrollment);

  return enrollment;
}