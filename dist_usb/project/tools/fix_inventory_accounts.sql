-- Add missing Inventory Adjustment account (5201) if not exists
INSERT INTO accounts (code, name, name_ar, type, parent_id, is_header, is_system, is_active, created_at, updated_at)
SELECT '5201', 'Inventory Adjustments', 'تسويات الجرد', 'expense', id, 0, 1, 1, NOW(), NOW()
FROM accounts WHERE code = '5200' AND NOT EXISTS (SELECT 1 FROM accounts WHERE code = '5201');

-- Add missing Inventory Other account (5202) if not exists
INSERT INTO accounts (code, name, name_ar, type, parent_id, is_header, is_system, is_active, created_at, updated_at)
SELECT '5202', 'Inventory - Other', 'تسويات مخزون أخرى', 'expense', id, 0, 1, 1, NOW(), NOW()
FROM accounts WHERE code = '5200' AND NOT EXISTS (SELECT 1 FROM accounts WHERE code = '5202');

-- Add missing Opening Balance Equity account (3101) if not exists
INSERT INTO accounts (code, name, name_ar, type, parent_id, is_header, is_system, is_active, created_at, updated_at)
SELECT '3101', 'Opening Balance Equity', 'رصيد افتتاحي', 'equity', id, 0, 1, 1, NOW(), NOW()
FROM accounts WHERE code = '3000' AND NOT EXISTS (SELECT 1 FROM accounts WHERE code = '3101');
