<template>
    <div class="flex flex-wrap items-center gap-3">
        <!-- Period Type -->
        <div class="w-full sm:w-40 lg:w-44">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Period</span>
            <Select v-model="localFilters.filter_type" @update:modelValue="onPeriodTypeChange">
                <SelectTrigger class="h-9 bg-slate-50/50">
                    <SelectValue placeholder="Period" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="day">Day</SelectItem>
                    <SelectItem value="week">Week</SelectItem>
                    <SelectItem value="month">Month</SelectItem>
                    <SelectItem value="year">Year</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Period Option -->
        <div class="w-full sm:w-40 lg:w-44">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Option</span>
            <Select v-model="localFilters.filter_option" @update:modelValue="onOptionChange">
                <SelectTrigger class="h-9 bg-slate-50/50">
                    <SelectValue placeholder="Option" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="opt in currentSubOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Custom Date/Month/Year Pickers -->
        <template v-if="localFilters.filter_option === 'custom'">
            <div class="w-full sm:w-44">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Date</span>
                <DatePicker v-model="localFilters.from_date" @update:modelValue="handleCustomSingleDate" customClass="h-9" placeholder="Select Date" />
            </div>
        </template>

        <!-- Active Range Display (Minimal) -->
        <div v-if="activeRangeLabel"
            class="flex items-center gap-1.5 text-[10px] font-bold text-primary bg-primary/5 px-2.5 py-1.5 rounded-lg h-9 self-end mb-[1px]">
            <CalendarIcon class="h-3 w-3" />
            {{ activeRangeLabel }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from "vue";
import { Calendar as CalendarIcon } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../ui/select";
import DatePicker from "../ui/pickers/DatePicker.vue";

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(["update:modelValue", "change"]);

const localFilters = reactive({ ...props.modelValue });

const subOptions = {
    day: [
        { label: "Today", value: "today" },
        { label: "Yesterday", value: "yesterday" },
        { label: "Day Before Yesterday", value: "before_yesterday" },
        { label: "Custom", value: "custom" },
    ],
    week: [
        { label: "This Week", value: "this_week" },
        { label: "Last Week", value: "last_week" },
        { label: "Last 2 Weeks", value: "last_2_weeks" },
        { label: "Custom", value: "custom" },
    ],
    month: [
        { label: "This Month", value: "this_month" },
        { label: "Last Month", value: "last_month" },
        { label: "Last 3 Months", value: "last_3_months" },
        { label: "Custom", value: "custom" },
    ],
    year: [
        { label: "This Year", value: "this_year" },
        { label: "Last Year", value: "last_year" },
        { label: "Last 3 Years", value: "last_3_years" },
        { label: "Custom", value: "custom" },
    ],
};

const currentSubOptions = computed(() => subOptions[localFilters.filter_type] || []);

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const todayDate = formatDate(new Date());
const todayMonth = todayDate.slice(0, 7);
const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear; i >= currentYear - 10; i--) {
        years.push(i.toString());
    }
    return years;
});

const onPeriodTypeChange = () => {
    localFilters.filter_option = subOptions[localFilters.filter_type][0].value;
    updateDateRange();
};

const onOptionChange = () => {
    updateDateRange();
};

const updateDateRange = () => {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    const type = localFilters.filter_type;
    const option = localFilters.filter_option;

    if (option === 'custom') {
        // Default to today when custom is selected
        localFilters.from_date = formatDate(today);
        localFilters.to_date = formatDate(today);
        return emitChange();
    }

    if (type === 'day') {
        if (option === 'today') {
            start = today;
            end = today;
        } else if (option === 'yesterday') {
            start.setDate(today.getDate() - 1);
            end.setDate(today.getDate() - 1);
        } else if (option === 'before_yesterday') {
            start.setDate(today.getDate() - 2);
            end.setDate(today.getDate() - 2);
        }
    } else if (type === 'week') {
        if (option === 'this_week') {
            const day = today.getDay();
            start.setDate(today.getDate() - day);
            end.setDate(today.getDate() + (6 - day));
        } else if (option === 'last_week') {
            const day = today.getDay();
            start.setDate(today.getDate() - day - 7);
            end.setDate(today.getDate() - day - 1);
        } else if (option === 'last_2_weeks') {
            start.setDate(today.getDate() - 14);
            end = today;
        }
    } else if (type === 'month') {
        if (option === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (option === 'last_month') {
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (option === 'last_3_months') {
            start = new Date(today.getFullYear(), today.getMonth() - 3, 1);
            end = today;
        }
    } else if (type === 'year') {
        if (option === 'this_year') {
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
        } else if (option === 'last_year') {
            start = new Date(today.getFullYear() - 1, 0, 1);
            end = new Date(today.getFullYear() - 1, 11, 31);
        } else if (option === 'last_3_years') {
            start = new Date(today.getFullYear() - 3, 0, 1);
            end = today;
        }
    }

    localFilters.from_date = formatDate(start);
    localFilters.to_date = formatDate(end);
    emitChange();
};

const handleCustomSingleDate = (val) => {
    localFilters.from_date = val;
    localFilters.to_date = val;
    emitChange();
};

const handleCustomWeek = (val) => {
    if(!val) return;
    const date = new Date(val);
    const day = date.getDay();
    const start = new Date(date);
    start.setDate(date.getDate() - day);
    const end = new Date(date);
    end.setDate(date.getDate() + (6 - day));
    
    localFilters.from_date = formatDate(start);
    localFilters.to_date = formatDate(end);
    emitChange();
};

const handleCustomMonth = (e) => {
    const val = e.target.value; // "YYYY-MM"
    if(!val) return;
    const [year, month] = val.split('-');
    const start = new Date(year, parseInt(month) - 1, 1);
    const end = new Date(year, parseInt(month), 0);
    
    localFilters.from_date = formatDate(start);
    localFilters.to_date = formatDate(end);
    emitChange();
};

const handleCustomYear = (val) => {
    if(!val) return;
    const start = new Date(val, 0, 1);
    const end = new Date(val, 11, 31);
    
    localFilters.from_date = formatDate(start);
    localFilters.to_date = formatDate(end);
    emitChange();
};

const activeRangeLabel = computed(() => {
    const type = localFilters.filter_type;
    const option = localFilters.filter_option;
    const start = localFilters.from_date;
    const end = localFilters.to_date;

    if (option !== 'custom') {
        const periodStr = type.charAt(0).toUpperCase() + type.slice(1);
        const optLabel = subOptions[type]?.find(o => o.value === option)?.label || option;
        return `${periodStr}: ${optLabel}`;
    }

    // For custom option, we always use a single date picker now
    const opt = { day: 'numeric', month: 'short', year: 'numeric' };
    return `Date: ${new Date(start).toLocaleDateString('en-GB', opt)}`;
});

const emitChange = () => {
    emit("update:modelValue", { ...localFilters });
    emit("change", { ...localFilters });
};

watch(() => props.modelValue, (newVal) => {
    Object.assign(localFilters, newVal);
}, { deep: true });

onMounted(() => {
    // Ensure the label is generated initially if provided
    emitChange();
});
</script>
