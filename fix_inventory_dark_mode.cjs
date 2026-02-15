const fs = require('fs');
const path = require('path');

const inventoryDir = path.join(__dirname, 'resources', 'views', 'inventory');

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;

    // 1. Remove :root { ... } blocks that override variables
    // Matches :root { ... } with any content inside, assuming it's the premium theme override
    // We use a non-greedy match for content
    content = content.replace(/:root\s*\{[\s\S]*?\}/g, (match) => {
        if (match.includes('--bg-dark') || match.includes('--glass-bg')) {
            console.log(`  - Removed :root block in ${path.basename(filePath)}`);
            return '/* :root override removed for theme compatibility */';
        }
        return match;
    });

    // 2. Fix .table-dark-custom color
    // Replace color: #e2e8f0; with color: var(--text-body);
    // We target the specific class definition block
    content = content.replace(/\.table-dark-custom\s*\{[\s\S]*?\}/g, (match) => {
        if (match.includes('color: #e2e8f0')) {
            console.log(`  - Fixed .table-dark-custom color in ${path.basename(filePath)}`);
            return match.replace('color: #e2e8f0', 'color: var(--text-body)');
        }
        return match;
    });

    // 3. Fix table header color in .table-dark-custom th
    content = content.replace(/\.table-dark-custom th\s*\{[\s\S]*?\}/g, (match) => {
        // Replace absolute black/dark background with semi-transparent or variable
        // background: rgba(0, 0, 0, 0.2); -> background: rgba(0, 0, 0, 0.05); (lighter)
        // Or just let it be. The main issue is text color.
        // It says: color: var(--text-secondary); which is fine if text-secondary is defined.
        // But let's ensure it's not hardcoded white.
        return match;
    });

    // 4. Fix text-gray-400 definition if it exists in style block
    // .text-gray-400 { color: var(--text-secondary) !important; } matches what we want, 
    // but if it is .text-gray-400 { color: #94a3b8 !important; } we might want to ensure it uses variable
    content = content.replace(/\.text-gray-400\s*\{[\s\S]*?\}/g, (match) => {
        if (match.includes('#')) {
            return match.replace(/#[a-fA-F0-9]{6}/, 'var(--text-secondary)');
        }
        return match;
    });

    if (content !== originalContent) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`[FIXED INVENTORY] ${path.relative(__dirname, filePath)}`);
    }
}

function getAllFiles(dirPath, arrayOfFiles) {
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

console.log('Scanning Inventory for hardcoded styles...');
if (fs.existsSync(inventoryDir)) {
    const files = getAllFiles(inventoryDir);
    files.forEach(processFile);
}
console.log('Inventory fix complete.');
