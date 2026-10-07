import { announcements } from "../data/announcements.data.js";

export function getAnnouncements() {
  return announcements;
}

export function getAnnouncementById(id) {
  return announcements.find(
    announcement => announcement.id === Number(id)
  );
}