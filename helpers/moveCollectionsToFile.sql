INSERT INTO FileCollection (title, category, collection, created)
VALUES ('Filer', '2017/2018', 'documents', UNIX_TIMESTAMP());

INSERT INTO File (title, created, updated, src, collection_id)
SELECT f1.title, f1.created, f1.updated, f1.src, f2.id
FROM FileCollection f1, FileCollection f2
WHERE f1.collection = 'documents' AND f1.src IS NOT NULL
AND f2.title = 'Filer' AND f2.category = '2017/2018' AND f2.collection = 'documents';

DELETE FROM FileCollection
WHERE collection = 'documents' AND src IS NOT NULL;
