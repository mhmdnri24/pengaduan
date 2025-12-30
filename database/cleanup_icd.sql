-- Backup table first (recommended)
CREATE TABLE icd_backup AS SELECT * FROM icd;

-- Option 1: Keep newest 10 records
DELETE FROM icd 
WHERE id NOT IN (
    SELECT id FROM (
        SELECT id FROM icd 
        ORDER BY created_at DESC, id DESC 
        LIMIT 10
    ) AS keep
);

-- If you need to rollback:
-- INSERT INTO icd SELECT * FROM icd_backup;
-- DROP TABLE icd_backup;

-- Alternative options (commented out by default):

-- Option 2: Keep oldest 10 records
/*
DELETE FROM icd 
WHERE id NOT IN (
    SELECT id FROM (
        SELECT id FROM icd 
        ORDER BY created_at ASC, id ASC 
        LIMIT 10
    ) AS keep
);
*/

-- Option 3: Keep specific 10 records by ID
/*
DELETE FROM icd 
WHERE id NOT IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
*/

-- After verifying the results, you can drop the backup:
-- DROP TABLE icd_backup;