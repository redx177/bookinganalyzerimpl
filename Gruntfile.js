module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev: {
                files: {"css/result.css": "css/source.less"}
            },
        },
        watch: {
            files: ['css/**/*'],
            tasks: ['less'],
        },
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task(s).
    grunt.registerTask('default', ['less']);

};