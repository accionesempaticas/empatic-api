-- Check current table structure
PRAGMA table_info(signed_documents);

-- Add the missing document_type column
ALTER TABLE signed_documents ADD COLUMN document_type VARCHAR(255);

-- Verify the column was added
PRAGMA table_info(signed_documents);