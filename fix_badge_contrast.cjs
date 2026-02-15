const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');
// No specific ignores needed as we want to fix this everywhere
const ignoreDirs = ['print', 'receipts'];

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;

    // We are looking for: class="... badge ... text-white ..."
    // BUT we only want to fix it if the background is "weak".
    // Weak backgrounds: bg-surface, bg-light, bg-white, any /10, /20, /5 opacity 
    // Strong backgrounds (keep text-white): bg-primary, bg-success, bg-danger, bg-warning, bg-info, bg-dark (without opacity)

    // Regex logic:
    // 1. Find class attributes containing 'badge' AND 'text-white'
    // 2. Check if they contain a "strong" background class appearing as a standalone word (e.g. "bg-primary" but not "bg-primary/10")

    content = content.replace(/class=(["'])(.*?)\1/g, (match, quote, classNames) => {
        if (!classNames.includes('badge')) return match;
        if (!classNames.includes('text-white')) return match;

        // Found a badge with text-white.
        // Check for strong backgrounds.
        // We consider a background strong if it is one of the standard colors AND DOES NOT have a slash (opacity) or opacity class nearby.
        // Actually, looking at the code `bg-success/10 text-white` -> text-white is BAD on 10% opacity.
        // So any "bg-*/10" or "bg-*/20" should have text-white removed/changed.

        // If the class contains "/" (Tailwind opacity) -> Remove text-white (replace with text-body)
        if (classNames.includes('/')) {
            return `class=${quote}${classNames.replace(/\btext-white\b/g, 'text-body')}${quote}`;
        }

        // If the class contains "bg-surface" or "bg-light" or "bg-white" -> Remove text-white
        if (classNames.includes('bg-surface') || classNames.includes('bg-light') || classNames.includes('bg-white')) {
            return `class=${quote}${classNames.replace(/\btext-white\b/g, 'text-body')}${quote}`;
        }

        // If it's a standard strong badge like "badge bg-primary", keep text-white.
        // We assume valid bootstrap badges "bg-primary", "bg-success" etc are opaque.
        // So we do nothing.

        return match;
    });

    if (content !== originalContent) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED BADGE] ${path.relative(__dirname, filePath)}`);
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
console.log(`Scanning ${allFiles.length} files for badge contrast issues...`);

allFiles.forEach(file => {
    processFile(file);
});

console.log('Badge contrast fix complete.');
