/**
 * Thermal Report Print Utility
 * Generates thermal receipt-formatted (80mm) print output for report pages.
 * Uses EXACT same sizing as POS receipt.blade.php (proven on XP-80C).
 */
function printThermal(config) {
    const {
        title = 'تقرير',
        subtitle = '',
        summaryCards = [],
        sections = [],
        footerNote = ''
    } = config;

    let html = `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title}</title>
    <style>
        /* === Robust Thermal Styling === */
        @page {
            margin: 0;
            size: auto; /* Let printer driver handle paper size */
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            margin: 0;
            padding: 5px;
            width: 100%; /* Fill available width */
            font-size: 14px; /* Default readable size */
            color: #000;
            line-height: 1.3;
            direction: rtl;
        }

        /* === Layout helpers === */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        .dashed-line {
            border-top: 2px dashed #000; /* Thicker for visibility */
            margin: 8px 0;
            width: 100%;
        }
        .double-line {
            border-top: 3px double #000; /* Thicker */
            margin: 8px 0;
        }

        /* === Header (matches store-name: 16px) === */
        .report-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .report-subtitle {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }
        .report-date {
            font-size: 12px;
            color: #555;
        }

        /* === Summary rows === */
        .summary-section {
            margin: 8px 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 14px;
        }
        .summary-row .value {
            font-weight: bold;
        }

        /* === Section (matches totals styling) === */
        .section {
            margin: 5px 0;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #eee;
            padding: 3px 5px;
            margin-bottom: 3px;
            border-right: 3px solid #000;
        }

        /* === Table (matches POS table: th 11px, td 12px) === */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        th {
            border-bottom: 1px solid #000;
            text-align: right;
            padding: 4px 0;
            font-size: 14px;
            font-weight: bold;
        }
        th:last-child, td:last-child {
            text-align: left;
        }
        td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 16px;
            border-bottom: 1px dotted #ccc;
        }
        td.num {
            text-align: left;
            white-space: nowrap;
        }
        td.center {
            text-align: center;
        }
        tr:last-child td {
            border-bottom: none;
        }

        /* === Totals (matches total-final: 16px bold) === */
        .section-footer {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 20px;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        /* === Footer === */
        .report-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #555;
        }

        /* === Popup controls (hidden on print) === */
        .no-print {
            margin: 8px 0;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #f59e0b; color: #000; border: none; border-radius: 5px; font-weight: bold;">
            🖨️ طباعة
        </button>
        <button onclick="window.close()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #eee; border: none; border-radius: 5px; margin-right: 8px;">
            ✕ إغلاق
        </button>
    </div>

    <div class="dashed-line"></div>

    <!-- Header -->
    <div class="report-header">
        <div class="report-title">${escapeHtml(title)}</div>
        ${subtitle ? `<div class="report-subtitle">${escapeHtml(subtitle)}</div>` : ''}
        <div class="report-date">طُبع: ${new Date().toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' })}</div>
    </div>

    <div class="dashed-line"></div>`;

    // Summary Cards
    if (summaryCards.length > 0) {
        html += `<div class="summary-section">`;
        summaryCards.forEach(card => {
            html += `<div class="summary-row">
                <span>${escapeHtml(card.label)}:</span>
                <span class="value">${escapeHtml(card.value)}</span>
            </div>`;
        });
        html += `</div><div class="dashed-line"></div>`;
    }

    // Sections
    sections.forEach((section, idx) => {
        html += `<div class="section">`;

        if (section.title) {
            html += `<div class="section-title">${escapeHtml(section.title)}</div>`;
        }

        if (section.headers && section.rows) {
            html += `<table><thead><tr>`;
            section.headers.forEach(h => {
                html += `<th>${escapeHtml(h)}</th>`;
            });
            html += `</tr></thead><tbody>`;

            section.rows.forEach(row => {
                const isHighlight = row._highlight;
                html += `<tr${isHighlight ? ' style="font-weight:bold;border-top:1px solid #000;"' : ''}>`;
                const cells = Array.isArray(row) ? row : row.cells || [];
                cells.forEach((cell, i) => {
                    const isLast = i === cells.length - 1;
                    const isMiddle = i > 0 && !isLast;
                    html += `<td${isLast ? ' class="num"' : ''}${isMiddle ? ' class="center"' : ''}>${escapeHtml(String(cell))}</td>`;
                });
                html += `</tr>`;
            });

            html += `</tbody></table>`;
        }

        if (section.footer) {
            html += `<div class="section-footer">
                <span>${escapeHtml(section.footer.label)}</span>
                <span>${escapeHtml(section.footer.value)}</span>
            </div>`;
        }

        html += `</div>`;

        if (idx < sections.length - 1) {
            html += `<div class="dashed-line"></div>`;
        }
    });

    // Footer note (grand total)
    if (footerNote) {
        html += `<div class="double-line"></div>
        <div class="grand-total">
            <span>${escapeHtml(footerNote.label || '')}</span>
            <span>${escapeHtml(footerNote.value || '')}</span>
        </div>`;
    }

    html += `
    <div class="dashed-line" style="margin-top: 10px;"></div>
    <div class="report-footer">
        <div>— نهاية التقرير —</div>
    </div>
</body>
</html>`;

    // Open popup and print
    const printWindow = window.open('', '_blank', 'width=350,height=600,scrollbars=yes');
    if (printWindow) {
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.onload = function () {
            printWindow.focus();
        };
    } else {
        alert('يرجى السماح بالنوافذ المنبثقة لطباعة التقرير الحراري');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
