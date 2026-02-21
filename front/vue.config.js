module.exports = {
    chainWebpack: config => {
        config.resolve.alias.set(
            'bootstrap-vue-3',
            require.resolve('bootstrap-vue-3')
        );
    }
};
