const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');
const ignoreDirs = ['print', 'receipts', 'errors'];

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;
    let modified = false;

    // We need to replace 'text-white' but NOT if it's part of a button, badge, or specific background context.
    // The previous script failed because it looked for specific tag structures.
    // This time we will tokenize the file roughly to find "class" attributes and operate on them.

    // Regex to find class attributes: class="..." or class='...'
    // We use a callback to inspect the content of the class attribute.
    content = content.replace(/class=(["'])(.*?)\1/g, (match, quote, classNames) => {
        // If the class doesn't contain text-white, return as is.
        if (!classNames.includes('text-white')) return match;

        // CHECK EXCLUSIONS
        // If it's a button, badge, or has bg-primary/success/danger etc, keep text-white.
        // We look for "btn", "badge", "bg-primary", "bg-success", "bg-danger", "bg-warning", "bg-info", "bg-dark"
        if (
            classNames.includes('btn') ||
            classNames.includes('badge') ||
            classNames.includes('bg-primary') ||
            classNames.includes('bg-success') ||
            classNames.includes('bg-danger') ||
            classNames.includes('bg-warning') ||
            classNames.includes('bg-info') ||
            classNames.includes('bg-dark') ||
            classNames.includes('bg-gradient')
        ) {
            return match; // Keep it
        }

        // Check for "text-white-50" -> "text-muted"
        if (classNames.includes('text-white-50')) {
            classNames = classNames.replace('text-white-50', 'text-muted');
        }

        // Replace "text-white" with "text-body" default
        // If it happens to be a header, we might want text-heading, but text-body is safe for visibility.
        // Let's stick to text-body (which maps to var(--text-primary)) for general safety.
        classNames = classNames.replace(/\btext-white\b/g, 'text-body');

        return `class=${quote}${classNames}${quote}`;
    });

    if (content !== originalContent) {
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
console.log(`Scanning ${allFiles.length} files for aggressive text-white regressions...`);

allFiles.forEach(file => {
    // Skip manually handled files
    if (file.includes('pos\\index.blade.php') || file.includes('auth\\login.blade.php')) {
        return;
    }
    processFile(file);
});

console.log('Batch text regression fix complete.');
