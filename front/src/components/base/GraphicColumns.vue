<script>
import {onMounted, ref} from 'vue';
import {
    // convertDate,
    // convertDateHour, convertDateText,
    // floatToDecimal
} from "@/composables/functions";
import http from "@/http";

export default {

    props: {
        url: String,
    },

    setup(props) {
        const graphic = ref({
            series: [],
            chartOptions: {}
        });

        const getData = async (datasets) => {


            const series = [{
                name: datasets.name,
                data: datasets.data
            }];
            const chartOptions = {
                chart: {
                    height: 350,
                    type: 'bar',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ["transparent"],
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: datasets.categories,
                    labels: {
                        // rotate: -45,
                        style: {
                            colors: datasets.colors,
                        },
                    },
                },

                yaxis: {
                    showAlways: true,
                    labels: {
                        show: true,
                        formatter: function (val) {
                            return parseInt(val);
                        },
                    },
                    title: {
                        text: `Quantidade`,
                        style: {
                            fontWeight: 500,
                        },
                    },
                    min: 0,
                },

                tooltip: {
                    shared: true,
                    x: {
                        formatter: function (val) {
                            return datasets.categories[val-1];
                        },

                    },

                },

                responsive: [
                    {
                        breakpoint: 600,
                        options: {
                            chart: {
                                toolbar: {
                                    show: false,
                                },
                            },
                            legend: {
                                show: false,
                            },
                        },
                    },
                ],
            };


            graphic.value = {
                chartOptions,
                series
            };
        }

        onMounted(() => {
            setType();
        })

        const setType = () => {
            http.get(props.url)
                .then((response) => {
                    getData(response.data.datasets);
                })
                .catch((error) => {
                    console.error('getData: ', error);
                })
        }

        return {
            graphic,
            setType,
            props
        }
    }
}

</script>
<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
  <apexchart
    class="apex-charts"
    type="line"
    height="350"
    dir="ltr"
    :series="graphic.series"
    :options="graphic.chartOptions"
  />
</template>
