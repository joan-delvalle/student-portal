import { schedules } from "../data/schedule.data.js";

export function getScheduleByStudentId(studentId) {
  return schedules.filter(
    schedule => schedule.studentId === studentId
  );
}