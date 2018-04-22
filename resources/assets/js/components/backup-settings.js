module.exports = {
    props: ['backup_type', 'options'],

    data: function () {
        return {
            loaded: false
        }
    },

    read: function () {
        this.loaded = true;
    }


};