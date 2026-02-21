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

        const getData = async (data) => {
            let widthLine = ref([]);
            let series = [];
            let x = [];
            let y = [];
            let i = 0;
            let colors = [];

            data.forEach((item) => {
                item.data.forEach((element) => {
                    if (i === 0) {
                        x.push(element.x);
                    }
                    y.push(element.y);
                })
                colors.push(item.backgroundColor)
                i++;
                series.push({
                    name: item.label,
                    data: y
                });
                y = [];
                widthLine.value.push(3)
            })

            const chartOptions = {
                chart: {
                    type: "line",
                    stacked: false,
                    height: 350,
                    zoom: {
                        enabled: false
                    }
                },
                colors,
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: "straight",
                    width: widthLine.value
                },


                xaxis: {
                    categories: x,
                    tickAmount: 7,
                    labels: {
                        rotate: -45,
                        show: true,
                    },

                },
                yaxis: {
                    showAlways: true,
                    labels: {
                        show: true,
                        formatter: function (val) {
                            return val;
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
                    y: {
                        formatter: function (val) {
                            return val;
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
            }


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
