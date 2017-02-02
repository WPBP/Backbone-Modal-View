'use strict';
module.exports = function (grunt) {

  grunt.initConfig({
	coffee: {
	  public: {
		options: {
		  bare: true,
		  join: true
		},
		files: {
		  'assets/js/public.js': 'assets/coffee/public.coffee'
		}
	  },
	  publicDev: {
		options: {
		  bare: true,
		  join: true,
		  sourceMap: true
		},
		files: {
		  'assets/js/public.js': 'assets/coffee/public.coffee'
		}
	  },
	  watch: {
		compass: {
		  files: [
			'assets/coffee/*.coffee'
		  ],
		  tasks: [
			'coffee:publicDev'
		  ]
		}
	  }
	}
  });

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-coffee');

  // Register tasks
  grunt.registerTask('default', [
	'coffee:public'
  ]);
  grunt.registerTask('dev', [
	'watch'
  ]);

};
