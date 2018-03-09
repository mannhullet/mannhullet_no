USE mannhullet_no;
UPDATE FileCollection SET collection = 'documents' WHERE collection = 'nths';
UPDATE FileCollection SET src = REPLACE(src, '/uploads/nths/', '/uploads/documents/') WHERE collection = 'documents';
