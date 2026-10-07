import { libraryRecords } from "../data/library.data.js";

export function getLibraryByStudentId(studentId) {
  return libraryRecords.filter(
    record => record.studentId === studentId
  );
}