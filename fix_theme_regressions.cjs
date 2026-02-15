const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');
// We will scan everything but skip specific files/folders during processing if needed
const ignoreDirs = ['print', 'receipts', 'errors'];

// Replacements Map
const replacements = [
    // Backgrounds - Context aware
    { regex: /class="([^"]*)bg-white([^"]*)"/g, replacement: 'class="$1bg-surface$2"' },
    { regex: /class="([^"]*)bg-light([^"]*)"/g, replacement: 'class="$1bg-surface-secondary$2"' },

    // Text Colors
    { regex: /text-dark/g, replacement: 'text-body' },
    { regex: /text-black-50/g, replacement: 'text-muted' },

    // Borders
    { regex: /border-white/g, replacement: 'border-secondary border-opacity-10' },
];

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;
    let modified = false;

    // We can't use complex regex lookbehinds easily for all cases in JS without robust parsing,
    // so we will do simple string replacements where safe, or regex replacements.

    // 1. Text Colors (Safe to replace globally usually)
    if (content.includes('text-dark')) {
        content = content.replace(/text-dark/g, 'text-body');
        modified = true;
    }
    if (content.includes('text-black-50')) {
        content = content.replace(/text-black-50/g, 'text-muted');
        modified = true;
    }

    // 2. Backgrounds & Borders
    // We want to avoid replacing bg-white if it's inside a specific component we manually fixed or if it needs to remain white (though for theming, almost nothing should be hardcoded white).

    // Replace bg-white with bg-surface
    if (content.includes('bg-white')) {
        content = content.replace(/bg-white/g, 'bg-surface');
        modified = true;
    }

    // Replace bg-light with bg-surface-secondary
    if (content.includes('bg-light')) {
        content = content.replace(/bg-light/g, 'bg-surface-secondary');
        modified = true;
    }

    // Replace border-white
    if (content.includes('border-white')) {
        content = content.replace(/border-white/g, 'border-secondary border-opacity-10');
        modified = true;
    }

    if (modified) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED] ${path.relative(__dirname, filePath)}`);
    }
}

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
    // Skip manually handled files to avoid overwriting complex logic or if verified
    // We skip pos/index.blade.php and auth/login.blade.php as requested/verified
    if (file.includes('pos\\index.blade.php') || file.includes('auth\\login.blade.php')) {
        return;
    }

    processFile(file);
});

console.log('Batch regression fix complete.');
