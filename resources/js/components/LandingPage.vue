<template>
    <section class="content">
        <div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ totalTransactions }}</h3>
                            <p>Total Transactions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ totalAmount }}<sup style="font-size: 20px">USD</sup></h3>
                            <p>Total Amount Processed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ newTransactions }}</h3>
                            <p>New Transactions Today</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ failedTransactions }}</h3>
                            <p>Failed Transactions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="row">
                <!-- Line Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Transactions Over Time</h3>
                        </div>
                        <div class="card-body">
                            <apexchart type="line" :options="lineChartOptions" :series="lineChartSeries"></apexchart>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Transaction Categories</h3>
                        </div>
                        <div class="card-body">
                            <apexchart type="bar" :options="barChartOptions" :series="barChartSeries"></apexchart>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Transaction Distribution</h3>
                        </div>
                        <div class="card-body">
                            <apexchart type="pie" :options="pieChartOptions" :series="pieChartSeries"></apexchart>
                        </div>
                    </div>
                </div>

                <!-- Donut Chart (Fourth Graph) -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Method Distribution</h3>
                        </div>
                        <div class="card-body">
                            <apexchart type="donut" :options="donutChartOptions" :series="donutChartSeries"></apexchart>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import VueApexCharts from "vue3-apexcharts";
import { defineComponent } from "vue";

export default defineComponent({
    name: "Dashboard",
    components: {
        apexchart: VueApexCharts,
    },
    data() {
        return {
            totalTransactions: 1524,
            totalAmount: 32456.45,
            newTransactions: 100,
            failedTransactions: 10,

            // Line Chart Data (Transactions Over Time)
            lineChartOptions: {
                chart: {
                    id: "line-chart",
                },
                xaxis: {
                    categories: ["January", "February", "March", "April", "May", "June", "July"],
                },
                title: {
                    text: "Monthly Transactions",
                },
            },
            lineChartSeries: [
                {
                    name: "Transactions",
                    data: [10, 15, 9, 12, 14, 20, 30],
                },
            ],

            // Bar Chart Data (Transaction Categories)
            barChartOptions: {
                chart: {
                    id: "bar-chart",
                },
                title: {
                    text: "Transaction Categories",
                },
                xaxis: {
                    categories: ["Card Payments", "Mobile Payments", "Bank Transfers", "Others"],
                },
            },
            barChartSeries: [
                {
                    name: "Amount Processed",
                    data: [5000, 12000, 8000, 2000],
                },
            ],

            // Pie Chart Data (Transaction Distribution)
            pieChartOptions: {
                chart: {
                    id: "pie-chart",
                },
                labels: ["Card Payments", "Mobile Payments", "Bank Transfers", "Others"],
                title: {
                    text: "Transaction Distribution",
                },
            },
            pieChartSeries: [5000, 12000, 8000, 2000],

            // Donut Chart Data (Payment Method Distribution)
            donutChartOptions: {
                chart: {
                    id: "donut-chart",
                    type: "donut",
                },
                labels: ["Visa", "Mastercard", "Mobile Money", "Bank Transfers"],
                title: {
                    text: "Payment Method Distribution",
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: "50%",
                        },
                    },
                },
            },
            donutChartSeries: [30, 25, 20, 25],
        };
    },
});
</script>

<style scoped>
/* Optional: Add your custom styles */
</style>
