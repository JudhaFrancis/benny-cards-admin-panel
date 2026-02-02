<template>
  <div class="h-[350px] w-full flex items-center justify-center">
    <apexchart
      type="donut"
      width="100%"
      height="100%"
      :options="chartOptions"
      :series="series"
    ></apexchart>
  </div>
</template>

<script setup>
import { ref } from "vue";

const series = ref([68, 22, 10]);

const chartOptions = ref({
  chart: {
    type: "donut",
  },
  labels: ["Paid", "Pending", "Overdue"],
  colors: ["#10B981", "#F59E0B", "#F43F5E"], // emerald, amber, rose
  stroke: { width: 0 },
  plotOptions: {
    pie: {
      donut: {
        size: "75%",
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: "12px",
            fontWeight: 800,
            color: "#94A3B8",
          },
          value: {
            show: true,
            fontSize: "24px",
            fontWeight: 800,
            color: "#0F172A",
            formatter: (val) => `${val}%`,
          },
          total: {
            show: true,
            label: "Total Paid",
            color: "#94A3B8",
            formatter: (w) => {
              return w.globals.seriesTotals[0] + "%";
            },
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: {
    position: "bottom",
    fontSize: "12px",
    fontWeight: 700,
    labels: { colors: "#64748B" },
    markers: { radius: 12 },
  },
  tooltip: {
    y: {
      formatter: (val) => `${val}%`,
    },
  },
});
</script>
