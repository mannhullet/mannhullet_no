USE mannhullet_no;

-- Change the name of the document collection to 'documents' from 'nths'
UPDATE FileCollection SET collection = 'documents' WHERE collection = 'nths';
UPDATE FileCollection SET src = REPLACE(src, '/uploads/nths/', '/uploads/documents/') WHERE collection = 'documents';

-- Create a folder 'Filer'
INSERT INTO FileCollection (title, category, collection, created)
VALUES ('Filer', '2017/2018', 'documents', UNIX_TIMESTAMP());

-- Move all documents to be Files instead of FileCollections
-- Places all files in a singe folder 'Filer', need to be moved manually later
INSERT INTO File (title, created, updated, src, collection_id)
SELECT f1.title, f1.created, f1.updated, f1.src, f2.id
FROM FileCollection f1, FileCollection f2
WHERE f1.collection = 'documents' AND f1.src IS NOT NULL
AND f2.title = 'Filer' AND f2.category = '2017/2018' AND f2.collection = 'documents';

DELETE FROM FileCollection
WHERE collection = 'documents' AND src IS NOT NULL;
