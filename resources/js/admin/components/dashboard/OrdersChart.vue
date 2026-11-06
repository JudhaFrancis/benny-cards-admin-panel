<template>
  <div class="h-[350px] w-full">
    <apexchart
      type="area"
      height="100%"
      :options="chartOptions"
      :series="series"
    ></apexchart>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});

const series = ref([
  {
    name: "Orders",
    data: [],
  },
]);

const chartOptions = ref({
  chart: {
    toolbar: { show: false },
    zoom: { enabled: false },
  },
  colors: ["#0F172A"],
  dataLabels: { enabled: false },
  stroke: {
    curve: "smooth",
    width: 2,
  },
  fill: {
    type: "gradient",
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.3,
      opacityTo: 0,
      stops: [0, 95],
    },
  },
  xaxis: {
    categories: [],
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      style: {
        colors: "#94A3B8",
        fontSize: "12px",
        fontWeight: 600,
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: "#94A3B8",
        fontSize: "12px",
        fontWeight: 600,
      },
    },
  },
  grid: {
    borderColor: "#F1F5F9",
    strokeDashArray: 4,
  },
  tooltip: {
    theme: "light",
    x: { show: true },
    y: {
      formatter: (val) => `${val} Orders`,
    },
  },
});

watch(
  () => props.data,
  (newData) => {
    if (newData && newData.length > 0) {
      series.value[0].data = newData.map((item) => item.count);
      chartOptions.value = {
        ...chartOptions.value,
        xaxis: {
          ...chartOptions.value.xaxis,
          categories: newData.map((item) => item.month),
        },
      };
    }
  },
  { immediate: true }
);
</script>
