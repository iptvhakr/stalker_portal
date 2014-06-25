--

UPDATE `cat_genre` SET title='foreign cartoons' WHERE title='foreign' and category_alias='animation';
UPDATE `cat_genre` SET title='our cartoons' WHERE title='ours' and category_alias='animation';
UPDATE `cat_genre` SET title='cartoon series' WHERE title='series' and category_alias='animation';

--//@UNDO