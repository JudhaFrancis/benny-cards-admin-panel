export const PRIORITY_OPTIONS = [
  { value: 'P1', label: 'Urgent', colorClass: 'bg-red-100 text-red-600 border-red-200', dotClass: 'bg-red-500' },
  { value: 'P2', label: 'High Priority', colorClass: 'bg-orange-100 text-orange-600 border-orange-200', dotClass: 'bg-orange-500' },
  { value: 'P3', label: 'Today', colorClass: 'bg-yellow-100 text-yellow-600 border-yellow-200', dotClass: 'bg-yellow-500' },
  { value: 'P4', label: 'Normal', colorClass: 'bg-green-100 text-green-600 border-green-200', dotClass: 'bg-green-500' }
];

export const getPriorityClass = (priorityValue) => {
  const option = PRIORITY_OPTIONS.find(o => o.value === priorityValue);
  return option ? option.colorClass : 'bg-gray-100 text-gray-700 border-gray-200';
};

export const getPriorityDotClass = (priorityValue) => {
  const option = PRIORITY_OPTIONS.find(o => o.value === priorityValue);
  return option ? option.dotClass : 'bg-gray-500';
};

export const getPriorityLabel = (priorityValue) => {
  const option = PRIORITY_OPTIONS.find(o => o.value === priorityValue);
  return option ? option.label : priorityValue;
};
