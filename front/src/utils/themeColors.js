/**
 * Theme Colors Utility
 *
 * Reads CSS custom properties (variables) from the document root
 * to provide consistent colors across Vue components and charts.
 *
 * This ensures charts and dynamic elements use the same colors
 * defined in the SCSS theme files.
 */

/**
 * Get a CSS custom property value from the document root
 * @param {string} propertyName - The CSS variable name (e.g., '--vz-primary')
 * @returns {string} The computed color value
 */
export const getCssVariable = (propertyName) => {
    if (typeof window === 'undefined') return '';
    return getComputedStyle(document.documentElement)
        .getPropertyValue(propertyName)
        .trim();
};

/**
 * Get theme colors from CSS variables
 * Falls back to default values if CSS variables are not available
 * @returns {Object} Object containing theme colors
 */
export const getThemeColors = () => {
    const colors = {
        primary: getCssVariable('--vz-primary') || '#00D1F9',
        secondary: getCssVariable('--vz-secondary') || '#878a99',
        success: getCssVariable('--vz-success') || '#3cd188',
        info: getCssVariable('--vz-info') || '#0ac7fb',
        warning: getCssVariable('--vz-warning') || '#efae4e',
        danger: getCssVariable('--vz-danger') || '#f7666e',
        light: getCssVariable('--vz-light') || '#f3f6f9',
        dark: getCssVariable('--vz-dark') || '#272a3a',

        // Gray scale
        gray100: getCssVariable('--vz-gray-100') || '#f3f6f9',
        gray200: getCssVariable('--vz-gray-200') || '#eff2f7',
        gray300: getCssVariable('--vz-gray-300') || '#e9ebec',
        gray400: getCssVariable('--vz-gray-400') || '#ced4da',
        gray500: getCssVariable('--vz-gray-500') || '#adb5bd',
        gray600: getCssVariable('--vz-gray-600') || '#878a99',
        gray700: getCssVariable('--vz-gray-700') || '#495057',
        gray800: getCssVariable('--vz-gray-800') || '#343a40',
        gray900: getCssVariable('--vz-gray-900') || '#272a3a',

        // Border and background
        borderColor: getCssVariable('--vz-border-color') || '#e9ebec',
        cardBg: getCssVariable('--vz-card-bg-custom') || '#ffffff',
        bodyBg: getCssVariable('--vz-body-bg') || '#f3f3f9',
    };

    return colors;
};

/**
 * Get chart-specific color configurations
 * @returns {Object} Object containing chart color arrays and configurations
 */
export const getChartColors = () => {
    const theme = getThemeColors();

    return {
        // Standard status colors for pie/donut charts
        statusColors: [theme.warning, theme.info, theme.success, theme.danger],

        // Colors for bar charts (comparing values)
        barColors: [theme.primary, theme.success],

        // Colors for area/line charts
        areaColors: [theme.primary, theme.success],

        // Grid and axis colors
        gridColor: theme.borderColor,
        axisLabelColor: theme.gray600,
        textColor: theme.gray700,

        // Gradient configurations
        gradientFrom: 0.4,
        gradientTo: 0.1,
    };
};

/**
 * Get ApexCharts default configuration
 * @returns {Object} Default ApexCharts options with theme colors
 */
export const getApexChartsDefaults = () => {
    const theme = getThemeColors();
    const chart = getChartColors();

    return {
        chart: {
            toolbar: { show: false },
        },
        grid: {
            borderColor: chart.gridColor,
            strokeDashArray: 3,
        },
        xaxis: {
            labels: {
                style: {
                    colors: chart.axisLabelColor,
                    fontSize: '12px',
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            labels: {
                style: {
                    colors: chart.axisLabelColor,
                    fontSize: '12px',
                }
            }
        },
        legend: {
            labels: {
                colors: chart.textColor,
            },
            markers: {
                width: 8,
                height: 8,
                radius: 8,
            }
        },
        tooltip: {
            theme: 'light',
        },
        dataLabels: {
            style: {
                colors: [theme.gray700],
            }
        },
    };
};

export default {
    getCssVariable,
    getThemeColors,
    getChartColors,
    getApexChartsDefaults,
};
