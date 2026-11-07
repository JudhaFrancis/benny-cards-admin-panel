/**
 * Staff options limited to one branch (users.branch_ids).
 *
 * @param {Array} options   [{ label, value, branch_ids }]
 * @param {number|null} branchId  the order's branch; none = all staff
 * @param {Array|string} current  value(s) already saved, always kept so they still show
 */
export function staffForBranch(options, branchId, current = []) {
  const list = options || [];
  if (!branchId) return list;

  const id = Number(branchId);
  const filtered = list.filter((o) => (o.branch_ids || []).map(Number).includes(id));

  const saved = (Array.isArray(current) ? current : [current]).filter(Boolean);
  saved.forEach((name) => {
    if (!filtered.some((o) => o.value === name)) {
      filtered.push({ label: `${name} (other branch)`, value: name });
    }
  });

  return filtered;
}
