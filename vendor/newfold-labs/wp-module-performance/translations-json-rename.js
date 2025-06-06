/* jshint ignore: start */

/**
 * Rename the json translation files replacing the md5 with script slug.
 *
 * @author Armando Liccardo <armando.liccardo@newfold.com>
 */

const fs    = require( 'fs' );
const {globSync}  = require( 'glob' );
const chalk = require( 'chalk' );

const RENAMED = chalk.reset.inverse.bold.green( ' RENAMED ' );
const ERROR   = chalk.reset.inverse.bold.red( ' ERROR ' );

const slugsMap = {
	'components/performance/defaultText.js': 'wp-module-performance-translations',
	'assets/image-bulk-optimizer/image-bulk-optimizer.js': 'nfd-performance-bulk-optimizer',
	'assets/image-optimized-marker/image-optimized-marker.js': 'nfd-performance-optimizer-marker'
};

const regex = /-(?:[a-f0-9]{32})\.json$/i;

const files = globSync('languages/*.json')
console.log('Renaming json files');
if ( files.length ) {
	files.forEach( ( file ) => {
		fs.readFile(file, function(err, data) { 

			if (err) {
				console.log( chalk.bold( ` - ${file} ` ) + ERROR );
				console.error( err );
				return;
			} 
		
			const fcontent = JSON.parse(data);
			const slug = slugsMap[fcontent.source];
			console.log(fcontent.source,slugsMap[fcontent.source]);
			if ( slug ) {
				const newname = file.replace(regex,  `-${slug}.json`);
				fs.rename(
					file,
					newname,
					() => {
						console.log( chalk.bold( ` - ${file} ` ) + RENAMED + ` to ${newname}` );
					});
			}
		}); 
	} );
}