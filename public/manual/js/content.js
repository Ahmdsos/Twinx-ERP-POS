window.docsContent = {
    "en": {
        "crm": {
            "title": "Sales & Customer Relationship Management",
            "icon": "fa-solid fa-users",
            "description": "Complete customer lifecycle management — from onboarding 9 customer types to credit control, account statements, and data import/export.",
            "sections": [
                {
                    "title": "1. Customer Master Data (Field-by-Field)",
                    "body": "Every entity interacting with your business starts here. The customer profile governs pricing, credit, and reporting.\n\n### Identity & Classification\n| Field | Type | Description |\n|-------|------|-------------|\n| **Name** | Required | Legal billing name. Appears on all invoices, receipts, and statements. |\n| **Customer Type** | Required | Determines pricing tier. Options: `Consumer` (retail), `Wholesale`, `Semi-Wholesale`, `Distributor`, `Company`, `Technician`, `Online`, `Export`, `Government`. |\n| **Code** | Auto/Manual | Unique identifier (e.g., `CUST-001`). Auto-generated or manually entered. |\n| **Tax Number** | Optional | VAT registration number. Required for tax-compliant B2B invoices. |\n\n### Contact Information\n| Field | Description |\n|-------|-------------|\n| **Mobile** | Primary search key in POS. Used for SMS notifications. |\n| **Phone** | Secondary landline number. |\n| **Email** | Used for digital statement delivery and notifications. |\n| **Fax** | Legacy field for formal B2B correspondence. |\n\n### Address Data\n| Field | Description |\n|-------|-------------|\n| **Billing Address** | Printed on invoices and tax documents. |\n| **Shipping Address** | Used for delivery orders. Can differ from billing. |\n| **City / State** | Geographic classification for reporting and delivery routing. |\n\n### Financial Controls\n| Field | Type | Description |\n|-------|------|-------------|\n| **Credit Limit** | Currency | Maximum allowed outstanding balance. System blocks sales if exceeded. |\n| **Payment Terms** | Days | Default credit period (e.g., 30 days). Auto-calculates invoice due dates. |\n| **Opening Balance** | Currency | Pre-existing debt when migrating from another system. Creates a G/L entry. |\n\n> **Accounting Entry — Opening Balance:**\n> When you set an opening balance of EGP 5,000:\n> - DR: Accounts Receivable (1201) → EGP 5,000\n> - CR: Opening Balance Equity (3100) → EGP 5,000",
                    "fields": {
                        "Credit Limit": "Hard cap on indebtedness. System refuses POS/Invoice sales if exceeded.",
                        "Payment Terms": "Default days for credit. Overridable per invoice.",
                        "Opening Balance": "Migration field. Fires an automatic journal entry."
                    }
                },
                {
                    "title": "2. Customer Types & Pricing Logic",
                    "body": "The system supports **9 distinct customer types**, each with its own pricing tier and business rules.\n\n### Type Matrix\n| Type | Use Case | Pricing Tier |\n|------|----------|-------------|\n| **Consumer** | Walk-in retail customers | Standard selling price |\n| **Wholesale** | Bulk buyers, small shops | Wholesale price (lower) |\n| **Semi-Wholesale** | Medium-volume buyers | Between retail and wholesale |\n| **Distributor** | Regional distributors | Distributor price (lowest) |\n| **Company** | B2B corporate accounts | Negotiated/contract pricing |\n| **Technician** | Service technicians | Technician-specific pricing |\n| **Online** | E-commerce orders | Standard + shipping fees |\n| **Export** | International buyers | Zero-rated VAT, USD pricing |\n| **Government** | Government tenders | Tax-exempt, formal invoicing |\n\n### How Pricing Works\n1. When a customer is selected in POS or an invoice, the system checks their **type**.\n2. It then looks up the product's price for that tier (set in the Product form under 'Pricing' tab).\n3. If no tier-specific price exists, it falls back to the **standard selling price**.\n\n> **Example:** Product 'Widget X' has:\n> - Selling Price: EGP 100\n> - Wholesale Price: EGP 80\n> - Distributor Price: EGP 65\n> When a 'Distributor' customer buys Widget X, the system auto-applies EGP 65.",
                    "fields": {
                        "Type Selection": "Dropdown on customer creation form. Cannot be changed after transactions exist.",
                        "Price Tier Fallback": "If tier price is 0 or empty, standard selling price is used."
                    }
                },
                {
                    "title": "3. Account Statements & Credit History",
                    "body": "Two powerful financial reports available per customer.\n\n### Account Statement\nA chronological ledger showing every financial interaction:\n- **Opening Balance** → Starting debt\n- **+ Invoices** → New charges added\n- **- Payments** → Cash/bank receipts applied\n- **- Returns** → Credit notes reducing balance\n- **= Closing Balance** → Current outstanding amount\n\nFilters: Date range, include/exclude specific transaction types.\n\n### Credit History Report\nAn executive dashboard showing:\n- **Total Sales Volume** — Lifetime revenue from this customer\n- **Total Payments Received** — Cash collected\n- **Outstanding Balance** — Current debt\n- **Overdue Amount** — Past payment-terms debt\n- **Return Ratio** — Returns as % of sales (high ratio = risk flag)\n\n### Block/Unblock Mechanism\nManagers can **block** a customer with a mandatory reason:\n- Blocked customers cannot make purchases (POS or B2B)\n- Block reason is visible to all users\n- Only managers can unblock\n- All block/unblock actions are audit-logged\n\n### Import/Export\n- **Import**: Upload customer data via Excel/CSV. Maps columns to fields.\n- **Export**: Download entire customer database as Excel for backup or analysis.",
                    "fields": {
                        "Statement Period": "Date range filter for the account statement.",
                        "Block Reason": "Mandatory text field when blocking a customer.",
                        "Export Format": "Excel (.xlsx) with all customer fields."
                    }
                }
            ]
        },
        "pos": {
            "title": "Point of Sale (POS) System",
            "icon": "fa-solid fa-cash-register",
            "description": "High-speed retail interface with shift management, barcode scanning, split payments, delivery integration, and PIN-secured operations.",
            "sections": [
                {
                    "title": "1. Shift Management & Security",
                    "body": "Every POS session is wrapped in a **shift** — a security and accounting container.\n\n### Opening a Shift\n| Step | Action | Details |\n|------|--------|---------|\n| 1 | Click 'Open Shift' | System checks if a shift is already open |\n| 2 | Enter Opening Cash | The physical cash in the drawer (float) |\n| 3 | Confirm | System records: User ID, Timestamp, Opening Amount |\n\n### During the Shift\n- All sales, returns, and expenses are linked to the active shift\n- **X-Report** (mid-shift): View current totals without closing\n- **Petty Cash**: Record cash withdrawals from the drawer (e.g., buying supplies)\n\n### Closing a Shift\n| Step | Action | Details |\n|------|--------|---------|\n| 1 | Click 'Close Shift' | Requires **Closing PIN** |\n| 2 | Count Physical Cash | Enter the actual amount in the drawer |\n| 3 | System Calculates | Expected = Opening + Sales - Returns - Expenses |\n| 4 | Variance Report | Shows difference: Actual vs Expected |\n| 5 | Z-Report Prints | Complete shift summary with all transactions |\n\n> **Accounting Entry — Shift Close (Cash Sales EGP 5,000):**\n> - DR: Cash Account (1101) → EGP 5,000\n> - CR: Sales Revenue (4101) → EGP 4,386\n> - CR: VAT Payable (2201) → EGP 614",
                    "fields": {
                        "Opening Cash": "Physical float amount. Tracked for variance calculation.",
                        "X-Report": "Non-destructive mid-shift snapshot.",
                        "Z-Report": "End-of-shift summary. Prints automatically on close."
                    }
                },
                {
                    "title": "2. Sales Workflow & Checkout",
                    "body": "The POS interface is optimized for speed with keyboard shortcuts and scanner support.\n\n### Product Search Methods\n| Method | Shortcut | How It Works |\n|--------|----------|--------------|\n| **Barcode Scan** | Auto | Scans EAN/UPC directly into the cart |\n| **Text Search** | F2 | Fuzzy search by product name |\n| **SKU Entry** | F2 | Exact match on product code |\n| **Grid Browse** | — | Visual product cards with stock indicators |\n\n### Cart Operations\n- **Add Item**: Scan or search. Auto-fills price from customer tier.\n- **Edit Quantity**: Click qty field, type new amount.\n- **Line Discount**: Per-item discount %. Capped by `Max Discount %` in settings.\n- **Price Override**: Click pencil icon → Enter new price → **Requires Manager PIN**.\n- **Remove Item**: Click X button → **Requires PIN** (if enabled).\n- **Hold/Suspend**: Save cart to resume later. Accessible from any terminal.\n\n### Payment Screen\n| Payment Method | How |\n|----------------|-----|\n| **Cash** | Enter amount tendered. System calculates change. |\n| **Card/Visa** | Enter reference number. No change calculation. |\n| **Split Payment** | Part cash + part card. System tracks each method. |\n| **Credit Sale** | Entire amount added to customer balance. Requires credit check. |\n\n### Delivery Mode\nToggle 'Delivery' to add shipping details:\n- Recipient name and phone\n- Delivery address\n- Delivery fee (configurable)\n- Auto-creates a Delivery Order linked to the sale",
                    "fields": {
                        "F2": "Focus search bar for product lookup.",
                        "F10": "Jump directly to payment screen.",
                        "Manager PIN": "Required for price overrides and sensitive operations.",
                        "Hold/Resume": "Saves cart state. Resume from any networked terminal."
                    }
                },
                {
                    "title": "3. Returns (RMA) & Refunds",
                    "body": "POS returns follow a controlled, PIN-protected workflow.\n\n### Return Process\n1. **Search Invoice**: Enter invoice number or scan receipt barcode\n2. **Select Items**: Check which items are being returned and enter quantities\n3. **PIN Verification**: System prompts for **Return PIN** (set in Settings)\n4. **Reason Entry**: Select or type a return reason\n5. **Process**: System creates a Return document\n\n### Safety Rules\n- Cannot return more than originally purchased quantity\n- Cannot return items from a closed/cancelled invoice\n- Return amount is credited to customer balance or refunded as cash\n- System auto-generates the reverse accounting entry\n\n> **Accounting Entry — Return (EGP 500 item):**\n> - DR: Sales Returns (4102) → EGP 439\n> - DR: VAT Payable (2201) → EGP 61\n> - CR: Cash/Customer Account → EGP 500\n\n### Refund Methods\n| Method | When to Use |\n|--------|------------|\n| **Cash Refund** | Customer wants money back immediately |\n| **Credit Note** | Amount stays as store credit on customer account |\n| **Exchange** | Return + new sale in same transaction |",
                    "fields": {
                        "Return PIN": "Separate from Manager PIN. Set in Settings > POS > Security.",
                        "Return Limit": "Cannot exceed original invoice quantity per line.",
                        "Reverse Entry": "Automatic G/L reversal on return processing."
                    }
                }
            ]
        },
        "b2b": {
            "title": "B2B Pipeline (Quotations → Invoices)",
            "icon": "fa-solid fa-file-invoice",
            "description": "Complete corporate sales pipeline from quotation creation through sales orders, delivery orders, invoicing, payments, and returns.",
            "sections": [
                {
                    "title": "1. Quotation Builder",
                    "body": "Create professional price proposals with multi-customer targeting.\n\n### Quotation Form Fields\n| Field | Type | Description |\n|-------|------|-------------|\n| **Customer** | Multi-select | Select one or more specific customers |\n| **Target Customer Type** | Dropdown | Apply quote to ALL customers of a type (e.g., all Wholesale) |\n| **Quotation Date** | Date | Issue date (defaults to today) |\n| **Valid Until** | Date | Expiry date (defaults to +15 days) |\n| **Notes** | Text | Additional notes visible on the printed quote |\n| **Terms & Conditions** | Text | Payment terms, delivery timeline, etc. |\n\n### Line Items Table\n| Column | Description |\n|--------|-------------|\n| **Product** | Select from product catalog with auto-price fill |\n| **Quantity** | Supports decimals (0.01 step) |\n| **Unit Price** | Auto-filled from product, editable |\n| **Discount %** | Per-line discount (0-100%) |\n| **Line Total** | Auto-calculated: (Qty × Price) - Discount |\n\n### Summary Panel\n- **Subtotal**: Sum of all line totals\n- **Global Discount**: Additional flat discount on entire quote\n- **Tax**: Calculated based on system tax rate\n- **Grand Total**: Final amount after all calculations\n\n### Status Flow\n`Draft` → `Sent` → `Accepted` → `Converted to SO`\n\nOr: `Draft`/`Sent` → `Rejected` / `Expired`\n\n### Available Actions by Status\n| Status | Available Actions |\n|--------|-------------------|\n| **Draft** | Edit, Send, Accept, Reject, Expire, Delete |\n| **Sent** | Accept, Reject, Expire |\n| **Accepted** | Convert to Sales Order, Reject, Expire |\n| **Rejected/Expired** | View only, Delete |",
                    "fields": {
                        "Multi-Customer": "Send identical quotes to multiple customers at once.",
                        "Type Targeting": "Target all customers of a specific type (e.g., all Wholesale).",
                        "Convert to SO": "One-click conversion of accepted quote to Sales Order."
                    }
                },
                {
                    "title": "2. Sales Orders & Delivery",
                    "body": "Sales Orders (SO) are binding commitments that reserve inventory.\n\n### Sales Order Form Fields\n| Field | Type | Description |\n|-------|------|-------------|\n| **Customer** | Required | Single customer selection |\n| **Warehouse** | Required | Source warehouse for stock reservation |\n| **Order Date** | Date | Defaults to today |\n| **Expected Delivery** | Date | Promised delivery date |\n| **Shipping Address** | Text | Delivery destination |\n| **Line Items** | Table | Same structure as quotation lines |\n\n### What Happens When You Create a SO\n1. Stock is **virtually reserved** in the selected warehouse\n2. Available quantity decreases, but physical count stays the same\n3. Reserved items cannot be sold to other customers via POS\n4. Delivery Order can be generated from the SO\n\n### Delivery Orders (DO)\n- **Full Delivery**: Ship all items at once\n- **Partial Delivery**: Ship some items now, rest later\n- **Status Tracking**: Pending → Out for Delivery → Delivered\n- **Proof of Delivery**: Signature/photo capture\n- **Driver Assignment**: Link to driver from HR module\n\n### Mission Control Dashboard\nA real-time operations center showing:\n- All pending deliveries on a map/list\n- Driver availability and current assignments\n- Drag-and-drop order-to-driver assignment\n- Status updates from drivers in real-time",
                    "fields": {
                        "Stock Reservation": "Virtual hold on inventory. Released on cancellation.",
                        "Partial Delivery": "Ship items across multiple trips/dates.",
                        "Mission Control": "Real-time delivery operations dashboard."
                    }
                },
                {
                    "title": "3. Sales Invoices, Payments & Returns",
                    "body": "The final financial documents in the B2B pipeline.\n\n### Sales Invoices\n- Generated from a Sales Order or created directly\n- Contains all line items with prices, discounts, and tax\n- Auto-generates accounting entries on creation\n- Supports printing in multiple formats (A4, thermal)\n\n> **Accounting Entry — Sales Invoice (EGP 10,000 + 14% VAT):**\n> - DR: Accounts Receivable (1201) → EGP 11,400\n> - CR: Sales Revenue (4101) → EGP 10,000\n> - CR: VAT Payable (2201) → EGP 1,400\n\n### Customer Payments\n| Field | Description |\n|-------|-------------|\n| **Customer** | Select the paying customer |\n| **Amount** | Payment amount received |\n| **Payment Method** | Cash, Bank Transfer, Cheque, Visa |\n| **Reference** | Cheque number, transfer reference, etc. |\n| **Date** | Payment date |\n| **Notes** | Additional details |\n\n> **Accounting Entry — Payment Received (EGP 11,400):**\n> - DR: Cash/Bank (1101) → EGP 11,400\n> - CR: Accounts Receivable (1201) → EGP 11,400\n\n### Sales Returns\nSame item-selection workflow as POS returns but for B2B context:\n- Link to original invoice\n- Select items and quantities to return\n- System creates credit note\n- Inventory is restocked automatically\n- Customer balance is credited",
                    "fields": {
                        "Invoice Posting": "Automatic G/L entries on invoice creation.",
                        "Payment Allocation": "Single payment can cover multiple invoices.",
                        "Credit Note": "Auto-generated on sales return approval."
                    }
                }
            ]
        },
        "loyalty": {
            "title": "Customer Loyalty Program",
            "icon": "fa-solid fa-crown",
            "description": "Points-based rewards system with tiered memberships, automatic earning rules, and detailed analytics.",
            "sections": [
                {
                    "title": "1. Loyalty Dashboard & Settings",
                    "body": "Configure and monitor your loyalty program from a central dashboard.\n\n### Dashboard Metrics\n- **Total Active Members** — Customers enrolled in the program\n- **Total Points Issued** — Lifetime points distributed\n- **Total Points Redeemed** — Points used for rewards\n- **Points Liability** — Outstanding unredeemed points (financial value)\n\n### Program Settings\n| Setting | Description |\n|---------|-------------|\n| **Points per Currency Unit** | How many points per EGP spent (e.g., 1 point per EGP 10) |\n| **Point Value** | Cash value when redeeming (e.g., 1 point = EGP 0.50) |\n| **Minimum Redemption** | Minimum points required to redeem |\n| **Expiry Period** | Points expiration in days (0 = never expire) |\n| **Earning Rules** | Which transactions earn points (POS, B2B, or both) |\n\n### Tier System\n| Tier | Points Range | Benefits |\n|------|-------------|----------|\n| **Bronze** | 0 - 999 | Basic earning rate |\n| **Silver** | 1,000 - 4,999 | 1.5x earning multiplier |\n| **Gold** | 5,000 - 9,999 | 2x earning + priority service |\n| **Platinum** | 10,000+ | 3x earning + exclusive offers |",
                    "fields": {
                        "Points Ratio": "Configurable earning rate per currency unit.",
                        "Tier Multiplier": "Higher tiers earn points faster.",
                        "Expiry Policy": "Auto-expire unused points after defined days."
                    }
                },
                {
                    "title": "2. Points Management & Redemption",
                    "body": "Add, redeem, and track loyalty points.\n\n### Adding Points\n- **Automatic**: Points added after every qualifying sale\n- **Manual**: Manager can add bonus points (e.g., promotions, apologies)\n- **Fields**: Customer, Points Amount, Reason/Notes\n\n### Redeeming Points\n- Customer requests redemption at POS or via B2B\n- System checks: Minimum threshold met? Points not expired?\n- Redemption creates a discount on the current transaction\n- Transaction is logged with full audit trail\n\n### Reports\n- **Points History**: Per-customer timeline of all point transactions\n- **Top Earners**: Customers with highest lifetime points\n- **Redemption Rate**: Percentage of earned points actually redeemed\n- **Financial Impact**: Revenue impact of the loyalty program",
                    "fields": {
                        "Manual Points": "Bonus points with mandatory reason field.",
                        "Redemption Check": "Validates balance, minimum threshold, and expiry.",
                        "Audit Trail": "Complete log of every point transaction."
                    }
                }
            ]
        },
        "inventory": {
            "title": "Inventory & Warehouse Management",
            "icon": "fa-solid fa-boxes-stacked",
            "description": "Multi-warehouse stock control with product master data, barcode system, stock operations, and financial valuation integrity.",
            "sections": [
                {
                    "title": "1. Product Master Data (5-Tab Form)",
                    "body": "The product form is organized into 5 tabs for comprehensive data entry.\n\n### Tab 1: Basic Information\n| Field | Type | Description |\n|-------|------|-------------|\n| **Product Name** | Required | Display name across all interfaces |\n| **SKU** | Auto/Manual | Stock Keeping Unit. Auto-generated via 'Magic' button |\n| **Barcode** | Auto/Manual | EAN/UPC code. Auto-generated or scanned |\n| **Category** | Dropdown | Product classification (e.g., Electronics, Food) |\n| **Brand** | Dropdown | Manufacturer/brand name |\n| **Unit of Measure** | Required | Base unit (Piece, Box, KG, etc.) |\n| **Description** | Text | Detailed product description |\n\n### Tab 2: Pricing\n| Field | Description |\n|-------|-------------|\n| **Cost Price** | Purchase/manufacturing cost |\n| **Selling Price** | Standard retail price |\n| **Wholesale Price** | Price for wholesale customers |\n| **Semi-Wholesale Price** | Mid-tier pricing |\n| **Distributor Price** | Lowest tier for distributors |\n| **Technician Price** | Special price for technicians |\n| **Minimum Sale Price** | Floor price — system warns if selling below this |\n\n### Tab 3: Inventory\n| Field | Description |\n|-------|-------------|\n| **Track Inventory** | Toggle: Enable stock quantity tracking |\n| **Initial Stock** | Opening quantity when creating the product |\n| **Warehouse** | Which warehouse holds the initial stock |\n| **Reorder Point** | Minimum qty before low-stock alert triggers |\n| **Reorder Quantity** | Suggested purchase quantity when reordering |\n\n### Tab 4: Attributes\n| Field | Description |\n|-------|-------------|\n| **Weight** | Product weight (KG or Grams) |\n| **Dimensions** | Length × Width × Height (CM) |\n| **Color / Size** | Variant attributes |\n| **Country of Origin** | For customs and compliance |\n| **Expiry Tracking** | Enable batch/expiry management |\n| **Serial Tracking** | Enable serial number tracking |\n\n### Tab 5: Images\n- Upload multiple product images\n- First image is the primary display image\n- Used in POS grid, e-commerce, and printed catalogs",
                    "fields": {
                        "Magic SKU": "Auto-generates unique SKU with one click.",
                        "Multi-Tier Pricing": "6 different price levels for customer types.",
                        "Reorder Point": "Low-stock threshold triggering automated alerts."
                    }
                },
                {
                    "title": "2. Barcode System",
                    "body": "Comprehensive barcode generation and printing.\n\n### Barcode Generation\n- Auto-generate from SKU prefix + sequential number\n- Support for EAN-13, UPC-A, Code 128 formats\n- Bulk generation for multiple products\n\n### Barcode Label Printing\n| Setting | Options |\n|---------|--------|\n| **Label Size** | Standard (50×25mm), Large (70×40mm), Custom |\n| **Content** | Product name, price, barcode, SKU |\n| **Quantity** | Print N labels per product |\n| **Printer** | Thermal label printer or A4 sheet |\n\n### Barcode Scanning in POS\n- USB/Bluetooth scanner support\n- Instant product lookup and cart addition\n- Supports scan-to-search and scan-to-add modes",
                    "fields": {
                        "Auto-Generate": "One-click barcode creation from SKU.",
                        "Bulk Print": "Print labels for multiple products at once.",
                        "Scanner Integration": "Works with any standard USB/Bluetooth barcode scanner."
                    }
                },
                {
                    "title": "3. Stock Operations & Valuation",
                    "body": "Managing physical stock across warehouses with financial accuracy.\n\n### Stock Adjustments\nUsed for physical count reconciliation:\n- **Increase**: Damaged items found, miscounted stock\n- **Decrease**: Theft, damage, spoilage\n- **Mandatory Reason**: Every adjustment requires a reason for audit trail\n\n> **Accounting Entry — Stock Adjustment (Decrease 10 units @ EGP 50):**\n> - DR: Inventory Adjustment Expense (5201) → EGP 500\n> - CR: Inventory Asset (1301) → EGP 500\n\n### Inter-Warehouse Transfers\n1. Select source and destination warehouses\n2. Choose products and quantities\n3. Confirm transfer — stock moves immediately\n4. Both warehouses update in real-time\n\n### Valuation Methods\n| Method | How It Works | Best For |\n|--------|-------------|----------|\n| **WAC** | (Old Value + New Value) / Total Qty | General merchandise |\n| **FIFO** | Oldest stock consumed first | Perishables, regulated items |\n\n### Categories, Brands & Units\n- **Categories**: Hierarchical product classification (parent/child)\n- **Brands**: Manufacturer tracking with import support\n- **Units**: Define custom units with conversion ratios (e.g., 1 Box = 12 Pieces)",
                    "fields": {
                        "Adjustment Reason": "Mandatory audit field for every stock change.",
                        "WAC Recalculation": "Automatic on every goods receipt.",
                        "Unit Conversion": "Define ratios between units (Box → Pieces)."
                    }
                }
            ]
        },
        "purchasing": {
            "title": "Procurement & Supplier Management",
            "icon": "fa-solid fa-cart-shopping",
            "description": "End-to-end supply chain from supplier onboarding through purchase orders, goods receipt, billing, payments, and returns.",
            "sections": [
                {
                    "title": "1. Supplier Master Data (Field-by-Field)",
                    "body": "Complete vendor profiles with financial and contact information.\n\n### Supplier Form Fields\n| Field | Type | Description |\n|-------|------|-------------|\n| **Supplier Code** | Auto/Manual | Unique ID (e.g., `SUP-123456-ABC`). Magic auto-generate button. |\n| **Supplier Name** | Required | Company or individual name |\n| **Contact Person** | Optional | Sales manager or account manager name |\n| **Tax Number** | Optional | VAT registration for tax-compliant billing |\n| **Phone** | Optional | Primary contact number |\n| **Email** | Optional | For digital communications |\n| **Address** | Optional | Street, district, building details |\n| **Payment Terms** | Days | Default credit period (default: 30 days) |\n| **Active Status** | Toggle | Active/Inactive supplier flag |\n| **Notes** | Text | Additional details about the supplier |\n\n### Supplier Statement\nLive reconstruction of the General Ledger showing:\n- Every Purchase Invoice vs Every Payment\n- Running balance per transaction\n- Total Outstanding amount",
                    "fields": {
                        "Magic Code": "Auto-generates unique supplier code (SUP-XXXXXX-XXX).",
                        "Payment Terms": "Default credit days. Auto-calculates bill due dates.",
                        "Supplier Statement": "Live G/L reconstruction of all transactions."
                    }
                },
                {
                    "title": "2. Purchase Orders & Goods Receipt",
                    "body": "Ordering from suppliers and receiving goods into warehouses.\n\n### Purchase Order (PO) Fields\n| Field | Description |\n|-------|-------------|\n| **Supplier** | Select from supplier list |\n| **Warehouse** | Destination for received goods |\n| **Order Date** | PO creation date |\n| **Expected Date** | Expected delivery date |\n| **Line Items** | Product, Qty, Unit Price, Discount, Total |\n| **Notes** | Additional instructions |\n\n### Goods Receipt Note (GRN)\nWhen goods arrive, create a GRN from the PO:\n- Verify quantities received vs ordered\n- Support **partial receipt** (receive part now, rest later)\n- System tracks remaining quantities per PO line\n- On confirmation, inventory increases immediately\n\n> **Accounting Entry — GRN (EGP 20,000 goods received):**\n> - DR: Inventory Asset (1301) → EGP 20,000\n> - CR: Accounts Payable (2101) → EGP 20,000",
                    "fields": {
                        "Partial Receipt": "Receive goods in multiple batches from one PO.",
                        "Auto-Inventory": "Stock quantities update immediately on GRN confirmation.",
                        "PO Tracking": "Remaining quantities tracked per line item."
                    }
                },
                {
                    "title": "3. Purchase Invoices, Payments & Returns",
                    "body": "Financial settlement of the procurement cycle.\n\n### Purchase Invoices (Bills)\n- Created from a GRN or as a direct invoice\n- Direct invoices auto-create PO and GRN in the background\n- Contains supplier details, line items, tax calculations\n\n### Supplier Payments\n| Field | Description |\n|-------|-------------|\n| **Supplier** | Select the supplier being paid |\n| **Amount** | Payment amount |\n| **Method** | Cash, Bank Transfer, Cheque |\n| **Reference** | Transfer/cheque reference number |\n| **Date** | Payment date |\n\n> **Accounting Entry — Supplier Payment (EGP 20,000):**\n> - DR: Accounts Payable (2101) → EGP 20,000\n> - CR: Bank Account (1102) → EGP 20,000\n\n### Purchase Returns\n- Select original purchase invoice\n- Choose items and quantities to return\n- Stock decreases automatically\n- Supplier balance is adjusted (credit note created)\n\n> **Accounting Entry — Purchase Return (EGP 3,000):**\n> - DR: Accounts Payable (2101) → EGP 3,000\n> - CR: Inventory Asset (1301) → EGP 3,000",
                    "fields": {
                        "Direct Invoice": "Creates PO+GRN automatically in background.",
                        "Multi-Invoice Payment": "One payment allocated across multiple bills.",
                        "Purchase Return": "Reverses inventory and AP entries automatically."
                    }
                }
            ]
        },
        "accounting": {
            "title": "Core Accounting & General Ledger",
            "icon": "fa-solid fa-file-invoice-dollar",
            "description": "Chart of Accounts management, double-entry journal system, treasury vouchers, and complete financial integration.",
            "sections": [
                {
                    "title": "1. Chart of Accounts (Field-by-Field)",
                    "body": "The foundation of all financial reporting.\n\n### Account Creation Fields\n| Field | Type | Description |\n|-------|------|-------------|\n| **Account Code** | Required | Numeric identifier (e.g., 1101). Determines hierarchy position. |\n| **Account Name (EN)** | Required | English name for international reports |\n| **Account Name (AR)** | Required | Arabic name for local compliance |\n| **Account Type** | Required | Asset, Liability, Equity, Revenue, or Expense |\n| **Parent Account** | Optional | Groups accounts hierarchically. Leave empty for root accounts. |\n| **Description** | Optional | Purpose and usage notes |\n| **Active** | Toggle | Inactive accounts cannot receive new postings |\n\n### Account Type Rules\n| Type | Normal Balance | DR Increases? | Used For |\n|------|---------------|---------------|----------|\n| **Asset** | Debit | Yes | Cash, Bank, Inventory, Receivables |\n| **Liability** | Credit | No | Payables, Loans, VAT Payable |\n| **Equity** | Credit | No | Owner's capital, Retained earnings |\n| **Revenue** | Credit | No | Sales, Service income |\n| **Expense** | Debit | Yes | COGS, Salaries, Rent, Utilities |\n\n### Tree View\nAccounts are displayed in a hierarchical tree:\n```\n1000 Assets\n├── 1100 Current Assets\n│   ├── 1101 Cash\n│   ├── 1102 Bank\n│   └── 1201 Accounts Receivable\n├── 1300 Inventory\n│   └── 1301 Merchandise Inventory\n2000 Liabilities\n├── 2101 Accounts Payable\n└── 2201 VAT Payable\n4000 Revenue\n└── 4101 Sales Revenue\n5000 Expenses\n└── 5101 Cost of Goods Sold\n```",
                    "fields": {
                        "Account Code": "Numeric code determining hierarchy (e.g., 1101 under 1100).",
                        "Bilingual Names": "Both English and Arabic names required for compliance.",
                        "Active Toggle": "Deactivated accounts are hidden from transaction forms."
                    }
                },
                {
                    "title": "2. Manual Journal Entries",
                    "body": "Create balanced double-entry journal entries for adjustments, corrections, and manual postings.\n\n### Journal Entry Form\n| Field | Type | Description |\n|-------|------|-------------|\n| **Entry Date** | Required | Date of the journal entry |\n| **Reference** | Optional | Manual reference number for cross-referencing |\n| **Description** | Required | Explanation of the entry purpose |\n\n### Line Items Table\n| Column | Description |\n|--------|-------------|\n| **Account** | Select from Chart of Accounts (Code - Name format) |\n| **Description** | Per-line explanation (optional) |\n| **Debit** | Amount to debit this account |\n| **Credit** | Amount to credit this account |\n\n### Balance Validation\n- System shows real-time totals for Total Debit and Total Credit\n- **Green badge 'Balanced'**: Debit = Credit (can save)\n- **Red badge 'Unbalanced'**: Shows the difference amount (cannot save)\n- Maximum allowed variance: EGP 0.01\n\n> **Example — Paying Rent (EGP 5,000):**\n> | Account | Debit | Credit |\n> |---------|-------|--------|\n> | 5301 Rent Expense | 5,000 | — |\n> | 1101 Cash | — | 5,000 |\n> | **Totals** | **5,000** | **5,000** ✅ |",
                    "fields": {
                        "Zero-Sum Rule": "Debit must equal Credit within EGP 0.01 tolerance.",
                        "Balance Badge": "Real-time visual indicator (green=balanced, red=unbalanced).",
                        "Auto-Lines": "System starts with 2 blank lines, add more as needed."
                    }
                },
                {
                    "title": "3. Treasury & Vouchers",
                    "body": "Managing cash and bank transactions through vouchers.\n\n### Receipt Vouchers (Sanad Qabd)\nFor incoming money:\n- Customer payment\n- Cash deposit\n- Other income\n\n### Payment Vouchers (Sanad Sarf)\nFor outgoing money:\n- Supplier payment\n- Expense payment\n- Cash withdrawal\n\n### Voucher Fields\n| Field | Description |\n|-------|-------------|\n| **Treasury Account** | Cash or Bank account (auto-detected from Asset accounts) |\n| **Counter Account** | The other side of the entry (Customer/Supplier/Expense) |\n| **Amount** | Transaction amount |\n| **Reference** | Cheque number, transfer reference |\n| **Date** | Transaction date |\n| **Description** | Purpose of the transaction |\n\n### Auto-Detection Logic\nThe system automatically identifies treasury accounts by:\n1. Account type = Asset\n2. Account code starts with 1 (1xxx range)\n3. Account name contains keywords: Cash, Bank, Khazna, Treasury",
                    "fields": {
                        "Receipt Voucher": "Incoming money. DR Cash/Bank, CR Counter-account.",
                        "Payment Voucher": "Outgoing money. DR Counter-account, CR Cash/Bank.",
                        "Auto-Detect": "System finds treasury accounts automatically."
                    }
                }
            ]
        },
        "hr": {
            "title": "Human Resources & Payroll",
            "icon": "fa-solid fa-users-gear",
            "description": "Complete employee lifecycle — profiles, attendance, leaves, payroll, salary advances, and driver management with accounting integration.",
            "sections": [
                {
                    "title": "1. Employee Profile (Field-by-Field)",
                    "body": "Comprehensive employee records organized in sections.\n\n### Personal Information\n| Field | Type | Description |\n|-------|------|-------------|\n| **First Name** | Required | Employee's given name |\n| **Last Name** | Required | Employee's family name |\n| **Email** | Required | Work email address |\n| **Phone** | Required | Contact number |\n| **National ID / Passport** | Optional | Official identification number |\n| **Date of Birth** | Optional | For age verification and HR records |\n| **Address** | Optional | Current residential address |\n\n### Professional Information\n| Field | Type | Description |\n|-------|------|-------------|\n| **Position** | Required | Job title (e.g., Sales Manager, Cashier) |\n| **Department** | Optional | Organizational unit (e.g., Sales, IT) |\n| **Basic Salary** | Required | Monthly base salary in EGP |\n| **Date of Joining** | Required | Employment start date |\n| **Contract Type** | Dropdown | Full Time, Part Time, or Contract |\n\n### Banking Information\n| Field | Description |\n|-------|-------------|\n| **Bank Name** | Employee's bank |\n| **Account Number** | Bank account for salary transfer |\n| **IBAN** | International Bank Account Number |\n\n### Emergency Contact\n| Field | Description |\n|-------|-------------|\n| **Contact Name** | Emergency contact person |\n| **Contact Phone** | Emergency phone number |\n\n### Driver Assignment\nToggle 'Assign as Delivery Driver' to unlock:\n| Field | Description |\n|-------|-------------|\n| **License Number** | Driving license ID |\n| **License Expiry** | License expiration date |\n| **Vehicle Type** | Motorcycle, Van, Car, etc. |\n| **Vehicle Plate** | License plate number |\n\n### Status & Access\n| Field | Description |\n|-------|-------------|\n| **Status** | Active, On Leave, Terminated, Suspended |\n| **Linked User** | Map to a system login account for self-service |",
                    "fields": {
                        "Basic Salary": "Foundation for all payroll calculations.",
                        "Driver Toggle": "Unlocks vehicle/license fields and links to delivery module.",
                        "User Link": "Connects employee to system login for self-service."
                    }
                },
                {
                    "title": "2. Attendance, Leaves & Advances",
                    "body": "Tracking employee presence and managing time-off requests.\n\n### Attendance System\n- Daily check-in/check-out recording\n- Statuses: Present, Late, Absent, On Leave\n- Manager can manually override with audit notes\n- Monthly summary report per employee\n\n### Leave Management\n- Employees request leaves through the system\n- Manager approval workflow\n- Leave types: Annual, Sick, Emergency, Unpaid\n- Leave balance tracking per employee\n- Impact on payroll calculated automatically\n\n### Salary Advances\n| Field | Description |\n|-------|-------------|\n| **Employee** | Who is requesting the advance |\n| **Amount** | Advance amount in EGP |\n| **Request Date** | When the advance was requested |\n| **Repayment Month** | Which payroll month to deduct from |\n| **Status** | Pending → Approved → Deducted |\n\n> **Accounting Entry — Salary Advance Issued (EGP 2,000):**\n> - DR: Employee Advances (1401) → EGP 2,000\n> - CR: Cash Account (1101) → EGP 2,000\n\n### Advance Recovery Rules\n- Deduction is automatic during payroll processing\n- System caps deduction at 'distributable salary' (prevents negative payslips)\n- Multiple advances can be scheduled for different months",
                    "fields": {
                        "Attendance Override": "Manager can edit with mandatory audit notes.",
                        "Leave Balance": "Tracks remaining days per leave type.",
                        "Advance Cap": "Prevents deduction from exceeding distributable salary."
                    }
                },
                {
                    "title": "3. Payroll Processing",
                    "body": "Automated monthly salary calculation with accounting integration.\n\n### Payroll Calculation Logic\n```\nGross Salary = Basic Salary + Allowances\n- Absence Deductions = Absent Days × Daily Rate\n- Advance Deductions = Scheduled advance repayments\n- Other Deductions = Insurance, loans, etc.\n= Net Salary (what the employee receives)\n```\n\n### Daily Rate Formula\n`Daily Rate = Basic Salary ÷ Working Days in Month`\n\n### Payroll Workflow\n1. **Generate**: System auto-calculates for all active employees\n2. **Review**: HR reviews each payslip for accuracy\n3. **Adjust**: Make any manual corrections (allowances, deductions)\n4. **Approve**: Lock the payroll for the month\n5. **Post**: Generate accounting entries\n\n> **Accounting Entry — Monthly Payroll (Total: EGP 50,000):**\n> - DR: Salary Expense (5101) → EGP 50,000\n> - CR: Salaries Payable (2301) → EGP 50,000\n>\n> **On Payment:**\n> - DR: Salaries Payable (2301) → EGP 50,000\n> - CR: Bank Account (1102) → EGP 50,000\n\n### Payslip Details\nEach employee's payslip shows:\n- Basic salary and allowances\n- Attendance summary (present/absent/late days)\n- Deductions breakdown\n- Net payable amount\n- Advance balance remaining",
                    "fields": {
                        "Auto-Calculate": "System computes all deductions from attendance data.",
                        "Floor Rule": "Advance deductions capped to prevent negative net salary.",
                        "Accounting Bridge": "Journal entries generated automatically on approval."
                    }
                }
            ]
        },
        "reporting": {
            "title": "Reports & Financial Statements",
            "icon": "fa-solid fa-chart-line",
            "description": "Live financial statements, operational analytics, shift reports, inventory valuation, and multi-dimensional sales analysis.",
            "sections": [
                {
                    "title": "1. Financial Statements",
                    "body": "Core financial reports generated in real-time from the General Ledger.\n\n### Profit & Loss Statement (Income Statement)\nShows business performance over a period:\n- **Revenue**: All income accounts (4xxx range)\n- **Cost of Goods Sold**: Direct costs (5xxx range)\n- **Gross Profit**: Revenue - COGS\n- **Operating Expenses**: Salaries, rent, utilities (5xxx-6xxx)\n- **Net Income**: Gross Profit - Operating Expenses\n\n### Balance Sheet\nShows business financial position at a point in time:\n- **Assets** = **Liabilities** + **Equity** + **Retained Earnings**\n- Retained Earnings are computed on-the-fly from historical P&L\n- Drill-down: Click any balance to see underlying journal entries\n\n### General Ledger\nDetailed transaction history per account:\n- Filter by account, date range, or reference\n- Shows every debit and credit with running balance\n- Export to Excel for external audit\n\n### Trial Balance\n- Lists every account with DR/CR totals\n- Must balance (Total DR = Total CR)\n- Quick health-check for the entire accounting system",
                    "fields": {
                        "Date Filter": "Generate reports for any historical period.",
                        "Drill-Down": "Click totals to see individual transactions.",
                        "Excel Export": "All reports export to Excel/PDF."
                    }
                },
                {
                    "title": "2. Operational Reports",
                    "body": "Day-to-day management reports.\n\n### Sales Reports\n- **By Customer**: Revenue, returns, and balance per customer\n- **By Product**: Top-selling items, margins, quantities\n- **Shift Reports**: Individual shift summaries with cash variance\n\n### Inventory Reports\n- **Low Stock Alert**: Products below reorder point\n- **Stock Valuation**: Total inventory value per warehouse (WAC or FIFO)\n- **Stock Movement**: All ins/outs for a product or warehouse\n\n### Purchase Reports\n- **By Supplier**: Purchase volume, returns, outstanding balance\n- **GRN Summary**: Goods received per period\n\n### HR Reports\n- **Employee Summary**: Active/inactive counts, department distribution\n- **Attendance Summary**: Monthly presence rates\n- **Payroll Reports**: Total salary expense by department",
                    "fields": {
                        "Multi-Filter": "Combined date, customer, product, and warehouse filters.",
                        "Visual Charts": "Graphical representations for key metrics.",
                        "Export Bridge": "All reports support Excel and PDF export."
                    }
                }
            ]
        },
        "settings": {
            "title": "System Administration & Settings",
            "icon": "fa-solid fa-gears",
            "description": "Complete system configuration — company info, tax, invoicing, POS security, printing, accounting integration, users, roles, and backup/restore.",
            "sections": [
                {
                    "title": "1. Company & Regional Settings",
                    "body": "Core business identity and localization.\n\n### Company Information\n| Field | Description |\n|-------|-------------|\n| **Company Name** | Appears on all invoices, receipts, and reports |\n| **Company Logo** | Uploaded image for branding (receipts, invoices) |\n| **Tax Number** | Company VAT registration |\n| **Address** | Business address for official documents |\n| **Phone** | Company contact number |\n| **Email** | Company email address |\n\n### Regional Settings\n| Field | Options |\n|-------|--------|\n| **Language** | Arabic (default) or English |\n| **Currency Code** | EGP, USD, or SAR |\n| **Currency Symbol** | ج.م, $, ر.س |\n\n### Finance & Tax\n| Field | Description |\n|-------|-------------|\n| **Default Tax Rate** | VAT percentage (default: 14%) |\n| **Tax Inclusive** | Toggle: Are product prices tax-inclusive? |",
                    "fields": {
                        "Company Logo": "Appears on printed receipts and invoices.",
                        "Tax Inclusive": "When ON, tax is calculated FROM the price, not added on top.",
                        "Multi-Currency": "Support for EGP, USD, and SAR."
                    }
                },
                {
                    "title": "2. POS & Security Settings",
                    "body": "Configure POS behavior and security protocols.\n\n### POS Settings\n| Setting | Default | Description |\n|---------|---------|-------------|\n| **Auto Print Receipt** | ON | Print receipt immediately after sale |\n| **Allow Negative Stock** | OFF | Allow selling when stock is depleted |\n\n### Security PINs\n| PIN | Purpose |\n|-----|--------|\n| **Sensitive Operations PIN** | Returns, item deletion, price changes |\n| **Manager PIN** | Price overrides, system-level approvals |\n| **Maximum Discount %** | Hard cap on POS discounts (default: 50%) |\n\n### Invoice Settings\n| Field | Description |\n|-------|-------------|\n| **Invoice Prefix** | Text prefix for invoice numbers (e.g., 'INV') |\n| **Next Number** | Starting sequential number |\n| **Footer Note** | Custom text at bottom of printed invoices |",
                    "fields": {
                        "Dual PINs": "Separate PINs for refunds vs manager overrides prevent collusion.",
                        "Discount Cap": "System-enforced maximum discount percentage.",
                        "Invoice Prefix": "Customizable document numbering format."
                    }
                },
                {
                    "title": "3. Print & Receipt Customization",
                    "body": "Configure printer hardware and receipt layout.\n\n### Printer Settings\n| Setting | Options |\n|---------|--------|\n| **Printer Type** | Thermal or Laser/Inkjet (A4) |\n| **Paper Width** | 80mm (standard) or 58mm (small) |\n| **Show Logo** | Print company logo on receipts |\n\n### Receipt Layout\n| Setting | Description |\n|---------|-------------|\n| **QR Code** | Toggle: Show QR code on receipt |\n| **QR Link** | Custom URL or auto-generated invoice link |\n| **Custom Header** | Extra text above receipt content (e.g., VAT number) |\n| **Custom Footer** | Extra text below receipt content (e.g., social media) |",
                    "fields": {
                        "Thermal Support": "80mm and 58mm thermal printer support.",
                        "QR Code": "Auto-links to customer invoice page or custom URL.",
                        "Custom Header/Footer": "Brand your receipts with custom messages."
                    }
                },
                {
                    "title": "4. Accounting Integration (25+ Mapping Keys)",
                    "body": "Map business operations to specific G/L accounts. This is the brain of automated accounting.\n\n### Sales & Revenue Accounts\n| Integration Key | Purpose |\n|----------------|--------|\n| **Accounts Receivable** | Customer debt tracking |\n| **Sales Revenue** | Income from sales |\n| **Sales Returns** | Revenue reversal on returns |\n| **Output Tax (VAT)** | Tax collected on sales |\n| **Input Tax** | Tax paid on purchases |\n| **Sales Discount** | Discount expense tracking |\n| **Shipping Revenue** | Delivery fee income |\n| **Delivery Deposits** | Driver liability account |\n| **Pending Delivery** | Delivery reconciliation |\n\n### Purchase & Expense Accounts\n| Key | Purpose |\n|-----|--------|\n| **Accounts Payable** | Supplier debt tracking |\n| **Purchase Discount** | Discount income from suppliers |\n\n### Inventory & COGS\n| Key | Purpose |\n|-----|--------|\n| **Inventory** | Stock asset account |\n| **COGS** | Cost of goods sold |\n| **Inventory Adjustment** | Write-off/reconciliation |\n| **Purchase Suspense** | Pending receipt valuation |\n| **Other Differences** | Miscellaneous inventory variances |\n\n### Payments & Cash\n| Key | Purpose |\n|-----|--------|\n| **Main Cash** | Primary cash register/drawer |\n| **Main Bank/Visa** | Primary bank account |\n| **Change Balance** | POS change tracking |\n\n### HR & Salaries\n| Key | Purpose |\n|-----|--------|\n| **Salary Expense** | Monthly salary costs |\n| **Salaries Payable** | Outstanding salary liability |\n| **Employee Advances** | Advance payment tracking |\n\n### System\n| Key | Purpose |\n|-----|--------|\n| **Opening Balance** | Equity account for system migration |\n\n### Delivery Accounting Method\n| Option | Behavior |\n|--------|----------|\n| **Revenue** | Delivery fees are company income |\n| **Liability** | Delivery fees are driver deposits (pass-through) |",
                    "fields": {
                        "25+ Keys": "Every business operation maps to a specific G/L account.",
                        "Delivery Method": "Choose between revenue vs liability for delivery fees.",
                        "Opening Balance": "System migration account for initial data import."
                    }
                },
                {
                    "title": "5. Users, Roles & Backup",
                    "body": "Access control and data protection.\n\n### User Management\n- Create users with name, email, and password\n- Assign roles with specific permissions\n- Link users to employees for HR integration\n\n### Role-Based Access Control (RBAC)\n- **Admin**: Full system access\n- **Manager**: All operations except system settings\n- **Cashier**: POS only, no back-office access\n- **Accountant**: Financial modules only\n- Custom roles with granular permission selection\n\n### Backup & Restore\n- **Create Backup**: Full database snapshot\n- **Download**: Download backup file for offsite storage\n- **Restore**: Upload and restore from a previous backup\n- **Compare**: Side-by-side comparison of backup vs current data\n- **Recommended**: Daily backups for data safety\n\n### System Reset (Danger Zone)\nA specialized atomic operation:\n- **Deletes**: All sales, purchases, inventory movements, journal entries\n- **Preserves**: Chart of Accounts, Products, Users, Permissions, Settings\n- **Requires**: Admin PIN verification\n- **Warning**: Irreversible operation\n\n> ⚠️ **CAUTION**: System Reset cannot be undone. Always create a backup before resetting.",
                    "fields": {
                        "RBAC": "Granular permission system with custom role creation.",
                        "Backup Compare": "Visual comparison between backup and current state.",
                        "Atomic Reset": "Wipes transactions while preserving master data."
                    }
                }
            ]
        }
    },
    "ar": {
        "crm": {
            "title": "المبيعات وإدارة علاقات العملاء",
            "icon": "fa-solid fa-users",
            "description": "إدارة شاملة لدورة حياة العملاء — من تسجيل 9 أنواع عملاء إلى التحكم بالائتمان وكشوف الحسابات والاستيراد/التصدير.",
            "sections": [
                {
                    "title": "1. البيانات الرئيسية للعملاء (خانة بخانة)",
                    "body": "كل كيان يتفاعل مع عملك يبدأ من هنا. ملف العميل يتحكم في التسعير والائتمان والتقارير.\n\n### الهوية والتصنيف\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **الاسم** | مطلوب | الاسم القانوني للفوترة. يظهر على جميع الفواتير والإيصالات والكشوف |\n| **نوع العميل** | مطلوب | يحدد فئة التسعير. الخيارات: `مستهلك`، `جملة`، `نصف جملة`، `موزع`، `شركة`، `فني`، `أونلاين`، `تصدير`، `حكومي` |\n| **الكود** | تلقائي/يدوي | معرف فريد (مثل CUST-001). يُولد تلقائياً أو يُدخل يدوياً |\n| **الرقم الضريبي** | اختياري | رقم التسجيل الضريبي. مطلوب للفواتير الضريبية للشركات |\n\n### بيانات الاتصال\n| الحقل | الوصف |\n|-------|-------|\n| **الموبايل** | مفتاح البحث الأساسي في نقاط البيع. يُستخدم للإشعارات |\n| **الهاتف** | رقم أرضي ثانوي |\n| **البريد الإلكتروني** | لإرسال كشوف الحسابات الرقمية والإشعارات |\n| **الفاكس** | حقل تقليدي للمراسلات الرسمية |\n\n### العناوين\n| الحقل | الوصف |\n|-------|-------|\n| **عنوان الفوترة** | يُطبع على الفواتير والمستندات الضريبية |\n| **عنوان الشحن** | يُستخدم لأوامر التوصيل. يمكن أن يختلف عن الفوترة |\n| **المدينة / المحافظة** | التصنيف الجغرافي للتقارير وتوجيه التوصيل |\n\n### الضوابط المالية\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **حد الائتمان** | عملة | الحد الأقصى للمديونية المسموح بها. النظام يحظر البيع إذا تم تجاوزه |\n| **شروط الدفع** | أيام | فترة الائتمان الافتراضية (مثل 30 يوماً) |\n| **الرصيد الافتتاحي** | عملة | مديونية سابقة عند الترحيل من نظام آخر. يولد قيد محاسبي |\n\n> **القيد المحاسبي — الرصيد الافتتاحي:**\n> عند تعيين رصيد افتتاحي بقيمة 5,000 ج.م:\n> - مدين: الذمم المدينة (1201) ← 5,000 ج.م\n> - دائن: حقوق الملكية الافتتاحية (3100) ← 5,000 ج.م",
                    "fields": {
                        "حد الائتمان": "سقف صارم للمديونية. النظام يرفض البيع عند تجاوزه.",
                        "شروط الدفع": "أيام الائتمان الافتراضية. قابلة للتعديل لكل فاتورة.",
                        "الرصيد الافتتاحي": "حقل ترحيل. يولد قيد يومية تلقائياً."
                    }
                },
                {
                    "title": "2. أنواع العملاء ومنطق التسعير",
                    "body": "يدعم النظام **9 أنواع عملاء مختلفة**، لكل منها فئة تسعير وقواعد عمل خاصة.\n\n### جدول الأنواع\n| النوع | حالة الاستخدام | فئة التسعير |\n|------|------------|------------|\n| **مستهلك** | عملاء التجزئة | سعر البيع القياسي |\n| **جملة** | مشترون بالجملة | سعر الجملة (أقل) |\n| **نصف جملة** | مشترون بكميات متوسطة | بين التجزئة والجملة |\n| **موزع** | موزعون إقليميون | سعر الموزع (الأقل) |\n| **شركة** | حسابات الشركات | تسعير تفاوضي/تعاقدي |\n| **فني** | فنيو الصيانة | تسعير خاص بالفنيين |\n| **أونلاين** | طلبات التجارة الإلكترونية | قياسي + رسوم شحن |\n| **تصدير** | مشترون دوليون | ضريبة صفرية، تسعير بالدولار |\n| **حكومي** | المناقصات الحكومية | معفى ضريبياً، فوترة رسمية |\n\n### كيف يعمل التسعير\n1. عند اختيار عميل في POS أو فاتورة، يتحقق النظام من **نوعه**.\n2. يبحث عن سعر المنتج لتلك الفئة (يُحدد في نموذج المنتج تحت تبويب 'التسعير').\n3. إذا لم يوجد سعر مخصص للفئة، يستخدم **سعر البيع القياسي**.",
                    "fields": {
                        "اختيار النوع": "قائمة منسدلة عند إنشاء العميل.",
                        "التسعير الاحتياطي": "إذا كان سعر الفئة 0 أو فارغاً، يُستخدم سعر البيع القياسي."
                    }
                },
                {
                    "title": "3. كشوف الحسابات وسجل الائتمان",
                    "body": "تقريران ماليان قويان متاحان لكل عميل.\n\n### كشف الحساب\nسجل زمني يعرض كل تفاعل مالي:\n- **الرصيد الافتتاحي** ← المديونية الأولية\n- **+ الفواتير** ← رسوم جديدة مضافة\n- **- المدفوعات** ← إيصالات نقدية/بنكية مطبقة\n- **- المرتجعات** ← إشعارات دائنة تقلل الرصيد\n- **= الرصيد الختامي** ← المبلغ المستحق الحالي\n\n### تقرير سجل الائتمان\nلوحة إدارية تعرض:\n- **إجمالي حجم المبيعات** — إيرادات العمر الكامل\n- **إجمالي المدفوعات المحصلة** — النقد المُجمع\n- **الرصيد القائم** — المديونية الحالية\n- **المتأخرات** — ديون تجاوزت شروط الدفع\n- **نسبة المرتجعات** — المرتجعات كنسبة من المبيعات\n\n### آلية الحظر/إلغاء الحظر\n- يمكن للمديرين **حظر** عميل مع سبب إلزامي\n- العملاء المحظورون لا يمكنهم الشراء\n- يُسجل كل حظر/إلغاء حظر في سجل التدقيق\n\n### الاستيراد/التصدير\n- **الاستيراد**: رفع بيانات العملاء عبر Excel/CSV\n- **التصدير**: تحميل قاعدة بيانات العملاء كملف Excel",
                    "fields": {
                        "فترة الكشف": "فلتر نطاق التاريخ لكشف الحساب.",
                        "سبب الحظر": "حقل نصي إلزامي عند حظر عميل.",
                        "صيغة التصدير": "Excel (.xlsx) بجميع حقول العميل."
                    }
                }
            ]
        },
        "pos": {
            "title": "نظام نقاط البيع (POS)",
            "icon": "fa-solid fa-cash-register",
            "description": "واجهة تجزئة عالية السرعة مع إدارة الورديات، مسح الباركود، الدفع المقسم، تكامل التوصيل، وعمليات محمية برمز PIN.",
            "sections": [
                {
                    "title": "1. إدارة الورديات والأمان",
                    "body": "كل جلسة POS مغلفة في **وردية** — حاوية أمنية ومحاسبية.\n\n### فتح الوردية\n| الخطوة | الإجراء | التفاصيل |\n|--------|---------|----------|\n| 1 | اضغط 'فتح وردية' | النظام يتحقق من عدم وجود وردية مفتوحة |\n| 2 | أدخل النقدية الافتتاحية | النقد الفعلي في الدرج (العُهدة) |\n| 3 | تأكيد | النظام يسجل: معرف المستخدم، الوقت، المبلغ الافتتاحي |\n\n### خلال الوردية\n- جميع المبيعات والمرتجعات والمصروفات مرتبطة بالوردية النشطة\n- **تقرير X** (منتصف الوردية): عرض الإجماليات بدون إغلاق\n- **المصروفات النثرية**: تسجيل سحب نقدي من الدرج\n\n### إغلاق الوردية\n| الخطوة | الإجراء | التفاصيل |\n|--------|---------|----------|\n| 1 | اضغط 'إغلاق الوردية' | يتطلب **رمز PIN الإغلاق** |\n| 2 | عد النقدية الفعلية | أدخل المبلغ الفعلي في الدرج |\n| 3 | حساب النظام | المتوقع = الافتتاحي + المبيعات - المرتجعات - المصروفات |\n| 4 | تقرير الفوارق | يعرض الفرق: الفعلي مقابل المتوقع |\n| 5 | طباعة تقرير Z | ملخص الوردية الكامل |\n\n> **القيد المحاسبي — إغلاق الوردية (مبيعات نقدية 5,000 ج.م):**\n> - مدين: حساب النقدية (1101) ← 5,000 ج.م\n> - دائن: إيرادات المبيعات (4101) ← 4,386 ج.م\n> - دائن: ضريبة القيمة المضافة المستحقة (2201) ← 614 ج.م",
                    "fields": {
                        "النقدية الافتتاحية": "مبلغ العُهدة الفعلي. يُتتبع لحساب الفوارق.",
                        "تقرير X": "لقطة غير مدمرة لمنتصف الوردية.",
                        "تقرير Z": "ملخص نهاية الوردية. يُطبع تلقائياً عند الإغلاق."
                    }
                },
                {
                    "title": "2. سير عمل البيع والدفع",
                    "body": "واجهة POS محسنة للسرعة مع اختصارات لوحة المفاتيح ودعم الماسح الضوئي.\n\n### طرق البحث عن المنتجات\n| الطريقة | الاختصار | آلية العمل |\n|---------|----------|------------|\n| **مسح الباركود** | تلقائي | مسح مباشر للباركود إلى السلة |\n| **بحث نصي** | F2 | بحث بالاسم |\n| **إدخال SKU** | F2 | مطابقة دقيقة لرمز المنتج |\n| **تصفح الشبكة** | — | بطاقات منتجات مرئية مع مؤشرات المخزون |\n\n### عمليات السلة\n- **إضافة صنف**: مسح أو بحث. يملأ السعر تلقائياً من فئة العميل\n- **تعديل الكمية**: انقر حقل الكمية واكتب القيمة الجديدة\n- **خصم البند**: خصم % لكل صنف. محدد بـ `أقصى خصم %` في الإعدادات\n- **تغيير السعر**: أيقونة القلم ← أدخل السعر الجديد ← **يتطلب PIN المدير**\n- **حذف صنف**: اضغط X ← **يتطلب PIN** (إذا مُفعل)\n- **تعليق/حفظ**: حفظ السلة للاستئناف لاحقاً من أي جهاز\n\n### شاشة الدفع\n| طريقة الدفع | الآلية |\n|------------|--------|\n| **نقدي** | أدخل المبلغ المدفوع. النظام يحسب الباقي |\n| **بطاقة/فيزا** | أدخل رقم المرجع |\n| **دفع مقسم** | جزء نقدي + جزء بطاقة |\n| **بيع آجل** | المبلغ يُضاف لرصيد العميل. يتطلب فحص ائتماني |",
                    "fields": {
                        "F2": "تركيز شريط البحث لاستعلام المنتج.",
                        "F10": "الانتقال المباشر لشاشة الدفع.",
                        "PIN المدير": "مطلوب لتغيير الأسعار والعمليات الحساسة.",
                        "تعليق/استئناف": "يحفظ حالة السلة. الاستئناف من أي جهاز متصل بالشبكة."
                    }
                },
                {
                    "title": "3. المرتجعات والاسترداد",
                    "body": "مرتجعات POS تتبع سير عمل محكم ومحمي برمز PIN.\n\n### عملية الإرجاع\n1. **البحث عن الفاتورة**: أدخل رقم الفاتورة أو امسح باركود الإيصال\n2. **اختيار الأصناف**: حدد الأصناف المُرتجعة وأدخل الكميات\n3. **التحقق من PIN**: النظام يطلب **رمز PIN المرتجعات** (يُحدد في الإعدادات)\n4. **إدخال السبب**: اختر أو اكتب سبب الإرجاع\n5. **المعالجة**: النظام ينشئ مستند مرتجعات\n\n### قواعد الأمان\n- لا يمكن إرجاع أكثر من الكمية المشتراة أصلاً\n- لا يمكن إرجاع أصناف من فاتورة ملغاة\n- يُرد المبلغ كنقدي أو يُضاف لرصيد العميل\n- القيد العكسي يُولد تلقائياً\n\n> **القيد المحاسبي — مرتجعات (صنف بقيمة 500 ج.م):**\n> - مدين: مرتجعات المبيعات (4102) ← 439 ج.م\n> - مدين: ضريبة القيمة المضافة (2201) ← 61 ج.م\n> - دائن: النقدية/حساب العميل ← 500 ج.م",
                    "fields": {
                        "PIN المرتجعات": "منفصل عن PIN المدير. يُحدد في الإعدادات > POS > الأمان.",
                        "حد الإرجاع": "لا يمكن تجاوز كمية الفاتورة الأصلية لكل بند.",
                        "القيد العكسي": "عكس تلقائي لقيود الأستاذ عند معالجة المرتجعات."
                    }
                }
            ]
        },
        "b2b": {
            "title": "خط أنابيب الشركات (عروض الأسعار ← الفواتير)",
            "icon": "fa-solid fa-file-invoice",
            "description": "خط مبيعات الشركات الكامل من إنشاء عروض الأسعار عبر أوامر البيع وأوامر التوصيل والفوترة والمدفوعات والمرتجعات.",
            "sections": [
                {
                    "title": "1. منشئ عروض الأسعار",
                    "body": "إنشاء عروض أسعار احترافية مع استهداف متعدد العملاء.\n\n### حقول نموذج العرض\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **العميل** | اختيار متعدد | اختر عميلاً واحداً أو أكثر |\n| **نوع العملاء المستهدف** | قائمة | تطبيق العرض على جميع عملاء نوع معين (مثل كل الجملة) |\n| **تاريخ العرض** | تاريخ | تاريخ الإصدار (افتراضي: اليوم) |\n| **صالح حتى** | تاريخ | تاريخ الانتهاء (افتراضي: +15 يوم) |\n| **ملاحظات** | نص | ملاحظات إضافية تظهر على العرض المطبوع |\n| **الشروط والأحكام** | نص | شروط الدفع، جدول التسليم، إلخ |\n\n### جدول البنود\n| العمود | الوصف |\n|--------|-------|\n| **المنتج** | اختر من كتالوج المنتجات مع ملء السعر تلقائياً |\n| **الكمية** | يدعم الكسور العشرية |\n| **سعر الوحدة** | يُملأ تلقائياً من المنتج، قابل للتعديل |\n| **الخصم %** | خصم لكل بند (0-100%) |\n| **إجمالي البند** | يُحسب تلقائياً |\n\n### مسار الحالة\n`مسودة` ← `مُرسل` ← `مقبول` ← `محول لأمر بيع`\n\nأو: `مسودة`/`مُرسل` ← `مرفوض` / `منتهي الصلاحية`",
                    "fields": {
                        "متعدد العملاء": "إرسال عروض متطابقة لعدة عملاء دفعة واحدة.",
                        "استهداف النوع": "استهداف جميع عملاء نوع محدد (مثل كل الجملة).",
                        "التحويل لأمر بيع": "تحويل العرض المقبول لأمر بيع بضغطة واحدة."
                    }
                },
                {
                    "title": "2. أوامر البيع والتوصيل",
                    "body": "أوامر البيع (SO) هي التزامات مُلزمة تحجز المخزون.\n\n### حقول نموذج أمر البيع\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **العميل** | مطلوب | اختيار عميل واحد |\n| **المستودع** | مطلوب | مستودع المصدر لحجز المخزون |\n| **تاريخ الطلب** | تاريخ | افتراضي: اليوم |\n| **التسليم المتوقع** | تاريخ | تاريخ التسليم الموعود |\n| **عنوان الشحن** | نص | وجهة التوصيل |\n\n### ماذا يحدث عند إنشاء أمر بيع\n1. يتم **حجز المخزون افتراضياً** في المستودع المختار\n2. الكمية المتاحة تنخفض، لكن العدد الفعلي يبقى كما هو\n3. الأصناف المحجوزة لا يمكن بيعها لعملاء آخرين عبر POS\n\n### أوامر التوصيل (DO)\n- **توصيل كامل**: شحن جميع الأصناف مرة واحدة\n- **توصيل جزئي**: شحن بعض الأصناف الآن والباقي لاحقاً\n- **تتبع الحالة**: قيد الانتظار ← خرج للتوصيل ← تم التسليم\n- **إثبات التوصيل**: التقاط توقيع/صورة\n- **تعيين السائق**: ربط بسائق من وحدة الموارد البشرية",
                    "fields": {
                        "حجز المخزون": "تجميد افتراضي للمخزون. يُحرر عند الإلغاء.",
                        "التوصيل الجزئي": "شحن الأصناف عبر رحلات/تواريخ متعددة.",
                        "غرفة التحكم": "لوحة عمليات التوصيل في الوقت الفعلي."
                    }
                },
                {
                    "title": "3. فواتير المبيعات والمدفوعات والمرتجعات",
                    "body": "المستندات المالية النهائية في خط أنابيب الشركات.\n\n### فواتير المبيعات\n- تُنشأ من أمر بيع أو مباشرة\n- تحتوي على جميع البنود مع الأسعار والخصومات والضرائب\n- قيود محاسبية تلقائية عند الإنشاء\n\n> **القيد المحاسبي — فاتورة مبيعات (10,000 ج.م + ضريبة 14%):**\n> - مدين: الذمم المدينة (1201) ← 11,400 ج.م\n> - دائن: إيرادات المبيعات (4101) ← 10,000 ج.م\n> - دائن: ضريبة القيمة المضافة (2201) ← 1,400 ج.م\n\n### مدفوعات العملاء\n| الحقل | الوصف |\n|-------|-------|\n| **العميل** | اختر العميل الدافع |\n| **المبلغ** | مبلغ الدفعة المستلمة |\n| **طريقة الدفع** | نقدي، تحويل بنكي، شيك، فيزا |\n| **المرجع** | رقم الشيك أو مرجع التحويل |\n\n> **القيد المحاسبي — دفعة مستلمة (11,400 ج.م):**\n> - مدين: النقدية/البنك (1101) ← 11,400 ج.م\n> - دائن: الذمم المدينة (1201) ← 11,400 ج.م",
                    "fields": {
                        "ترحيل الفاتورة": "قيود أستاذ تلقائية عند إنشاء الفاتورة.",
                        "تخصيص الدفعة": "دفعة واحدة تغطي عدة فواتير.",
                        "إشعار دائن": "يُولد تلقائياً عند اعتماد مرتجعات البيع."
                    }
                }
            ]
        },
        "loyalty": {
            "title": "برنامج ولاء العملاء",
            "icon": "fa-solid fa-crown",
            "description": "نظام مكافآت قائم على النقاط مع عضويات متدرجة وقواعد كسب تلقائية وتحليلات مفصلة.",
            "sections": [
                {
                    "title": "1. لوحة التحكم والإعدادات",
                    "body": "إعداد ومراقبة برنامج الولاء من لوحة مركزية.\n\n### مقاييس اللوحة\n- **إجمالي الأعضاء النشطين** — العملاء المسجلون في البرنامج\n- **إجمالي النقاط الممنوحة** — النقاط الموزعة مدى الحياة\n- **إجمالي النقاط المستردة** — النقاط المستخدمة للمكافآت\n- **التزام النقاط** — النقاط غير المستردة (قيمة مالية)\n\n### إعدادات البرنامج\n| الإعداد | الوصف |\n|---------|-------|\n| **نقاط لكل وحدة عملة** | كم نقطة لكل جنيه |\n| **قيمة النقطة** | القيمة النقدية عند الاسترداد |\n| **الحد الأدنى للاسترداد** | الحد الأدنى من النقاط المطلوبة |\n| **فترة الانتهاء** | انتهاء صلاحية النقاط بالأيام |\n\n### نظام المستويات\n| المستوى | نطاق النقاط | المزايا |\n|---------|-------------|--------|\n| **برونزي** | 0 - 999 | معدل كسب أساسي |\n| **فضي** | 1,000 - 4,999 | مضاعف 1.5× |\n| **ذهبي** | 5,000 - 9,999 | مضاعف 2× + خدمة أولوية |\n| **بلاتيني** | 10,000+ | مضاعف 3× + عروض حصرية |",
                    "fields": {
                        "نسبة النقاط": "معدل كسب قابل للتكوين لكل وحدة عملة.",
                        "مضاعف المستوى": "المستويات الأعلى تكسب نقاطاً أسرع.",
                        "سياسة الانتهاء": "انتهاء تلقائي للنقاط غير المستخدمة بعد أيام محددة."
                    }
                },
                {
                    "title": "2. إدارة النقاط والاسترداد",
                    "body": "إضافة واسترداد وتتبع نقاط الولاء.\n\n### إضافة النقاط\n- **تلقائي**: النقاط تُضاف بعد كل عملية بيع مؤهلة\n- **يدوي**: المدير يمكنه إضافة نقاط إضافية (ترويجات، اعتذارات)\n\n### استرداد النقاط\n- العميل يطلب الاسترداد في POS أو B2B\n- النظام يتحقق: الحد الأدنى مستوفى؟ النقاط غير منتهية؟\n- الاسترداد ينشئ خصماً على المعاملة الحالية\n- يُسجل بسجل تدقيق كامل\n\n### التقارير\n- **سجل النقاط**: جدول زمني لكل معاملة نقاط\n- **أعلى الكاسبين**: العملاء بأعلى نقاط مدى الحياة\n- **معدل الاسترداد**: نسبة النقاط المستردة فعلياً\n- **الأثر المالي**: تأثير الإيرادات لبرنامج الولاء",
                    "fields": {
                        "النقاط اليدوية": "نقاط إضافية مع حقل سبب إلزامي.",
                        "فحص الاسترداد": "يتحقق من الرصيد والحد الأدنى والانتهاء.",
                        "سجل التدقيق": "سجل كامل لكل معاملة نقاط."
                    }
                }
            ]
        },
        "inventory": {
            "title": "إدارة المخزون والمستودعات",
            "icon": "fa-solid fa-boxes-stacked",
            "description": "تحكم في المخزون متعدد المستودعات مع بيانات المنتجات الرئيسية ونظام الباركود وعمليات المخزون ونزاهة التقييم المالي.",
            "sections": [
                {
                    "title": "1. البيانات الرئيسية للمنتج (نموذج 5 تبويبات)",
                    "body": "نموذج المنتج منظم في 5 تبويبات لإدخال بيانات شامل.\n\n### التبويب 1: المعلومات الأساسية\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **اسم المنتج** | مطلوب | اسم العرض عبر جميع الواجهات |\n| **SKU** | تلقائي/يدوي | رمز التعريف. زر 'Magic' للتوليد التلقائي |\n| **الباركود** | تلقائي/يدوي | كود EAN/UPC |\n| **الفئة** | قائمة | تصنيف المنتج |\n| **العلامة التجارية** | قائمة | اسم المصنع/العلامة |\n| **وحدة القياس** | مطلوب | الوحدة الأساسية (قطعة، صندوق، كجم) |\n\n### التبويب 2: التسعير\n| الحقل | الوصف |\n|-------|-------|\n| **سعر التكلفة** | تكلفة الشراء/التصنيع |\n| **سعر البيع** | سعر التجزئة القياسي |\n| **سعر الجملة** | سعر عملاء الجملة |\n| **سعر نصف الجملة** | تسعير متوسط |\n| **سعر الموزع** | أقل فئة للموزعين |\n| **سعر الفني** | سعر خاص للفنيين |\n| **الحد الأدنى للبيع** | سعر أرضي — تحذير عند البيع بأقل منه |\n\n### التبويب 3: المخزون\n| الحقل | الوصف |\n|-------|-------|\n| **تتبع المخزون** | تبديل: تفعيل تتبع الكميات |\n| **المخزون الأولي** | الكمية الافتتاحية عند إنشاء المنتج |\n| **المستودع** | المستودع الذي يحمل المخزون الأولي |\n| **نقطة إعادة الطلب** | الحد الأدنى قبل تنبيه نقص المخزون |\n\n### التبويب 4: الخصائص\n| الحقل | الوصف |\n|-------|-------|\n| **الوزن** | وزن المنتج (كجم أو جرام) |\n| **الأبعاد** | الطول × العرض × الارتفاع (سم) |\n| **بلد المنشأ** | للجمارك والامتثال |\n| **تتبع الصلاحية** | تفعيل إدارة التشغيلات/الصلاحية |\n| **تتبع تسلسلي** | تفعيل تتبع الأرقام التسلسلية |\n\n### التبويب 5: الصور\n- رفع صور متعددة للمنتج\n- الصورة الأولى هي صورة العرض الرئيسية",
                    "fields": {
                        "SKU السحري": "توليد فريد بضغطة واحدة.",
                        "التسعير متعدد الفئات": "6 مستويات أسعار مختلفة لأنواع العملاء.",
                        "نقطة إعادة الطلب": "عتبة نقص المخزون التي تطلق تنبيهات آلية."
                    }
                },
                {
                    "title": "2. نظام الباركود",
                    "body": "توليد وطباعة باركود شاملة.\n\n### توليد الباركود\n- توليد تلقائي من بادئة SKU + رقم تسلسلي\n- دعم صيغ EAN-13 و UPC-A و Code 128\n- توليد جماعي لعدة منتجات\n\n### طباعة الملصقات\n| الإعداد | الخيارات |\n|---------|--------|\n| **حجم الملصق** | قياسي (50×25مم)، كبير (70×40مم)، مخصص |\n| **المحتوى** | اسم المنتج، السعر، الباركود، SKU |\n| **الكمية** | طباعة N ملصق لكل منتج |\n| **الطابعة** | طابعة حرارية أو ورق A4 |",
                    "fields": {
                        "التوليد التلقائي": "إنشاء باركود بضغطة واحدة من SKU.",
                        "الطباعة الجماعية": "طباعة ملصقات لعدة منتجات مرة واحدة.",
                        "تكامل الماسح": "يعمل مع أي ماسح باركود USB/Bluetooth قياسي."
                    }
                },
                {
                    "title": "3. عمليات المخزون والتقييم",
                    "body": "إدارة المخزون الفعلي عبر المستودعات بدقة مالية.\n\n### تسويات المخزون\n- **زيادة**: أصناف تالفة وُجدت، خطأ في العد\n- **نقصان**: سرقة، تلف، فساد\n- **سبب إلزامي**: كل تسوية تتطلب سبباً لسجل التدقيق\n\n> **القيد المحاسبي — تسوية مخزون (نقص 10 وحدات @ 50 ج.م):**\n> - مدين: مصروف تسوية المخزون (5201) ← 500 ج.م\n> - دائن: أصل المخزون (1301) ← 500 ج.م\n\n### التحويلات بين المستودعات\n1. اختر المستودع المصدر والوجهة\n2. اختر المنتجات والكميات\n3. أكد التحويل — المخزون ينتقل فوراً\n\n### طرق التقييم\n| الطريقة | آلية العمل | الأنسب لـ |\n|---------|-----------|----------|\n| **WAC** | (القيمة القديمة + الجديدة) / إجمالي الكمية | البضائع العامة |\n| **FIFO** | الأقدم يُستهلك أولاً | المواد القابلة للتلف |",
                    "fields": {
                        "سبب التسوية": "حقل تدقيق إلزامي لكل تغيير في المخزون.",
                        "إعادة حساب WAC": "تلقائي عند كل استلام بضاعة.",
                        "تحويل الوحدات": "تعريف نسب بين الوحدات (صندوق ← قطع)."
                    }
                }
            ]
        },
        "purchasing": {
            "title": "المشتريات وإدارة الموردين",
            "icon": "fa-solid fa-cart-shopping",
            "description": "سلسلة توريد شاملة من تسجيل الموردين عبر أوامر الشراء واستلام البضائع والفوترة والمدفوعات والمرتجعات.",
            "sections": [
                {
                    "title": "1. البيانات الرئيسية للمورد (خانة بخانة)",
                    "body": "ملفات موردين شاملة مع معلومات مالية واتصال.\n\n### حقول نموذج المورد\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **كود المورد** | تلقائي/يدوي | معرف فريد (مثل SUP-123456-ABC). زر Magic للتوليد |\n| **اسم المورد** | مطلوب | اسم الشركة أو الفرد |\n| **الشخص المسؤول** | اختياري | مدير المبيعات أو مدير الحساب |\n| **الرقم الضريبي** | اختياري | تسجيل ضريبي للفوترة المتوافقة |\n| **الهاتف** | اختياري | رقم الاتصال الأساسي |\n| **البريد الإلكتروني** | اختياري | للمراسلات الرقمية |\n| **العنوان** | اختياري | الشارع والحي والمبنى |\n| **شروط الدفع** | أيام | فترة الائتمان الافتراضية (افتراضي: 30 يوماً) |\n| **الحالة** | تبديل | مورد نشط/غير نشط |\n\n### كشف حساب المورد\nإعادة بناء حية لدفتر الأستاذ تعرض:\n- كل فاتورة شراء مقابل كل دفعة\n- رصيد جاري لكل معاملة\n- إجمالي المبلغ المستحق",
                    "fields": {
                        "الكود السحري": "يولد كود مورد فريد تلقائياً (SUP-XXXXXX-XXX).",
                        "شروط الدفع": "أيام ائتمان افتراضية. يحسب تلقائياً تواريخ استحقاق الفواتير.",
                        "كشف حساب المورد": "إعادة بناء حية لدفتر الأستاذ لجميع المعاملات."
                    }
                },
                {
                    "title": "2. أوامر الشراء واستلام البضائع",
                    "body": "الطلب من الموردين واستلام البضائع في المستودعات.\n\n### حقول أمر الشراء (PO)\n| الحقل | الوصف |\n|-------|-------|\n| **المورد** | اختر من قائمة الموردين |\n| **المستودع** | وجهة البضائع المستلمة |\n| **تاريخ الطلب** | تاريخ إنشاء الأمر |\n| **التاريخ المتوقع** | تاريخ التسليم المتوقع |\n| **البنود** | المنتج، الكمية، سعر الوحدة، الخصم، الإجمالي |\n\n### سند استلام البضائع (GRN)\nعند وصول البضائع، أنشئ GRN من أمر الشراء:\n- تحقق من الكميات المستلمة مقابل المطلوبة\n- دعم **الاستلام الجزئي** (استلم جزءاً الآن والباقي لاحقاً)\n- النظام يتتبع الكميات المتبقية لكل بند\n- عند التأكيد، المخزون يزداد فوراً\n\n> **القيد المحاسبي — GRN (استلام بضائع بقيمة 20,000 ج.م):**\n> - مدين: أصل المخزون (1301) ← 20,000 ج.م\n> - دائن: الذمم الدائنة (2101) ← 20,000 ج.م",
                    "fields": {
                        "الاستلام الجزئي": "استلام بضائع على دفعات متعددة من أمر شراء واحد.",
                        "تحديث المخزون التلقائي": "كميات المخزون تتحدث فوراً عند تأكيد GRN.",
                        "تتبع أمر الشراء": "الكميات المتبقية تُتتبع لكل بند."
                    }
                },
                {
                    "title": "3. فواتير الشراء والمدفوعات والمرتجعات",
                    "body": "التسوية المالية لدورة المشتريات.\n\n### فواتير الشراء (الفواتير)\n- تُنشأ من GRN أو كفاتورة مباشرة\n- الفواتير المباشرة تنشئ PO و GRN تلقائياً في الخلفية\n\n### مدفوعات الموردين\n| الحقل | الوصف |\n|-------|-------|\n| **المورد** | اختر المورد المدفوع له |\n| **المبلغ** | مبلغ الدفعة |\n| **الطريقة** | نقدي، تحويل بنكي، شيك |\n| **المرجع** | رقم مرجع التحويل/الشيك |\n\n> **القيد المحاسبي — دفعة المورد (20,000 ج.م):**\n> - مدين: الذمم الدائنة (2101) ← 20,000 ج.م\n> - دائن: حساب البنك (1102) ← 20,000 ج.م\n\n### مرتجعات المشتريات\n- اختر فاتورة الشراء الأصلية\n- اختر الأصناف والكميات للإرجاع\n- المخزون ينخفض تلقائياً\n- رصيد المورد يُعدَّل\n\n> **القيد المحاسبي — مرتجعات شراء (3,000 ج.م):**\n> - مدين: الذمم الدائنة (2101) ← 3,000 ج.م\n> - دائن: أصل المخزون (1301) ← 3,000 ج.م",
                    "fields": {
                        "الفاتورة المباشرة": "تنشئ PO+GRN تلقائياً في الخلفية.",
                        "دفعة متعددة الفواتير": "دفعة واحدة توزع على عدة فواتير.",
                        "مرتجعات الشراء": "عكس تلقائي لقيود المخزون والذمم الدائنة."
                    }
                }
            ]
        },
        "accounting": {
            "title": "المحاسبة ودفتر الأستاذ العام",
            "icon": "fa-solid fa-file-invoice-dollar",
            "description": "إدارة دليل الحسابات ونظام القيد المزدوج وسندات الخزينة والتكامل المالي الكامل.",
            "sections": [
                {
                    "title": "1. دليل الحسابات (خانة بخانة)",
                    "body": "الأساس لجميع التقارير المالية.\n\n### حقول إنشاء الحساب\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **كود الحساب** | مطلوب | معرف رقمي (مثل 1101). يحدد موقع التسلسل |\n| **اسم الحساب (EN)** | مطلوب | الاسم الإنجليزي للتقارير الدولية |\n| **اسم الحساب (AR)** | مطلوب | الاسم العربي للامتثال المحلي |\n| **نوع الحساب** | مطلوب | أصل، التزام، حقوق ملكية، إيراد، أو مصروف |\n| **الحساب الأب** | اختياري | يجمع الحسابات هرمياً |\n| **الوصف** | اختياري | ملاحظات الاستخدام والغرض |\n| **نشط** | تبديل | الحسابات غير النشطة لا تستقبل قيود جديدة |\n\n### قواعد أنواع الحسابات\n| النوع | الرصيد الطبيعي | المدين يزيد؟ | يُستخدم لـ |\n|------|--------------|-------------|----------|\n| **أصل** | مدين | نعم | نقدية، بنك، مخزون، ذمم مدينة |\n| **التزام** | دائن | لا | ذمم دائنة، قروض، ضريبة مستحقة |\n| **حقوق ملكية** | دائن | لا | رأس المال، أرباح محتجزة |\n| **إيراد** | دائن | لا | مبيعات، دخل خدمات |\n| **مصروف** | مدين | نعم | تكلفة بضاعة، رواتب، إيجار |",
                    "fields": {
                        "كود الحساب": "رمز رقمي يحدد التسلسل (مثل 1101 تحت 1100).",
                        "أسماء ثنائية اللغة": "مطلوب اسمان بالإنجليزية والعربية للامتثال.",
                        "تبديل النشط": "الحسابات المعطلة تُخفى من نماذج المعاملات."
                    }
                },
                {
                    "title": "2. قيود اليومية اليدوية",
                    "body": "إنشاء قيود يومية متوازنة بالقيد المزدوج.\n\n### نموذج القيد\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **تاريخ القيد** | مطلوب | تاريخ القيد اليومية |\n| **المرجع** | اختياري | رقم مرجع يدوي للربط المتبادل |\n| **الوصف** | مطلوب | شرح الغرض من القيد |\n\n### جدول البنود\n| العمود | الوصف |\n|--------|-------|\n| **الحساب** | اختر من دليل الحسابات |\n| **الوصف** | شرح لكل بند (اختياري) |\n| **المدين** | المبلغ المدين لهذا الحساب |\n| **الدائن** | المبلغ الدائن لهذا الحساب |\n\n### التحقق من التوازن\n- النظام يعرض إجماليات المدين والدائن في الوقت الفعلي\n- **شارة خضراء 'متوازن'**: المدين = الدائن (يمكن الحفظ)\n- **شارة حمراء 'غير متوازن'**: تعرض فرق المبلغ (لا يمكن الحفظ)\n- أقصى فارق مسموح: 0.01 ج.م\n\n> **مثال — دفع إيجار (5,000 ج.م):**\n> | الحساب | مدين | دائن |\n> |--------|------|------|\n> | 5301 مصروف الإيجار | 5,000 | — |\n> | 1101 النقدية | — | 5,000 |\n> | **الإجماليات** | **5,000** | **5,000** ✅ |",
                    "fields": {
                        "قاعدة التوازن الصفري": "المدين يجب أن يساوي الدائن بفارق 0.01 ج.م كحد أقصى.",
                        "شارة التوازن": "مؤشر مرئي لحظي (أخضر=متوازن، أحمر=غير متوازن).",
                        "بنود تلقائية": "النظام يبدأ ببندين فارغين، أضف المزيد حسب الحاجة."
                    }
                },
                {
                    "title": "3. الخزينة والسندات",
                    "body": "إدارة معاملات النقدية والبنك عبر السندات.\n\n### سندات القبض\nللأموال الواردة: دفعات العملاء، إيداعات نقدية، دخل آخر.\n\n### سندات الصرف\nللأموال الصادرة: دفعات الموردين، مصروفات، سحب نقدي.\n\n### حقول السند\n| الحقل | الوصف |\n|-------|-------|\n| **حساب الخزينة** | حساب نقدية أو بنك (يُكتشف تلقائياً) |\n| **الحساب المقابل** | الطرف الآخر من القيد (عميل/مورد/مصروف) |\n| **المبلغ** | مبلغ المعاملة |\n| **المرجع** | رقم الشيك أو مرجع التحويل |\n| **التاريخ** | تاريخ المعاملة |\n| **الوصف** | غرض المعاملة |\n\n### منطق الاكتشاف التلقائي\nالنظام يحدد حسابات الخزينة تلقائياً بناءً على:\n1. نوع الحساب = أصل\n2. كود الحساب يبدأ بـ 1 (نطاق 1xxx)\n3. اسم الحساب يحتوي كلمات: نقدية، بنك، خزينة",
                    "fields": {
                        "سند القبض": "أموال واردة. مدين نقدية/بنك، دائن الحساب المقابل.",
                        "سند الصرف": "أموال صادرة. مدين الحساب المقابل، دائن نقدية/بنك.",
                        "اكتشاف تلقائي": "النظام يجد حسابات الخزينة تلقائياً."
                    }
                }
            ]
        },
        "hr": {
            "title": "الموارد البشرية والرواتب",
            "icon": "fa-solid fa-users-gear",
            "description": "دورة حياة الموظف الكاملة — الملفات الشخصية، الحضور، الإجازات، الرواتب، السلف، وإدارة السائقين مع التكامل المحاسبي.",
            "sections": [
                {
                    "title": "1. ملف الموظف (خانة بخانة)",
                    "body": "سجلات موظفين شاملة منظمة في أقسام.\n\n### المعلومات الشخصية\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **الاسم الأول** | مطلوب | الاسم الشخصي |\n| **اسم العائلة** | مطلوب | اسم العائلة |\n| **البريد الإلكتروني** | مطلوب | بريد العمل |\n| **الهاتف** | مطلوب | رقم الاتصال |\n| **الرقم القومي / جواز السفر** | اختياري | رقم التعريف الرسمي |\n| **تاريخ الميلاد** | اختياري | للتحقق من العمر |\n| **العنوان** | اختياري | عنوان السكن الحالي |\n\n### المعلومات المهنية\n| الحقل | النوع | الوصف |\n|-------|------|-------|\n| **المسمى الوظيفي** | مطلوب | الوظيفة (مثل مدير مبيعات، كاشير) |\n| **القسم** | اختياري | الوحدة التنظيمية |\n| **الراتب الأساسي** | مطلوب | الراتب الشهري بالجنيه المصري |\n| **تاريخ التعيين** | مطلوب | تاريخ بدء العمل |\n| **نوع العقد** | قائمة | دوام كامل، دوام جزئي، أو عقد مؤقت |\n\n### البيانات البنكية\n| الحقل | الوصف |\n|-------|-------|\n| **اسم البنك** | بنك الموظف |\n| **رقم الحساب** | حساب التحويل |\n| **IBAN** | رقم الحساب البنكي الدولي |\n\n### جهة الاتصال للطوارئ\n| الحقل | الوصف |\n|-------|-------|\n| **اسم جهة الاتصال** | شخص الطوارئ |\n| **رقم الهاتف** | رقم هاتف الطوارئ |\n\n### تعيين كسائق\nفعّل 'تعيين كسائق توصيل' لفتح:\n| الحقل | الوصف |\n|-------|-------|\n| **رقم الرخصة** | رقم رخصة القيادة |\n| **انتهاء الرخصة** | تاريخ انتهاء الرخصة |\n| **نوع المركبة** | دراجة بخارية، فان، سيارة |\n| **لوحة المركبة** | رقم اللوحة |",
                    "fields": {
                        "الراتب الأساسي": "الأساس لجميع حسابات الرواتب.",
                        "تبديل السائق": "يفتح حقول المركبة/الرخصة ويربط بوحدة التوصيل.",
                        "ربط المستخدم": "يربط الموظف بحساب دخول النظام للخدمة الذاتية."
                    }
                },
                {
                    "title": "2. الحضور والإجازات والسلف",
                    "body": "تتبع حضور الموظفين وإدارة طلبات الإجازات.\n\n### نظام الحضور\n- تسجيل حضور/انصراف يومي\n- الحالات: حاضر، متأخر، غائب، في إجازة\n- المدير يمكنه التعديل يدوياً مع ملاحظات تدقيق\n\n### إدارة الإجازات\n- الموظفون يطلبون إجازة عبر النظام\n- سير عمل اعتماد المدير\n- أنواع الإجازة: سنوية، مرضية، طارئة، بدون راتب\n- تتبع رصيد الإجازات لكل موظف\n\n### السلف\n| الحقل | الوصف |\n|-------|-------|\n| **الموظف** | من يطلب السلفة |\n| **المبلغ** | مبلغ السلفة بالجنيه |\n| **تاريخ الطلب** | موعد طلب السلفة |\n| **شهر السداد** | أي شهر رواتب للخصم منه |\n| **الحالة** | قيد الانتظار ← معتمد ← مخصوم |\n\n> **القيد المحاسبي — صرف سلفة (2,000 ج.م):**\n> - مدين: سلف الموظفين (1401) ← 2,000 ج.م\n> - دائن: حساب النقدية (1101) ← 2,000 ج.م\n\n### قواعد استرداد السلف\n- الخصم تلقائي أثناء معالجة الرواتب\n- النظام يحد الخصم عند 'الراتب الموزع' (يمنع رواتب سالبة)",
                    "fields": {
                        "تعديل الحضور": "المدير يمكنه التعديل مع ملاحظات تدقيق إلزامية.",
                        "رصيد الإجازات": "تتبع الأيام المتبقية لكل نوع إجازة.",
                        "سقف السلفة": "يمنع الخصم من تجاوز الراتب الموزع."
                    }
                },
                {
                    "title": "3. معالجة الرواتب",
                    "body": "حساب رواتب شهري آلي مع تكامل محاسبي.\n\n### منطق حساب الرواتب\n```\nإجمالي الراتب = الراتب الأساسي + البدلات\n- خصم الغياب = أيام الغياب × أجر اليوم\n- خصم السلف = أقساط السلف المجدولة\n- خصومات أخرى = تأمينات، قروض، إلخ\n= صافي الراتب (ما يستلمه الموظف)\n```\n\n### معادلة أجر اليوم\n`أجر اليوم = الراتب الأساسي ÷ أيام العمل في الشهر`\n\n### سير عمل الرواتب\n1. **التوليد**: النظام يحسب تلقائياً لجميع الموظفين النشطين\n2. **المراجعة**: الموارد البشرية تراجع كل مسير\n3. **التعديل**: تصحيحات يدوية (بدلات، خصومات)\n4. **الاعتماد**: قفل الرواتب للشهر\n5. **الترحيل**: إنشاء القيود المحاسبية\n\n> **القيد المحاسبي — الرواتب الشهرية (إجمالي: 50,000 ج.م):**\n> - مدين: مصروف الرواتب (5101) ← 50,000 ج.م\n> - دائن: الرواتب المستحقة (2301) ← 50,000 ج.م\n>\n> **عند الصرف:**\n> - مدين: الرواتب المستحقة (2301) ← 50,000 ج.م\n> - دائن: حساب البنك (1102) ← 50,000 ج.م",
                    "fields": {
                        "الحساب التلقائي": "النظام يحسب جميع الخصومات من بيانات الحضور.",
                        "قاعدة الحد الأدنى": "خصومات السلف محددة لمنع صافي راتب سالب.",
                        "الجسر المحاسبي": "قيود يومية تُولد تلقائياً عند الاعتماد."
                    }
                }
            ]
        },
        "reporting": {
            "title": "التقارير والقوائم المالية",
            "icon": "fa-solid fa-chart-line",
            "description": "قوائم مالية حية وتحليلات تشغيلية وتقارير ورديات وتقييم مخزون وتحليل مبيعات متعدد الأبعاد.",
            "sections": [
                {
                    "title": "1. القوائم المالية",
                    "body": "تقارير مالية أساسية تُولد لحظياً من دفتر الأستاذ.\n\n### قائمة الأرباح والخسائر (قائمة الدخل)\nتعرض أداء الأعمال خلال فترة:\n- **الإيرادات**: جميع حسابات الدخل (نطاق 4xxx)\n- **تكلفة البضاعة المباعة**: التكاليف المباشرة (نطاق 5xxx)\n- **إجمالي الربح**: الإيرادات - تكلفة البضاعة\n- **المصروفات التشغيلية**: رواتب، إيجار، مرافق\n- **صافي الدخل**: إجمالي الربح - المصروفات التشغيلية\n\n### الميزانية العمومية\nتعرض المركز المالي في نقطة زمنية:\n- **الأصول** = **الالتزامات** + **حقوق الملكية** + **أرباح محتجزة**\n- الأرباح المحتجزة تُحسب لحظياً من تاريخ الأرباح والخسائر\n- التعمق: انقر أي رصيد لرؤية قيود اليومية الأساسية\n\n### دفتر الأستاذ العام\nتاريخ المعاملات المفصل لكل حساب\n\n### ميزان المراجعة\n- يسرد كل حساب مع إجماليات المدين/الدائن\n- يجب أن يتوازن (إجمالي المدين = إجمالي الدائن)",
                    "fields": {
                        "فلتر التاريخ": "توليد تقارير لأي فترة تاريخية.",
                        "التعمق": "انقر الإجماليات لرؤية المعاملات الفردية.",
                        "تصدير Excel": "جميع التقارير تُصدّر إلى Excel/PDF."
                    }
                },
                {
                    "title": "2. التقارير التشغيلية",
                    "body": "تقارير الإدارة اليومية.\n\n### تقارير المبيعات\n- **حسب العميل**: الإيرادات والمرتجعات والرصيد لكل عميل\n- **حسب المنتج**: الأصناف الأكثر مبيعاً والهوامش والكميات\n- **تقارير الورديات**: ملخصات ورديات فردية مع فوارق النقد\n\n### تقارير المخزون\n- **تنبيه نقص المخزون**: منتجات أقل من نقطة إعادة الطلب\n- **تقييم المخزون**: إجمالي قيمة المخزون لكل مستودع\n- **حركة المخزون**: كل الداخل/الخارج لمنتج أو مستودع\n\n### تقارير المشتريات\n- **حسب المورد**: حجم المشتريات والمرتجعات والرصيد المستحق\n\n### تقارير الموارد البشرية\n- **ملخص الموظفين**: أعداد النشطين/غير النشطين\n- **ملخص الحضور**: معدلات الحضور الشهرية\n- **تقارير الرواتب**: إجمالي مصروف الرواتب حسب القسم",
                    "fields": {
                        "فلاتر متعددة": "فلاتر مجمعة للتاريخ والعميل والمنتج والمستودع.",
                        "رسوم بيانية": "تمثيلات مرئية للمقاييس الرئيسية.",
                        "جسر التصدير": "جميع التقارير تدعم تصدير Excel و PDF."
                    }
                }
            ]
        },
        "settings": {
            "title": "إدارة النظام والإعدادات",
            "icon": "fa-solid fa-gears",
            "description": "تكوين النظام الكامل — معلومات الشركة، الضرائب، الفوترة، أمان POS، الطباعة، التكامل المحاسبي، المستخدمين، الصلاحيات، والنسخ الاحتياطي.",
            "sections": [
                {
                    "title": "1. إعدادات الشركة والمنطقة",
                    "body": "هوية الشركة الأساسية والتوطين.\n\n### معلومات الشركة\n| الحقل | الوصف |\n|-------|-------|\n| **اسم الشركة** | يظهر على جميع الفواتير والإيصالات والتقارير |\n| **شعار الشركة** | صورة مرفوعة للعلامة التجارية |\n| **الرقم الضريبي** | تسجيل ضريبي للشركة |\n| **العنوان** | عنوان الشركة للمستندات الرسمية |\n| **الهاتف** | رقم اتصال الشركة |\n| **البريد الإلكتروني** | بريد الشركة |\n\n### الإعدادات الإقليمية\n| الحقل | الخيارات |\n|-------|--------|\n| **اللغة** | عربي (افتراضي) أو إنجليزي |\n| **كود العملة** | EGP, USD, أو SAR |\n| **رمز العملة** | ج.م, $, ر.س |\n\n### المالية والضرائب\n| الحقل | الوصف |\n|-------|-------|\n| **نسبة الضريبة الافتراضية** | نسبة ضريبة القيمة المضافة (افتراضي: 14%) |\n| **شاملة الضريبة** | تبديل: هل الأسعار شاملة الضريبة؟ |",
                    "fields": {
                        "شعار الشركة": "يظهر على الإيصالات والفواتير المطبوعة.",
                        "شاملة الضريبة": "عند التفعيل، الضريبة تُحسب من السعر وليست مضافة فوقه.",
                        "متعدد العملات": "دعم ج.م و $ و ر.س."
                    }
                },
                {
                    "title": "2. إعدادات POS والأمان",
                    "body": "تكوين سلوك POS وبروتوكولات الأمان.\n\n### إعدادات POS\n| الإعداد | الافتراضي | الوصف |\n|---------|---------|-------|\n| **طباعة تلقائية** | مُفعل | طباعة الإيصال فوراً بعد البيع |\n| **السماح بمخزون سالب** | معطل | السماح بالبيع عند نفاد المخزون |\n\n### رموز PIN الأمنية\n| الرمز | الغرض |\n|-------|-------|\n| **PIN العمليات الحساسة** | المرتجعات، حذف الأصناف، تغيير الأسعار |\n| **PIN المدير** | تجاوز الأسعار، اعتمادات على مستوى النظام |\n| **أقصى خصم %** | سقف صارم لخصومات POS (افتراضي: 50%) |\n\n### إعدادات الفواتير\n| الحقل | الوصف |\n|-------|-------|\n| **بادئة الفاتورة** | نص بادئة لأرقام الفواتير (مثل 'INV') |\n| **الرقم التالي** | الرقم التسلسلي البدائي |\n| **نص التذييل** | نص مخصص أسفل الفواتير المطبوعة |",
                    "fields": {
                        "رموز PIN مزدوجة": "رموز منفصلة للمرتجعات مقابل تجاوز المدير لمنع التواطؤ.",
                        "سقف الخصم": "حد أقصى مفروض من النظام لنسبة الخصم.",
                        "بادئة الفاتورة": "تنسيق ترقيم مستندات قابل للتخصيص."
                    }
                },
                {
                    "title": "3. تخصيص الطباعة والإيصالات",
                    "body": "تكوين أجهزة الطباعة وتخطيط الإيصالات.\n\n### إعدادات الطابعة\n| الإعداد | الخيارات |\n|---------|--------|\n| **نوع الطابعة** | حرارية أو ليزر/حبر (A4) |\n| **عرض الورق** | 80مم (قياسي) أو 58مم (صغير) |\n| **عرض الشعار** | طباعة شعار الشركة على الإيصالات |\n\n### تخطيط الإيصال\n| الإعداد | الوصف |\n|---------|-------|\n| **كود QR** | تبديل: عرض كود QR على الإيصال |\n| **رابط QR** | رابط مخصص أو رابط فاتورة مُولد تلقائياً |\n| **رأس مخصص** | نص إضافي فوق محتوى الإيصال |\n| **تذييل مخصص** | نص إضافي أسفل محتوى الإيصال |",
                    "fields": {
                        "دعم الحرارية": "دعم طابعات حرارية 80مم و 58مم.",
                        "كود QR": "ربط تلقائي لصفحة فاتورة العميل أو رابط مخصص.",
                        "رأس/تذييل مخصص": "ضع علامتك التجارية على الإيصالات برسائل مخصصة."
                    }
                },
                {
                    "title": "4. التكامل المحاسبي (25+ مفتاح ربط)",
                    "body": "ربط العمليات التجارية بحسابات دفتر أستاذ محددة. هذا هو دماغ المحاسبة الآلية.\n\n### حسابات المبيعات والإيرادات\n| مفتاح التكامل | الغرض |\n|--------------|-------|\n| **الذمم المدينة** | تتبع ديون العملاء |\n| **إيرادات المبيعات** | دخل المبيعات |\n| **مرتجعات المبيعات** | عكس الإيرادات عند المرتجعات |\n| **ضريبة المخرجات (VAT)** | الضريبة المحصلة على المبيعات |\n| **ضريبة المدخلات** | الضريبة المدفوعة على المشتريات |\n| **خصم المبيعات** | تتبع مصروف الخصومات |\n| **إيرادات الشحن** | دخل رسوم التوصيل |\n\n### حسابات المشتريات والمصروفات\n| المفتاح | الغرض |\n|---------|-------|\n| **الذمم الدائنة** | تتبع ديون الموردين |\n| **خصم المشتريات** | دخل خصومات الموردين |\n\n### المخزون وتكلفة البضاعة\n| المفتاح | الغرض |\n|---------|-------|\n| **المخزون** | حساب أصل المخزون |\n| **تكلفة البضاعة المباعة** | تكلفة البضاعة |\n| **تسوية المخزون** | شطب/مطابقة |\n\n### المدفوعات والنقدية\n| المفتاح | الغرض |\n|---------|-------|\n| **النقدية الرئيسية** | صندوق/درج النقدية الأساسي |\n| **البنك/الفيزا الرئيسي** | حساب البنك الأساسي |\n\n### الموارد البشرية والرواتب\n| المفتاح | الغرض |\n|---------|-------|\n| **مصروف الرواتب** | تكاليف الرواتب الشهرية |\n| **الرواتب المستحقة** | التزام الرواتب القائمة |\n| **سلف الموظفين** | تتبع مدفوعات السلف |",
                    "fields": {
                        "25+ مفتاح": "كل عملية تجارية تُربط بحساب دفتر أستاذ محدد.",
                        "طريقة التوصيل": "اختر بين إيراد مقابل التزام لرسوم التوصيل.",
                        "الرصيد الافتتاحي": "حساب ترحيل النظام لاستيراد البيانات الأولي."
                    }
                },
                {
                    "title": "5. المستخدمين والصلاحيات والنسخ الاحتياطي",
                    "body": "التحكم في الوصول وحماية البيانات.\n\n### إدارة المستخدمين\n- إنشاء مستخدمين بالاسم والبريد وكلمة المرور\n- تعيين أدوار بصلاحيات محددة\n- ربط المستخدمين بالموظفين لتكامل الموارد البشرية\n\n### التحكم بالوصول المبني على الأدوار (RBAC)\n- **مدير**: وصول كامل للنظام\n- **مشرف**: جميع العمليات عدا إعدادات النظام\n- **كاشير**: POS فقط، بدون وصول للمكتب الخلفي\n- **محاسب**: الوحدات المالية فقط\n- أدوار مخصصة مع اختيار صلاحيات دقيق\n\n### النسخ الاحتياطي والاستعادة\n- **إنشاء نسخة**: لقطة كاملة لقاعدة البيانات\n- **تحميل**: تحميل ملف النسخة للتخزين الخارجي\n- **استعادة**: رفع واستعادة من نسخة سابقة\n- **مقارنة**: مقارنة جنباً لجنب للنسخة مقابل البيانات الحالية\n\n### تصفير النظام (منطقة الخطر)\nعملية ذرية متخصصة:\n- **يحذف**: جميع المبيعات والمشتريات وحركات المخزون والقيود\n- **يحافظ على**: دليل الحسابات والمنتجات والمستخدمين والصلاحيات والإعدادات\n- **يتطلب**: تحقق من PIN المدير\n- **تحذير**: عملية لا رجعة فيها",
                    "fields": {
                        "RBAC": "نظام صلاحيات دقيق مع إنشاء أدوار مخصصة.",
                        "مقارنة النسخ": "مقارنة مرئية بين النسخة الاحتياطية والحالة الحالية.",
                        "التصفير الذري": "يمسح المعاملات بينما يحافظ على البيانات الأساسية."
                    }
                }
            ]
        }
    }
};
