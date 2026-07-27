/**
 * commit-and-tag-version updater for turbosmtp.php:
 * keeps the plugin header "Version:" and the TURBOSMTP_VERSION constant in sync.
 */

const HEADER_RE = /(\* Version:\s+)([0-9.]+)/;
const CONSTANT_RE = /(define\(\s*'TURBOSMTP_VERSION',\s*')([0-9.]+)('\s*\))/;

module.exports.readVersion = function (contents) {
    const match = contents.match(HEADER_RE);
    if (!match) {
        throw new Error('Version header not found in turbosmtp.php');
    }
    return match[2];
};

module.exports.writeVersion = function (contents, version) {
    return contents
        .replace(HEADER_RE, `$1${version}`)
        .replace(CONSTANT_RE, `$1${version}$3`);
};
