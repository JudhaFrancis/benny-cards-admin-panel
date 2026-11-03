export const ENQUIRY_STATUSES = {
  new: {
    label: "New",
    color: "bg-blue-50 text-blue-700 border-blue-200",
    badgeVariant: "info",
    dot: "bg-blue-500",
  },
  under_review: {
    label: "Under Review",
    color: "bg-amber-50 text-amber-700 border-amber-200",
    badgeVariant: "warning",
    dot: "bg-amber-500",
  },
  quoted: {
    label: "Quoted",
    color: "bg-purple-50 text-purple-700 border-purple-200",
    badgeVariant: "secondary",
    dot: "bg-purple-500",
  },
  approved: {
    label: "Approved",
    color: "bg-indigo-50 text-indigo-700 border-indigo-200",
    badgeVariant: "primary",
    dot: "bg-indigo-500",
  },
  confirmed: {
    label: "Confirmed",
    color: "bg-emerald-50 text-emerald-700 border-emerald-200",
    badgeVariant: "success",
    dot: "bg-emerald-500",
  },
  not_approved: {
    label: "Not Approved",
    color: "bg-rose-50 text-rose-700 border-rose-200",
    badgeVariant: "danger",
    dot: "bg-rose-500",
  },
};

export const ENQUIRY_SOURCES = [
  { value: "walk_in", label: "Walk-In" },
  { value: "website", label: "Website" },
  { value: "instagram", label: "Instagram" },
  { value: "phone", label: "Phone Call" },
  { value: "whatsapp", label: "WhatsApp" },
  { value: "other", label: "Other" },
];
