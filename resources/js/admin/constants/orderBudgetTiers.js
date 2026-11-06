export const BUDGET_TIER_OPTIONS = [
  { value: 'Platinum', label: 'Platinum', colorClass: 'bg-purple-100 text-purple-700 border-purple-200', dotClass: 'bg-purple-500' },
  { value: 'Gold', label: 'Gold', colorClass: 'bg-amber-100 text-amber-700 border-amber-200', dotClass: 'bg-amber-500' },
  { value: 'Silver', label: 'Silver', colorClass: 'bg-slate-100 text-slate-700 border-slate-200', dotClass: 'bg-slate-400' }
];

export const getBudgetTierClass = (tierValue) => {
  if (!tierValue || tierValue === 'None') return 'bg-gray-100 text-gray-700 border-gray-200';
  const option = BUDGET_TIER_OPTIONS.find(o => o.value === tierValue);
  return option ? option.colorClass : 'bg-gray-100 text-gray-700 border-gray-200';
};

export const getBudgetTierDotClass = (tierValue) => {
  if (!tierValue || tierValue === 'None') return 'bg-gray-400';
  const option = BUDGET_TIER_OPTIONS.find(o => o.value === tierValue);
  return option ? option.dotClass : 'bg-gray-500';
};

export const getBudgetTierLabel = (tierValue) => {
  if (!tierValue || tierValue === 'None') return '';
  const option = BUDGET_TIER_OPTIONS.find(o => o.value === tierValue);
  return option ? option.label : tierValue;
};
