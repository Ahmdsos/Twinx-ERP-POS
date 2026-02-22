const fs = require('fs');
const path = require('path');

const viewsDir = path.join(__dirname, 'resources/views');
const arJsonPath = path.join(__dirname, 'resources/lang/ar.json');

// Check translation file
if (!fs.existsSync(arJsonPath)) {
    console.error("Error: resources/lang/ar.json not found!");
    process.exit(1);
}

// 1. Load Translations and Create Reverse Map (Arabic -> English Key)
const arData = JSON.parse(fs.readFileSync(arJsonPath, 'utf8'));
const substitutions = [];

// Populate substitutions from ar.json
for (const [englishKey, arabicVal] of Object.entries(arData)) {
    if (arabicVal && typeof arabicVal === 'string' && arabicVal.length > 2) {
        substitutions.push({ arabic: arabicVal, english: englishKey });
    }
}

// Sort by length descending to replace longest phrases first
substitutions.sort((a, b) => b.arabic.length - a.arabic.length);

// 2. Define CSS Replacements (Regex -> Replacement)
const cssSubs = [
    // Remove local .glass-card definitions entirely
    { reg: /\.glass-card\s*\{[\s\S]*?\}/g, val: '' },

    // Replace hex colors with CSS variables
    { reg: /background(?:-color)?:\s*#1e293b;?/gi, val: 'background-color: var(--input-bg);' },
    { reg: /background(?:-color)?:\s*rgba\(30,\s*41,\s*59,\s*0\.7\);?/gi, val: 'background: var(--glass-bg);' },
    { reg: /border(?:-bottom)?:\s*1px\s+solid\s+rgba\(255,\s*255,\s*255,\s*0\.08\);?/gi, val: 'border-bottom: 1px solid var(--border-color);' },

    // Context-specific table headers
    { reg: /(thead\s+th\s*\{[\s\S]*?background-color:\s*)(?:rgba\(255,\s*255,\s*255,\s*0\.05\)|#ffffff0d)(;?)/g, val: '$1var(--table-head-bg)$2' },
    { reg: /(thead\s+th\s*\{[\s\S]*?color:\s*)(?:#94a3b8)(;?)/g, val: '$1var(--table-head-color)$2' },

    // Generic replacements for remaining items
    { reg: /background:\s*rgba\(255,\s*255,\s*255,\s*0\.05\);?/gi, val: 'background: var(--btn-glass-bg);' },
    { reg: /border:\s*1px\s+solid\s+rgba\(255,\s*255,\s*255,\s*0\.1\);?/gi, val: 'border: 1px solid var(--btn-glass-border);' },

    // Text colors
    { reg: /color:\s*white;?/gi, val: 'color: var(--text-primary);' },
    { reg: /color:\s*#94a3b8;?/gi, val: 'color: var(--text-secondary);' },
];

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // A. Apply i18n Replacements
    for (const { arabic, english } of substitutions) {
        // Escape regex special characters in the Arabic string
        const esc = arabic.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        // 1. Between tags: >Arabic<  ->  >{{ __('Key') }}<
        content = content.replace(new RegExp(`>\\s*${esc}\\s*<`, 'g'), `>{{ __('${english}') }}<`);

        // 2. In Double Quotes (HTML Attributes/JS): "Arabic"  ->  "{{ __('Key') }}"
        content = content.replace(new RegExp(`"${esc}"`, 'g'), `"{{ __('${english}') }}"`);

        // 3. In @section Directive: @section('key', 'Arabic')  ->  @section('key', __('Key'))
        content = content.replace(new RegExp(`(@section\\s*\\(\\s*['"][^'"]+['"]\\s*,\\s*)'${esc}'`, 'g'), `$1__('${english}')`);
    }

    // B. Apply CSS Replacements
    for (const { reg, val } of cssSubs) {
        content = content.replace(reg, val);
    }

    // Only write if changes were made
    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Modified: ${path.relative(viewsDir, filePath)}`);
    }
}

function walk(dir) {
    if (!fs.existsSync(dir)) return;
    const files = fs.readdirSync(dir);

    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);

        if (stat.isDirectory()) {
            walk(fullPath);
        } else if (file.endsWith('.blade.php')) {
            // Skip print/receipt/barcode files as they should remain Light/Special
            if (file.includes('receipt') || file.includes('print') || file.includes('barcode')) {
                continue;
            }
            processFile(fullPath);
        }
    }
}

console.log('Starting Batch Fix...');
walk(viewsDir);
console.log('Batch Fix Completed.');
