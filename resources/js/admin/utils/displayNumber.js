/**
 * Order and enquiry numbers are stored as PREFIX-MONYY### (e.g. OD-NOV26001, EQ-NOV26001).
 * The UI hides the 2-digit year, so they display as OD-NOV001 / EQ-NOV001.
 * This uses the same pattern the order screens use inline.
 */
export const formatDisplayNumber = (number) =>
  number ? number.replace(/-(?:[A-Z]{3})\d{2}/, (m) => m.slice(0, 4)) : number;

/**
 * Hide the year in every order/enquiry number inside a sentence
 * (e.g. API messages like "Enquiry EQ-NOV26002 approved. Order #OD-NOV26001 created.").
 */
export const hideYearInText = (text) =>
  typeof text === "string" ? text.replace(/\b(OD|EQ)-([A-Z]{3})\d{2}(?=\d{3}\b)/g, "$1-$2") : text;
