const fs = require('fs');
const path = require('path');
const glob = require('glob');

const viewsDir = path.join(__dirname, 'resources', 'views');
const ignoreDirs = ['print', 'receipts', 'errors', 'layouts', 'auth', 'pos']; // Skip these, handled manually or strict

// Replacements Map
const replacements = [
    // Backgrounds
    { regex: /bg-white/g, replacement: 'bg-surface' },
    { regex: /bg-light/g, replacement: 'bg-surface-secondary' },

    // Text Colors
    { regex: /text-dark/g, replacement: 'text-body' },
    { regex: /text-black-50/g, replacement: 'text-muted' },
    { regex: /text-muted/g, replacement: 'text-secondary opacity-75' }, // Better contrast check

    // Borders
    { regex: /border-white/g, replacement: 'border-secondary border-opacity-10' },
];

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;
    let modified = false;

    replacements.forEach(rule => {
        if (rule.regex.test(content)) {
            content = content.replace(rule.regex, rule.replacement);
            modified = true;
        }
    });

    if (modified) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED] ${path.relative(__dirname, filePath)}`);
    }
}

function walkArgs(dir) {
    return new Promise((resolve, reject) => {
        glob(dir + '/**/*.blade.php', { ignore: ignoreDirs.map(d => `**/${d}/**`) }, (err, files) => {
            if (err) return reject(err);
            resolve(files);
        });
    });
}

// Custom simple walker since glob ignore patterns can be tricky
function getAllFiles(dirPath, arrayOfFiles) {
    files = fs.readdirSync(dirPath);

    arrayOfFiles = arrayOfFiles || [];

    files.forEach(function (file) {
        const fullPath = path.join(dirPath, file);
        if (fs.statSync(fullPath).isDirectory()) {
            if (!ignoreDirs.includes(file)) {
                arrayOfFiles = getAllFiles(fullPath, arrayOfFiles);
            }
        } else {
            if (file.endsWith('.blade.php')) {
                arrayOfFiles.push(fullPath);
            }
        }
    });

    return arrayOfFiles;
}

const allFiles = getAllFiles(viewsDir);
console.log(`Scanning ${allFiles.length} files...`);

allFiles.forEach(file => {
    // Double check exclusions just in case
    if (!file.includes('pos\\index.blade.php') && !file.includes('auth\\login.blade.php')) {
        processFile(file);
    }
});

console.log('Batch regression fix complete.');
