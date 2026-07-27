module.exports = {
    // existing tags are plain "4.9.7", no "v" prefix
    'tag-prefix': '',
    releaseCommitMessageFormat: 'chore(release): {{currentTag}}',
    // source of truth for the current version
    packageFiles: [
        {filename: 'turbosmtp.php', updater: 'scripts/version-updaters/php-plugin.js'},
    ],
    bumpFiles: [
        {filename: 'turbosmtp.php', updater: 'scripts/version-updaters/php-plugin.js'},
        {filename: '.wordpress-org/readme/README.md', updater: 'scripts/version-updaters/wp-readme.js'},
        {filename: 'package.json', type: 'json'},
        {filename: 'package-lock.json', type: 'json'},
    ],
};
