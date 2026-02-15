const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources', 'views');
const modulesDir = path.join(__dirname, 'Modules');

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;

    // Pattern 1: Hardcoded bg-slate-900 -> bg-surface (or similar)
    // We need to be careful. bg-slate-900 is often used for "dark" cards.
    // In light mode, this should be white/surface.

    // Replace bg-slate-900 with bg-surface in class attributes
    // Use regex to ensure we are inside class="..."
    content = content.replace(/class=(["'])(.*?)\1/g, (match, quote, classNames) => {
        let newClassNames = classNames;

        // Fix: bg-slate-900 -> bg-surface (dark card background to theme-aware)
        if (newClassNames.includes('bg-slate-900')) {
            newClassNames = newClassNames.replace(/\bbg-slate-900\b/g, 'bg-surface');
        }

        // Fix: bg-dark -> bg-surface-secondary
        // Check if it's a dropdown menu - dropdown-menu-dark is valid bootstrap, but we handled it in CSS.
        // If it is just "bg-dark", it forces black background.
        if (newClassNames.includes('bg-dark') && !newClassNames.includes('dropdown-menu-dark') && !newClassNames.includes('text-dark')) {
            newClassNames = newClassNames.replace(/\bbg-dark\b/g, 'bg-surface-secondary');
        }

        // Fix: text-white on headings/body -> text-heading (Specific for HR Dashboard Headers)
        // If we are in a glass-card (which is now light/glass) or just generally in these files
        // We should replace text-white with text-heading for h1-h6 and text-body for others
        // But we need to be careful not to break buttons.

        // Strategy: Replace text-white with text-heading if it's likely a header or valid text
        if (newClassNames.includes('text-white') && !newClassNames.includes('btn') && !newClassNames.includes('badge')) {
            newClassNames = newClassNames.replace(/\btext-white\b/g, 'text-heading');
        }

        // Fix: text-gray-400/300 -> text-secondary (Too light for light mode)
        if (newClassNames.includes('text-gray-400')) {
            newClassNames = newClassNames.replace(/\btext-gray-400\b/g, 'text-secondary');
        }
        if (newClassNames.includes('text-gray-300')) {
            newClassNames = newClassNames.replace(/\btext-gray-300\b/g, 'text-secondary');
        }

        // Fix: border-white -> border-secondary (for light mode visibility)
        if (newClassNames.includes('border-white')) {
            newClassNames = newClassNames.replace(/\bborder-white\b/g, 'border-secondary border-opacity-10');
        }

        // Fix: form-control-dark -> form-control (let CSS handle theme)
        if (newClassNames.includes('form-control-dark')) {
            newClassNames = newClassNames.replace(/\bform-control-dark\b/g, 'form-control');
        }

        // Fix: form-select-dark -> form-select
        if (newClassNames.includes('form-select-dark')) {
            newClassNames = newClassNames.replace(/\bform-select-dark\b/g, 'form-select');
        }

        return `class=${quote}${newClassNames}${quote}`;
    });

    if (content !== originalContent) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED DARK MODE] ${path.relative(__dirname, filePath)}`);
    }
}

function getAllFiles(dirPath, arrayOfFiles) {
    if (!fs.existsSync(dirPath)) return arrayOfFiles || [];

    files = fs.readdirSync(dirPath);
    arrayOfFiles = arrayOfFiles || [];

    files.forEach(function (file) {
        const fullPath = path.join(dirPath, file);
        if (fs.statSync(fullPath).isDirectory()) {
            arrayOfFiles = getAllFiles(fullPath, arrayOfFiles);
        } else {
            if (file.endsWith('.blade.php')) {
                arrayOfFiles.push(fullPath);
            }
        }
    });

    return arrayOfFiles;
}

const viewsDirInventory = path.join(viewsDir, 'inventory');
const hrDir = path.join(modulesDir, 'HR', 'resources', 'views');

console.log('Scanning Inventory...');
const inventoryFiles = getAllFiles(viewsDirInventory);
inventoryFiles.forEach(processFile);

console.log('Scanning HR...');
if (fs.existsSync(hrDir)) {
    const hrFiles = getAllFiles(hrDir);
    hrFiles.forEach(processFile);
}

// Also scan Categories (Finance/Inventory could share)
const categoriesDir1 = path.join(viewsDir, 'finance', 'categories');
const categoriesDir2 = path.join(viewsDir, 'inventory', 'categories');

if (fs.existsSync(categoriesDir1)) getAllFiles(categoriesDir1).forEach(processFile);
if (fs.existsSync(categoriesDir2)) getAllFiles(categoriesDir2).forEach(processFile);

console.log('Dark mode regression fix complete.');
