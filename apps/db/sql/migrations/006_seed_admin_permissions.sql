INSERT IGNORE INTO emr_permissions (name, created_at, updated_at) VALUES
('admin.access', NOW(), NOW()),
('admin.users.view', NOW(), NOW()),
('admin.users.create', NOW(), NOW()),
('admin.users.edit', NOW(), NOW()),
('admin.users.delete', NOW(), NOW()),
('admin.roles.view', NOW(), NOW()),
('admin.roles.create', NOW(), NOW()),
('admin.roles.edit', NOW(), NOW()),
('admin.roles.delete', NOW(), NOW()),
('admin.permissions.view', NOW(), NOW());

-- Assign semua admin permission ke role admin
INSERT IGNORE INTO emr_role_has_permissions (role_id, permission_id)
SELECT 1, id FROM emr_permissions WHERE name LIKE 'admin.%';
