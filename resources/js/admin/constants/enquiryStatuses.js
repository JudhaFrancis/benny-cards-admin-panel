export const ENQUIRY_STATUSES = {
  pending: {
    label: "Pending Approval",
    color: "bg-amber-500/10 text-amber-600 border-amber-500/20",
    badgeVariant: "warning",
    dot: "bg-amber-500",
  },
  approved: {
    label: "Approved",
    color: "bg-emerald-500/10 text-emerald-600 border-emerald-500/20",
    badgeVariant: "success",
    dot: "bg-emerald-500",
  },
  rejected: {
    label: "Rejected",
    color: "bg-rose-500/10 text-rose-600 border-rose-500/20",
    badgeVariant: "danger",
    dot: "bg-rose-500",
  },
};

export const getEnquiryStatusLabel = (status) =>
  ENQUIRY_STATUSES[status]?.label || (status ? status.replace(/_/g, " ") : "Pending Approval");

export const getEnquiryStatusClass = (status) =>
  ENQUIRY_STATUSES[status]?.color || "bg-slate-100 text-slate-800 border-slate-200";

