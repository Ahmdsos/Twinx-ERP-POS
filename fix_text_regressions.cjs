const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');
const ignoreDirs = ['print', 'receipts', 'errors'];

// We need to be careful with text-white. 
// It is valid on buttons, badges, and dark cards.
// It is INVALID on page headers, table headers (if bg is transparent), and standard text in light mode.

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;
    let modified = false;

    // Strategy:
    // 1. Replace 'text-white' with 'text-heading' on H1-H6 tags.
    //    Regex: <h[1-6][^>]*class="[^"]*text-white[^"]*"
    //    We will replace the specific string 'text-white' with 'text-heading' inside the class attribute of headers.

    // 2. Replace 'text-white' with 'text-body' on specific problematic containers if found throughout the file, 
    //    BUT we must exclude specific patterns like 'btn', 'badge', 'bg-primary', etc.

    // Implementation:
    // We will use a function to replace 'text-white' only when safe.

    // Safer approach for headings:
    // Find <h1...h6 tags
    content = content.replace(/(<h[1-6][^>]*class="[^"]*)text-white([^"]*"[^>]*>)/gi, '$1text-heading$2');

    // Find table headers <th> often having text-white
    content = content.replace(/(<th[^>]*class="[^"]*)text-white([^"]*"[^>]*>)/gi, '$1text-secondary$2');

    // Find <div class="... text-white ..."> that ARE NOT buttons or badges or have specific backgrounds
    // This is hard to regex perfectly. 
    // Instead, let's look for specific patterns we saw in the dashboard/audit.
    // Pattern: "text-white mb-*" (common in heroes), "text-white opacity-*"

    // Replace "text-white opacity-75" -> "text-muted" (approx)
    // content = content.replace(/text-white opacity-75/g, 'text-muted');
    // content = content.replace(/text-white opacity-50/g, 'text-muted opacity-50');

    // Let's rely on the previous specific logic for headings as that is the #1 offender.
    // And let's replace "text-white" in <p> tags too.
    content = content.replace(/(<p[^>]*class="[^"]*)text-white([^"]*"[^>]*>)/gi, '$1text-body$2');

    // Also, previously we missed "text-white-50".
    content = content.replace(/text-white-50/g, 'text-muted');

    if (content !== originalContent) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED] ${path.relative(__dirname, filePath)}`);
        // console.log('Diff:', content.length - originalContent.length); 
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
console.log(`Scanning ${allFiles.length} files for text-white regressions...`);

allFiles.forEach(file => {
    // Skip manually handled files
    if (file.includes('pos\\index.blade.php') || file.includes('auth\\login.blade.php')) {
        return;
    }
    processFile(file);
});

console.log('Batch text regression fix complete.');
