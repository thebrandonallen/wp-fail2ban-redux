/* jshint node:true */
module.exports = function( grunt ) {

	var SOURCE_DIR = '',
		BUILD_DIR = 'build/',

		WPF2BR_EXCLUDED_MISC = [
			'!**/assets/**',
			'!**/bin/**',
			'!**/build/**',
			'!**/coverage/**',
			'!**/node_modules/**',
			'!**/tests/**',
			'!**/vendor/**',
			'!composer.*',
			'!Gruntfile.js*',
			'!package.json*',
			'!package-lock.json*',
			'!phpcs.xml*',
			'!phpunit.xml*',
			'!.*',
			'!.*/**',
		];

	// Load tasks.
	require( 'matchdep' ).filterDev(['grunt-*', '!grunt-legacy-util']).forEach( grunt.loadNpmTasks );

	// Project configuration
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		checktextdomain: {
			options: {
				text_domain: 'wp-fail2ban-redux',
				correct_domain: false,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: ['**/*.php'].concat( WPF2BR_EXCLUDED_MISC ),
				expand: true
			}
		},
		clean: {
			all: [ BUILD_DIR ]
		},
		copy: {
			files: {
				files: [{
					cwd: '',
					dest: 'build/',
					dot: true,
					expand: true,
					src: ['**', '!**/.{svn,git}/**'].concat( WPF2BR_EXCLUDED_MISC )
				}]
			}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			}
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'wp-fail2ban-redux.php',
					potFilename: 'wp-fail2ban-redux.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true,
					processPot: function( pot, options ) {
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/thebrandonallen/wp-fail2ban-redux/issues';
						pot.headers['last-translator']      = 'BRANDON ALLEN <plugins@brandonallen.me>';
						pot.headers['language-team']        = 'ENGLISH <plugins@brandonallen.me>';
						pot.headers['language']             = 'en_US';
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
							];

						for ( translation in pot.translations[''] ) {
							if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
									delete pot.translations[''][ translation ];
								}
							}
						}

						return pot;
					}
				}
			}
		},
		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
			'codecoverage': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist', '--coverage-clover=coverage.clover']
			}
		},
		'string-replace': {
			dev: {
				files: {
					'wp-fail2ban-redux.php': 'wp-fail2ban-redux.php'
				},
				options: {
					replacements: [{
						pattern: /(\*\sVersion:\s+)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					}]
				}
			},
			build: {
				files: {
					'CHANGELOG.md': 'CHANGELOG.md',
					'readme.txt': 'readme.txt',
					'wp-fail2ban-redux.php': 'wp-fail2ban-redux.php',
				},
				options: {
					replacements: [{
						pattern: /(\*\sVersion:\s+)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					},
					{
						pattern: /(Stable\stag:\s+)(.*)/gm, // For readme.txt
						replacement: '$1<%= pkg.version %>'
					},
					{
						pattern: /(Copyright\s\(C\)\s2016-)[0-9]{4}(\s+?Brandon\sAllen)/gm, // For Copyright.
						replacement: '$1<%= grunt.template.today("UTC:yyyy") %>$2'
					},
					{
						pattern: /(\*\sRelease\sdate:\s)(TBD|TBA|TDB)$/gm,
						replacement: '$1<%= grunt.template.today("yyyy-mm-dd") %>'
					},
					{
						pattern: /^(##\s.*\s-\s)(TBD|TBA|TDB)$/gm,
						replacement: '$1<%= grunt.template.today("yyyy-mm-dd") %>'
					}]
				}
			}
		},
		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			}
		}
	} );

	grunt.registerTask(
		'i18n:build',
		'Runs the WP-CLI i18n command to generate the pot file.',
		function () {
			const banner = grunt.template.process(
				'Copyright (C) 2016-<%= grunt.template.today("UTC:yyyy") %> Brandon Allen\n' +
					'This file is distributed under the same license as the WP Fail2Ban Redux package.\n' +
					'Submit translations to https://translate.wordpress.org/projects/wp-plugins/wp-fail2ban-redux.'
			);
			const keywords = [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'_n:1,2,4d',
				'_ex:1,2c,3d',
				'_nx:1,2,4c,5d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d',
			];
			const headers = {
				'Report-Msgid-Bugs-To':
					'https://github.com/thebrandonallen/wp-fail2ban-redux/issues',
				'X-Poedit-KeywordsList': `${ keywords.join( ';' ) }`,
			};
			grunt.util.spawn(
				{
					cmd: 'wp',
					args: [
						'i18n',
						'make-pot',
						'.',
						'languages/wp-fail2ban-redux.pot',
						`--headers=${ JSON.stringify( headers ) }`,
						`--file-comment=${ banner }`,
						'--exclude=build',
					],
					opts: { stdio: 'inherit' },
				},
				this.async()
			);
		}
	);

	// Register custom tasks.
	grunt.registerTask( 'i18n',   ['checktextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );
	grunt.registerTask( 'build',  [
		'clean:all',
		'checktextdomain',
		'string-replace:build',
		'readme',
		'i18n:build',
		'copy:files'
	] );

	// PHPUnit test task.
	grunt.registerMultiTask( 'phpunit', 'Runs PHPUnit tests, including the multisite tests.', function() {
		grunt.util.spawn( {
			cmd: this.data.cmd,
			args: this.data.args,
			opts: { stdio: 'inherit' }
		}, this.async() );
	} );

	// Travis CI Tasks.
	grunt.registerTask( 'travis:phpunit', ['phpunit:default'] );
	grunt.registerTask( 'travis:codecoverage', 'Runs PHPUnit tasks with code-coverage generation.', ['phpunit:codecoverage'] );
};
