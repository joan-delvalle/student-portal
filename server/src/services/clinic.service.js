import { clinicRecords } from "../data/clinic.data.js";

export function getClinicByStudentId(studentId) {
  return clinicRecords.filter(
    record => record.studentId === studentId
  );
}